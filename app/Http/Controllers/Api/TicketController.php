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

        if (!$ticket) {
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
    
}
