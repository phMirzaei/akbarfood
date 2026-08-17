<?php

namespace App\Infrastructure;

use App\Contracts\PermitStorage;
use App\Exceptions\PermitStorageException;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class LocalPermitStorage implements PermitStorage
{
    public function store(string $tempPath, string $originalName): string
    {
        $path = Storage::disk('local')->putFileAs('permits', new File($tempPath), $originalName);
        if ($path === false) {
            throw new PermitStorageException;
        }

        return $path;

    }

    public function delete(string $path): void
    {
        Storage::disk('local')->delete($path);
    }
}
