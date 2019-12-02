<?php


namespace Sczts\Upload;


use Illuminate\Http\UploadedFile;

interface UploadService
{
    public function upload(UploadedFile $file): array;

    public function fileList(string $marker,int $limit,string $prefix) :array;
}
