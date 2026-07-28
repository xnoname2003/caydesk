<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Services\TicketService;
use App\Http\Requests\StoreTicketRequest;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ticket::with(['category', 'priority', 'creator', 'assignedAgent']);

        if ($user && $user->hasRole('customer')) {
            $query->where('created_by', $user->id);

        } elseif ($user && $user->hasRole('agent')) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_agent_id', $user->id)
                    ->orWhere('created_by', $user->id);
            });

        } elseif ($user && $user->hasRole('supervisor')) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('assignedAgent', function ($subQ) use ($user) {
                    $subQ->where('team_id', $user->team_id);
                })
                    ->orWhere('created_by', $user->id);
            });
        }

        $tickets = $query->latest()->paginate(10);

        return response()->json($tickets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request, TicketService $ticketService)
    {
        $ticket = $ticketService->createTicket(
            $request->validated(),
            $request->user()->id,
            $request->file('attachments')
        );

        return response()->json([
            'message' => 'Ticket successfully created!',
            'data' => $ticket->load('priority', 'category', 'labels', 'attachments')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * Display the specified resource.
     */
    public function show(Request $request, $ticket_number)
    {
        $user = $request->user();

        $ticket = Ticket::with([
            'category',
            'priority',
            'creator',
            'assignedAgent',
            'labels',
            'attachments',
            'comments' => function ($q) use ($user) {
                if ($user && $user->hasRole('customer')) {
                    $q->where('is_internal', false);
                }
            },
            'comments.user',
            'comments.attachments',
        ])
            ->where('ticket_number', $ticket_number)
            ->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $hasAccess = false;

        if ($user->hasRole('administrator')) {
            $hasAccess = true;
        } elseif ($user->hasRole('supervisor')) {
            if ($ticket->created_by === $user->id || ($ticket->assignedAgent && $ticket->assignedAgent->team_id === $user->team_id)) {
                $hasAccess = true;
            }
        } elseif ($user->hasRole('agent')) {
            if ($ticket->created_by === $user->id || $ticket->assigned_agent_id === $user->id) {
                $hasAccess = true;
            }
        } elseif ($user->hasRole('customer')) {
            if ($ticket->created_by === $user->id) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Forbidden. You do not have permission to view this ticket.'
            ], 403);
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

}
