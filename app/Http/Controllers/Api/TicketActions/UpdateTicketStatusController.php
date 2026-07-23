<?php

namespace App\Http\Controllers\Api\TicketActions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Services\TicketStatusService;
use App\Http\Requests\UpdateTicketStatusRequest;

class UpdateTicketStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateTicketStatusRequest $request, $ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        $user = $request->user();

        $requestedStatus = $request->validated('status');

        $isCreator = $ticket->created_by === $user->id;
        $isAssignedAgent = $ticket->assigned_agent_id === $user->id;

        $customerAllowedStatuses = [
            TicketStatusService::STATUS_CLOSED,
            TicketStatusService::STATUS_REOPENED,
        ];
        $isCustomerAction = in_array($requestedStatus, $customerAllowedStatuses);

        if (!$user->hasAnyRole(['administrator', 'supervisor'])) {

            if ($user->hasRole('agent')) {
                $agentAllowedTransitions = [
                    TicketStatusService::STATUS_IN_PROGRESS,
                    TicketStatusService::STATUS_WAITING_FOR_CUSTOMER,
                    TicketStatusService::STATUS_RESOLVED,
                    TicketStatusService::STATUS_ESCALATED,
                ];

                $canActAsAgent = $isAssignedAgent && in_array($requestedStatus, $agentAllowedTransitions);
                $canActAsCreator = $isCreator && $isCustomerAction;

                if (!$canActAsAgent && !$canActAsCreator) {
                    if (!$isAssignedAgent && !$isCreator) {
                        return response()->json(['message' => 'You are not authorized to update this ticket.'], 403);
                    }
                    return response()->json(['message' => 'Agent can only change status to allowed agent statuses, or close/reopen their own tickets.'], 403);
                }
            } elseif ($user->hasRole('customer')) {
                if (!$isCreator) {
                    return response()->json(['message' => 'You are not authorized to update this ticket.'], 403);
                }

                if (!$isCustomerAction) {
                    return response()->json(['message' => 'Customers can only close or reopen tickets.'], 403);
                }
            } else {
                return response()->json(['message' => 'Unauthorized role.'], 403);
            }
        }

        if (!TicketStatusService::isValidTransition($ticket->status, $requestedStatus)) {
            return response()->json([
                'message' => 'Invalid status transition based on current ticket status.'
            ], 422);
        }

        $updates = ['status' => $requestedStatus];

        if ($request->has('assigned_agent_id')) {
            $updates['assigned_agent_id'] = $request->validated('assigned_agent_id');
        }

        $ticket->update($updates);

        return response()->json([
            'message' => 'Status updated successfully!',
            'data' => $ticket
        ]);
    }
}
