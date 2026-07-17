<?php

namespace App\Filament\Pages\Tickets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Services\FileService;
use App\Services\FileUploaderService;

class TicketFormSchema
{
    public static function schema(): array
    {
        return [
            TextInput::make('title')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->required()
                ->columnSpanFull(),

            Select::make('category_id')
                ->relationship('category', 'name')
                ->required()
                ->label('Category'),

            Select::make('priority_id')
                ->relationship('priority', 'name')
                ->required()
                ->label('Priority'),
            Select::make('labels')
                ->relationship('labels', 'name')
                ->multiple()
                ->preload()
                ->required()
                ->label('Labels'),
            FileUpload::make('file_attachments')
                ->multiple()
                ->maxSize(FileService::getMaxFileSize())
                ->acceptedFileTypes(FileService::getAcceptedMimeTypes())
                ->label('Attachments (Max 2MB)')
                ->columnSpanFull()
                ->directory('comment-attachments')
                ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                    return FileUploaderService::storeAndFormat($file, 'ticket-attachments');
                }),
        ];
    }
}