<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Priority;
use App\Models\SlaRule;
use App\Models\User;
use App\Services\TicketStatusService;
use App\Notifications\TicketCommentedNotification;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tickets = Ticket::with(['category', 'priority', 'creator', 'assignedAgent'])
            ->latest()
            ->paginate(10);

        return response()->json($tickets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'priority_id' => 'required|exists:priorities,id',
            'labels' => 'required|array',
            'labels.*' => 'exists:labels,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:2048',
        ]);

        $datePrefix = now()->format('ymd');
        $latestTicket = Ticket::withTrashed()->whereDate('created_at', today())->count() + 1;
        $ticketNumber = 'TCK-' . $datePrefix . '-' . str_pad($latestTicket, 6, '0', STR_PAD_LEFT);

        $priority = Priority::with('slaRule')->find($validated['priority_id']);
        $dueAt = null;
        if ($priority && $priority->slaRule) {
            $dueAt = now()->addHours($priority->slaRule->resolution_time_hours);
        }

        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'priority_id' => $validated['priority_id'],
            'status' => TicketStatusService::STATUS_OPEN,
            'created_by' => auth()->id(),
            'due_at' => $dueAt,
        ]);

        $ticket->labels()->sync($validated['labels']);
        
        $activity = $ticket->activitiesAsSubject()->where('event', 'created')->latest()->first();
        if ($activity) {
            $labels = $ticket->labels()->pluck('name')->toArray();
            $dbLog = DB::table('activity_log')->where('id', $activity->id)->first();
            if ($dbLog && property_exists($dbLog, 'attribute_changes')) {
                $changes = json_decode($dbLog->attribute_changes, true) ?: [];
                $changes['attributes'] = $changes['attributes'] ?? [];
                $changes['old'] = $changes['old'] ?? [];
                $changes['attributes']['labels'] = $labels;
                DB::table('activity_log')->where('id', $activity->id)->update([
                    'attribute_changes' => json_encode($changes),
                ]);
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');
                
                $ticket->attachments()->create([
                    'uploaded_by' => auth()->id(),
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => basename($path),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(), 
                ]);
            }
        }

        return response()->json([
            'message' => 'Ticket successfully created!',
            'data' => $ticket->load('priority', 'category', 'labels', 'attachments')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($ticket_number)
    {
        $ticket = Ticket::with([
            'category',
            'priority',
            'creator',
            'assignedAgent',
            'labels',
            'attachments', 
            'comments.user',
            'comments.attachments',
        ])
        ->where('ticket_number', $ticket_number)
        ->first();

        if (! $ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        return response()->json($ticket);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($ticket_number)
    {
        if (!auth()->user()->hasRole('administrator')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted successfully!']);
    }

    /**
     * 5. UPDATE STATUS 
     */
    public function updateStatus(Request $request, $ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        $user = auth()->user();

        if ($user->hasRole('customer') || $user->hasRole('Customer')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate(['status' => 'required|string']);
        $statusService = app(TicketStatusService::class);
        $options = $statusService->getAllowedNextStatuses($ticket->status);

        if ($user->hasRole('agent') || $user->hasRole('Agent')) {
            if ($ticket->assigned_agent_id !== $user->id) {
                return response()->json(['message' => 'You are not assigned to this ticket.'], 403);
            }
            $agentAllowed = [
                TicketStatusService::STATUS_IN_PROGRESS,
                TicketStatusService::STATUS_WAITING_FOR_CUSTOMER,
                TicketStatusService::STATUS_RESOLVED,
                TicketStatusService::STATUS_ESCALATED,
            ];
            $validForAgent = array_intersect(array_values($options), $agentAllowed);
            if (!in_array($validated['status'], $validForAgent)) {
                return response()->json(['message' => 'Invalid status transition for agent.'], 422);
            }
        } elseif (!array_key_exists($validated['status'], $options) && !in_array($validated['status'], $options)) {
            return response()->json(['message' => 'Invalid status transition.'], 422);
        }

        $ticket->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Status updated successfully!', 'data' => $ticket]);
    }

    /**
     * 6. UPDATE PRIORITY
     */
    public function updatePriority(Request $request, $ticket_number)
    {
        if (!auth()->user()->hasAnyRole(['administrator', 'supervisor'])) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        $validated = $request->validate(['priority_id' => 'required|exists:priorities,id']);

        $slaRule = SlaRule::where('priority_id', $validated['priority_id'])->first();
        $newDueAt = null;
        if ($slaRule && $slaRule->resolution_time_hours) {
            $newDueAt = $ticket->created_at->addHours($slaRule->resolution_time_hours);
        }

        $ticket->update([
            'priority_id' => $validated['priority_id'],
            'due_at' => $newDueAt,
        ]);

        return response()->json(['message' => 'Priority updated successfully!', 'data' => $ticket->load('priority')]);
    }

    /**
     * 7. ASSIGN AGENT
     */
    public function assignAgent(Request $request, $ticket_number)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['administrator', 'supervisor'])) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        $validated = $request->validate(['assigned_agent_id' => 'required|exists:users,id']);

        if ($user->hasRole('supervisor')) {
            $targetAgent = User::findOrFail($validated['assigned_agent_id']);
            if ($targetAgent->team_id !== $user->team_id && $targetAgent->id !== $user->id) {
                return response()->json(['message' => 'You can only assign agents within your team.'], 403);
            }
        }

        $updates = ['assigned_agent_id' => $validated['assigned_agent_id']];
        
        $statusService = app(TicketStatusService::class);
        if ($statusService->isValidTransition($ticket->status, TicketStatusService::STATUS_ASSIGNED)) {
            $updates['status'] = TicketStatusService::STATUS_ASSIGNED;
        }

        $ticket->update($updates);

        return response()->json(['message' => 'Agent assigned successfully!', 'data' => $ticket->load('assignedAgent')]);
    }

    /**
     * 8. MANAGE LABELS
     */
    public function manageLabels(Request $request, $ticket_number)
    {
        if (!auth()->user()->hasAnyRole(['administrator', 'supervisor'])) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        $validated = $request->validate([
            'labels' => 'required|array',
            'labels.*' => 'exists:labels,id'
        ]);

        $oldLabels = $ticket->labels->pluck('name')->toArray();
        $ticket->labels()->sync($validated['labels']);
        $newLabels = $ticket->fresh()->labels->pluck('name')->toArray();

        $log = activity()
            ->performedOn($ticket)
            ->causedBy(auth()->user())
            ->event('updated')
            ->log('Labels have been updated');
        
        $log->attribute_changes = [
            'old' => ['labels' => $oldLabels],
            'attributes' => ['labels' => $newLabels],
        ];
        $log->save();

        return response()->json(['message' => 'Labels updated successfully!', 'data' => $newLabels]);
    }

    /**
     * 9. CLOSE TICKET
     */
    public function closeTicket($ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        
        if (!auth()->user()->hasRole('customer')) {
            return response()->json(['message' => 'Only customers can close this ticket.'], 403);
        }

        $statusService = app(TicketStatusService::class);
        if (!$statusService->isValidTransition($ticket->status, TicketStatusService::STATUS_CLOSED)) {
            return response()->json(['message' => 'Ticket cannot be closed from its current status.'], 422);
        }

        $ticket->update([
            'status' => TicketStatusService::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return response()->json(['message' => 'Ticket closed successfully!', 'data' => $ticket]);
    }

    /**
     * 10. REOPEN TICKET
     */
    public function reopenTicket($ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();

        if (!auth()->user()->hasRole('customer')) {
            return response()->json(['message' => 'Only customers can reopen this ticket.'], 403);
        }

        $statusService = app(TicketStatusService::class);
        if (!$statusService->isValidTransition($ticket->status, TicketStatusService::STATUS_REOPENED)) {
            return response()->json(['message' => 'Ticket cannot be reopened from its current status.'], 422);
        }

        $ticket->update([
            'status' => TicketStatusService::STATUS_REOPENED,
        ]);

        return response()->json(['message' => 'Ticket reopened successfully!', 'data' => $ticket]);
    }

    /**
     * 11. SUBMIT REPLY
     */
    public function submitReply(Request $request, $ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        $validated = $request->validate([
            'content' => 'required|string',
            'is_internal' => 'nullable|boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:2048',
        ]);

        $comment = $ticket->comments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'is_internal' => $validated['is_internal'] ?? 0,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('comment-attachments', 'public');
                
                $comment->attachments()->create([
                    'uploaded_by' => auth()->id(),
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => basename($path),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        if (is_null($ticket->first_responded_at) && !auth()->user()->hasRole('customer')) {
            $ticket->update(['first_responded_at' => now()]);
        }

        $commenterId = auth()->id();
        $isInternal = $validated['is_internal'] ?? 0;

        if (!$isInternal && $ticket->created_by !== $commenterId) {
            $ticket->creator->notify(new TicketCommentedNotification($comment));
        }

        if ($ticket->assigned_agent_id && $ticket->assigned_agent_id !== $commenterId) {
            $ticket->assignedAgent->notify(new TicketCommentedNotification($comment));
        }

        return response()->json([
            'message' => 'Reply sent successfully!', 
            'data' => $comment->load('user')
        ], 201);
    }
}
