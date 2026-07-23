<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Priority;
use App\Services\TicketStatusService;
use Illuminate\Http\UploadedFile;

class TicketService
{
    public function createTicket(array $data, string $userId, ?array $attachments = null): Ticket
    {
        $datePrefix = now()->format('ymd');
        $latestTicket = Ticket::withTrashed()->whereDate('created_at', today())->count() + 1;
        $ticketNumber = 'TCK-' . $datePrefix . '-' . str_pad($latestTicket, 6, '0', STR_PAD_LEFT);

        $priority = Priority::with('slaRule')->find($data['priority_id']);
        $dueAt = null;
        if ($priority && $priority->slaRule) {
            $dueAt = now()->addHours($priority->slaRule->resolution_time_hours);
        }

        $ticketData = [
            'ticket_number' => $ticketNumber,
            'title'         => $data['title'],
            'description'   => $data['description'],
            'category_id'   => $data['category_id'],
            'priority_id'   => $data['priority_id'],
            'status'        => TicketStatusService::STATUS_OPEN, 
            'created_by'    => $userId,
            'due_at'        => $dueAt,
        ];

        if (isset($data['assigned_agent_id'])) {
            $ticketData['assigned_agent_id'] = $data['assigned_agent_id'];
        }

        $ticket = Ticket::create($ticketData);

        if ($attachments) {
            /** @var UploadedFile $file */
            foreach ($attachments as $file) {
                $path = $file->store('ticket-attachments', 'public');
                $ticket->attachments()->create([
                    'uploaded_by'   => $userId,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name'   => basename($path),
                    'path'          => $path,
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ]);
            }
        }

        return $ticket;
    }
}