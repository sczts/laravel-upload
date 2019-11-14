<?php

namespace Sczts\Upload\Services;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Illuminate\Support\Facades\Cache;
use Sczts\Upload\Exceptions\UploadException;

class QiniuService
{
    private static $config;

    public static function getConfig()
    {
        if (!(static::$config)) {
            static::$config = config('upload.drivers.qiniu');
        }
        return static::$config;
    }


    public static function getUploadToken()
    {
        $config = static::getConfig();
        return Cache::remember('upload_token', 50, function () use ($config) {
            $accessKey = $config['access_key'];
            $secretKey = $config['secret_key'];
            $bucket = $config['bucket'];

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

    public static function upload($key, $file)
    {
        $token = self::getUploadToken();
        $config = self::getConfig();
        $manager = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($result, $error) = $manager->putFile($token, $key, $file);
        if (empty($error)) {
            $result = ['file' => $config['domain'] . '/' . $result['file']];
            return $result;
        } else {
            throw new UploadException($error);
        }
    }
}
