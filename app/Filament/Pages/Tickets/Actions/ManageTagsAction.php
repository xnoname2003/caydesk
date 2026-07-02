<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use App\Models\Label; // Pastikan model Label sudah dibuat
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class ManageTagsAction
{
    public static function make(Ticket $ticket): Action
    {
        return Action::make('manageTags')
            ->label('') 
            ->icon('heroicon-m-pencil-square')
            ->color('gray')
            ->visible(fn () => auth()->user()->hasAnyRole(['administrator', 'supervisor']))
            ->form([
                Select::make('labels')
                    ->multiple()
                    ->options(Label::pluck('name', 'id'))
                    ->default($ticket->labels->pluck('id')->toArray())
                    ->preload()
            ])
            ->action(function (array $data) use ($ticket) {
                $ticket->labels()->sync($data['labels'] ?? []);
                $ticket->load('labels');
            })
            ->successNotificationTitle('Tags updated successfully!');;
    }
}