<?php

namespace App\Filament\Pages\Tickets\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Services\FileService;
use App\Services\FileUploaderService;

class ReplyFormSchema
{
    public static function schema(): array
    {
        return [
            ToggleButtons::make('is_internal')
                ->label('')
                ->options([
                    0 => 'Public Reply',
                    1 => 'Internal Note',
                ])
                ->colors([
                    0 => 'info',
                    1 => 'warning',
                ])
                ->icons([
                    0 => 'heroicon-m-globe-americas',
                    1 => 'heroicon-m-lock-closed',
                ])
                ->grouped()
                ->default(0)
                ->visible(fn () => ! auth()->user()->hasRole('customer')),

            Textarea::make('content')
                ->label('')
                ->placeholder('Type your reply here...')
                ->rows(4)
                ->required(),

            FileUpload::make('file_attachments')
                ->label('Attachments (Max 2MB)')
                ->multiple()
                ->maxSize(FileService::getMaxFileSize())
                ->acceptedFileTypes(FileService::getAcceptedMimeTypes())
                ->directory('comment-attachments')
                ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                    return FileUploaderService::storeAndFormat($file, 'comment-attachments');
                })
                ->columnSpanFull(),
        ];
    }
}