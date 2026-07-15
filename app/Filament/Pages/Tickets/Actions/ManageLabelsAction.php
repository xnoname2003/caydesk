<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use App\Models\Label;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class ManageLabelsAction
{
    public static function make(Ticket $ticket): Action
    {
        return Action::make('manageLabels')
            ->label('')
            ->icon('heroicon-m-pencil-square')
            ->color('gray')
            ->visible(fn() => auth()->user()->hasAnyRole(['administrator', 'supervisor']))
            ->form([
                Select::make('labels')
                    ->multiple()
                    ->options(Label::pluck('name', 'id'))
                    ->default($ticket->labels->pluck('id')->toArray())
                    ->preload()
            ])
            ->action(function (array $data) use ($ticket) {
                $oldLabels = $ticket->labels->pluck('name')->toArray();
                $ticket->labels()->sync($data['labels']);
                $newLabels = $ticket->fresh()->labels->pluck('name')->toArray();

                $log = activity()
                    ->performedOn($ticket)
                    ->causedBy(auth()->user())
                    ->event('updated')
                    ->log('Labels have been updated');

                $log->attribute_changes = [
                    'old' => ['labels' => $oldLabels],
                    'attributes' => ['labels' => $newLabels],
                ];
                $log->save();
            })
            ->successNotificationTitle('Labels updated successfully!');
        ;
    }
}