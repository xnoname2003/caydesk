<?php

namespace App\Http\Controllers\Api\TicketActions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketStatusService;
use App\Http\Requests\AssignTicketAgentRequest;

class AssignTicketAgentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AssignTicketAgentRequest $request, $ticket_number)
    {
        $user = $request->user();
        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        $assignedAgentId = $request->validated('assigned_agent_id');

        if ($user->hasRole('supervisor')) {
            $targetAgent = User::findOrFail($assignedAgentId);
            if ($targetAgent->team_id !== $user->team_id && $targetAgent->id !== $user->id) {
                return response()->json(['message' => 'You can only assign agents within your team.'], 403);
            }
        }

        $updates = ['assigned_agent_id' => $assignedAgentId];

        $statusService = app(TicketStatusService::class);
        if ($statusService->isValidTransition($ticket->status, TicketStatusService::STATUS_ASSIGNED)) {
            $updates['status'] = TicketStatusService::STATUS_ASSIGNED;
        }

        $ticket->update($updates);

        return response()->json(['message' => 'Agent assigned successfully!', 'data' => $ticket->load('assignedAgent')]);
    }
}
