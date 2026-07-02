<?php

namespace App\Services;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FileUploaderService
{
    public static function storeAndFormat(TemporaryUploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

        return json_encode([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }
}