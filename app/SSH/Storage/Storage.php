<?php

namespace App\SSH\Storage;

interface Storage
{
    /**
     * @return array<string, mixed>
     */
    public function upload(string $src, string $dest): array;

    public function download(string $src, string $dest): void;

    public function delete(string $src): void;
}
