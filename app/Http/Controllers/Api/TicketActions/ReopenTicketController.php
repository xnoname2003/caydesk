<?php

namespace App\Http\Controllers\Api\TicketActions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Services\TicketStatusService;

class ReopenTicketController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();

        if ($request->user()->id !== $ticket->created_by && !$request->user()->hasAnyRole(['administrator', 'supervisor'])) {
            return response()->json(['message' => 'You are not authorized to close this ticket.'], 403);
        }

        $statusService = app(TicketStatusService::class);
        if (!$statusService->isValidTransition($ticket->status, TicketStatusService::STATUS_REOPENED)) {
            return response()->json(['message' => 'Ticket cannot be reopened from its current status.'], 422);
        }

        $ticket->update(['status' => TicketStatusService::STATUS_REOPENED]);

        return response()->json(['message' => 'Ticket reopened successfully!', 'data' => $ticket]);
    }
}
