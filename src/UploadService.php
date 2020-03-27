<?php


namespace Sczts\Upload;


use Illuminate\Http\UploadedFile;

interface UploadService
{
    public function getUploadToken(string $custom_prefix, int $expire): string;

    public function upload(UploadedFile $file, string $custom_prefix, int $expire): array;

    public function fileList(string $marker, int $limit, string $prefix): array;
}
