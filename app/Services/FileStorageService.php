<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileStorageService
{
    public function store(string $contents,string $originalName):string
    {
return Storage::disk('local')->put('permits/'.$originalName,$contents);
}

    public function delete(string $path):void
    {
        Storage::disk('local')->delete($path);
}
}
