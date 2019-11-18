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
        if (!$file->isValid()) {
            throw new UploadException('文件验证失败');
        }
        try {
            $allow_ext = config('upload.allowed_ext');
            $ext = $file->extension();
            if (!in_array($ext, $allow_ext)) {
                throw new UploadException('不允许上传后缀为 ' . $ext . ' 的文件');
            }
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $this->service->upload($file);
    }


}
