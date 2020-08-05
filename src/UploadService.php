<?php


namespace Sczts\Upload;


use Illuminate\Http\UploadedFile;

interface UploadService
{
    public function upload(UploadedFile $file, array $returnBody = []): array;

    public function fileList(string $marker, int $limit, string $prefix): array;

    public function uploadToken(array $returnBody = [], int $expires = 3600): string;

    public function downloadUrl($url, $expires = 3600): string;

}
