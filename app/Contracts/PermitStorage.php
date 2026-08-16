<?php

namespace App\Contracts;

interface PermitStorage
{
    public function store(
        string $tempPath,
        string $originalName,
    ):string;

    public function delete(
        string $path,
    ):void;
}
