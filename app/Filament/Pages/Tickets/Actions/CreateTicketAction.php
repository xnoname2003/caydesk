<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Filament\Pages\Tickets\Schemas\TicketFormSchema;
use App\Models\Priority;
use App\Models\Ticket;
use App\Services\TicketStatusService;
use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
                $data['ticket_number'] = 'TCK-'.$datePrefix.'-'.str_pad($latestTicket, 6, '0', STR_PAD_LEFT);
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

                $activity = $record->activitiesAsSubject()->where('event', 'created')->latest()->first();

                if ($activity) {
                    $labels = $record->labels()->pluck('name')->toArray();

                    if (! empty($labels)) {
                        $dbLog = DB::table('activity_log')->where('id', $activity->id)->first();

                        if ($dbLog && property_exists($dbLog, 'attribute_changes')) {

                            $changes = json_decode($dbLog->attribute_changes, true) ?: [];

                            if (! isset($changes['attributes'])) {
                                $changes['attributes'] = [];
                            }
                            if (! isset($changes['old'])) {
                                $changes['old'] = [];
                            }

                            $changes['attributes']['labels'] = $labels;

                            DB::table('activity_log')
                                ->where('id', $activity->id)
                                ->update([
                                    'attribute_changes' => json_encode($changes),
                                ]);
                        }
                    }
                }
            })
            ->successNotificationTitle('Ticket successfully created!');
    }
}
