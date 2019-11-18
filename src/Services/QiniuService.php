<?php

namespace Sczts\Upload\Services;

use Illuminate\Http\UploadedFile;
use Qiniu\Auth;
use Qiniu\Etag;
use Qiniu\Storage\UploadManager;
use Illuminate\Support\Facades\Cache;
use Sczts\Upload\Exceptions\UploadException;
use Sczts\Upload\UploadService;

class QiniuService implements UploadService
{
    private $config;

    public function __construct()
    {
        $this->config = config('upload.services.qiniu');
    }


    public function getUploadToken()
    {
        return Cache::remember('upload_token', 58, function () {
            $accessKey = $this->config['access_key'];
            $secretKey = $this->config['secret_key'];
            $bucket = $this->config['bucket'];

            $auth = new Auth($accessKey, $secretKey);

            $putPolicy = [
                'saveKey' => '$(etag)',
                'returnBody' => json_encode([
                    'file' => '$(etag)'
                ])
            ];
            $upToken = $auth->uploadToken($bucket, null, 3600, $putPolicy);
            return $upToken;
        });
    }

    public function upload(UploadedFile $file): array
    {
        $token = self::getUploadToken();

        $ext = $file->extension();
        $etg = Etag::sum($file->getPath() . '/' . $file->getFilename());
        $key = $etg[0] . '.' . $ext;

        $manager = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($result, $error) = $manager->putFile($token, $key, $file);
        if (empty($error)) {
            $result = ['file' => $this->config['domain'] . '/' . $result['file'] . '.' . $file->extension()];
            return $result;
        } else {
            throw new UploadException($error);
        }
    }
}
