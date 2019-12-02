<?php

namespace Sczts\Upload;

use Illuminate\Http\UploadedFile;
use Sczts\Upload\Exceptions\UploadException;

class Upload
{
    private $service;

    public function __construct(UploadService $service)
    {
        $this->service = $service;
    }

    public function upload(UploadedFile $file)
    {
        try {
            $allow_ext = config('upload.allowed_ext');
            $max_size = config('upload.max_size');
            $ext = $file->extension();
            $size = round($file->getSize() / 1024 / 1024, 2);
            if ($size > $max_size) {
                throw new UploadException($size . 'M超过最大允许值');
            }
            if (!in_array($ext, $allow_ext)) {
                throw new UploadException('不允许上传后缀为 ' . $ext . ' 的文件');
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
        return $this->service->upload($file);
    }

    public function fileList($marker,$limit,$prefix)
    {
        return $this->service->fileList($marker,$limit,$prefix);
    }


}
