<?php

namespace App\Services;

class FileService
{
    public static function getAcceptedMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }
    public static function getMaxFileSize(): int
    {
        return 2048;
    }
}