<?php

namespace Sczts\Upload;

use Illuminate\Http\UploadedFile;
use Qiniu\Etag;
use Sczts\Upload\Services\QiniuService;
use Sczts\Upload\Exceptions\UploadException;

class Upload
{
    public function upload(UploadedFile $file)
    {
        try {
            $default = config('upload.default');
            $config = config('upload.drivers')[$default];
            $allow_ext = $config['allowed_ext'];
            $upload_max_size = $config['upload_max_size'];
            $ext = $file->extension();
            $size = round($file->getSize() / 1024 / 1024, 2);
            if ($size > $upload_max_size) {
                throw new UploadException($size . 'M超过最大允许值');
            }
            if (!in_array($ext, $allow_ext)) {
                throw new UploadException($ext . '后缀不允许');
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
        if (!$file->isValid()) {
            throw new UploadException('文件验证失败');
        }
        $etg = Etag::sum($file->getPath() . '/' . $file->getFilename());
        $key = $etg[0] . '.' . $ext;
        return QiniuService::upload($key, $file);
    }
}
