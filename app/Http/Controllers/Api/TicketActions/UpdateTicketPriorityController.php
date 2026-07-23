<?php

namespace App\Http\Controllers\Api\TicketActions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\SlaRule;
use App\Http\Requests\UpdateTicketPriorityRequest;

class UpdateTicketPriorityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateTicketPriorityRequest $request, $ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        
        $priorityId = $request->validated('priority_id');
        $slaRule = SlaRule::where('priority_id', $priorityId)->first();
        
        $newDueAt = null;
        if ($slaRule && $slaRule->resolution_time_hours) {
            $newDueAt = $ticket->created_at->addHours($slaRule->resolution_time_hours);
        }

        $ticket->update([
            'priority_id' => $priorityId,
            'due_at' => $newDueAt,
        ]);

        return response()->json(['message' => 'Priority updated successfully!', 'data' => $ticket->load('priority')]);
    }
}
