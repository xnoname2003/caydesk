<?php

namespace App\Http\Controllers\Api\TicketActions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Http\Requests\ManageTicketLabelsRequest;

class ManageTicketLabelsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ManageTicketLabelsRequest $request, $ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        
        $oldLabels = $ticket->labels->pluck('name')->toArray();
        $ticket->labels()->sync($request->validated('labels'));
        $newLabels = $ticket->fresh()->labels->pluck('name')->toArray();

        $log = activity()
            ->performedOn($ticket)
            ->causedBy($request->user())
            ->event('updated')
            ->log('Labels have been updated');

        $log->attribute_changes = [
            'old' => ['labels' => $oldLabels],
            'attributes' => ['labels' => $newLabels],
        ];
        $log->save();

        return response()->json(['message' => 'Labels updated successfully!', 'data' => $newLabels]);
    }
}
