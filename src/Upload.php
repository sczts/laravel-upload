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

    /**
     * @param string $custom_prefix //  自定义前缀
     * @param int $expire //  有效时间
     * @return string                   //  token 值
     */
    public function getUploadToken($custom_prefix = '', $expire = 3600)
    {
        return $this->service->getUploadToken($custom_prefix, $expire);
    }

    /**
     * @param UploadedFile $file
     * @param string $custom_prefix
     * @param int $expire
     * @return array
     * @throws \Exception
     */
    public function upload(UploadedFile $file, $custom_prefix = '', $expire = 3600)
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
        return $this->service->upload($file, $custom_prefix, $expire);
    }

    public function fileList($marker, $limit, $prefix)
    {
        return $this->service->fileList($marker, $limit, $prefix);
    }
}
