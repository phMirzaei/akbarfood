<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileStorageService
{
    public function store(string $contents, string $originalName): string
    {
        $path = 'permits/'.$originalName;
        Storage::disk('local')->put($path, $contents);

        return $path;
    }

    public function delete(string $path): void
    {
        Storage::disk('local')->delete($path);
    }
}
