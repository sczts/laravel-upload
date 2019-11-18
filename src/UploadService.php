<?php


namespace Sczts\Upload;


use Illuminate\Http\UploadedFile;

interface UploadService
{
    public function upload(UploadedFile $file): array;
}
