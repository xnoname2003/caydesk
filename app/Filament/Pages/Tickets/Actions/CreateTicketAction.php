<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use App\Models\Priority;
use App\Services\TicketStatusService;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Tickets\Schemas\TicketFormSchema;
use Illuminate\Database\Eloquent\Model;

class CreateTicketAction
{
    public static function make(): CreateAction
    {
        return CreateAction::make('createTicket')
            ->label('+ New Ticket')
            ->model(Ticket::class)
            ->form(TicketFormSchema::schema())
            ->mutateFormDataUsing(function (array $data): array {
                $data['created_by'] = auth()->id();
                $data['status'] = TicketStatusService::STATUS_OPEN;
                $datePrefix = now()->format('ymd');
                $latestTicket = Ticket::withTrashed()->whereDate('created_at', today())->count() + 1;
                $data['ticket_number'] = 'TCK-' . $datePrefix . '-' . str_pad($latestTicket, 6, '0', STR_PAD_LEFT);
                $priority = Priority::with('slaRule')->find($data['priority_id']);
                if ($priority && $priority->slaRule) {
                    $data['due_at'] = now()->addHours($priority->slaRule->resolution_time_hours);
                }

                return $data;
            })
            ->using(function (array $data, string $model): Model {
                unset($data['file_attachments']);
                
                return $model::create($data);
            })
            ->after(function (array $data, Ticket $record) {
                $attachments = $data['file_attachments'] ?? [];
                
                foreach ($attachments as $jsonString) {
                    $meta = json_decode($jsonString, true);
                    
                    if ($meta && isset($meta['path'])) {
                        $record->attachments()->create([
                            'uploaded_by' => auth()->id(),
                            'original_name' => $meta['original_name'],
                            'stored_name' => basename($meta['path']),
                            'path' => $meta['path'],
                            'mime_type' => $meta['mime_type'], 
                            'size' => $meta['size'],
                        ]);
                    }
                }
            })
            ->successNotificationTitle('Ticket successfully created!');
    }
}