<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SafeImageUpload extends FileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->preventFilePathTampering();
        $this->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
            // Store a MIME-derived extension, never a client-supplied extension.
            $extension = match ($file->getMimeType()) {
                'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
                default => throw ValidationException::withMessages([$this->getStatePath() => 'Upload a JPEG, PNG or WebP image.']),
            };

            return Str::ulid().'.'.$extension;
        });
    }
}
