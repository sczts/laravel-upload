<?php

namespace Sczts\Upload\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Qiniu\Auth;
use Qiniu\Etag;
use Qiniu\Storage\UploadManager;
use Sczts\Upload\Exceptions\UploadException;
use Sczts\Upload\UploadService;

class QiniuService implements UploadService
{
    private $config;
    private $prefix;

    const SUFFIX_JPEG = 'jpeg';
    const SUFFIX_JPG = 'jpg';

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * 获取前缀
     * @return string
     */
    public function getPrefix()
    {
        if ($this->prefix) {
            return Str::finish($this->prefix, '/');
        }
        return '';
    }


    private function getAuth()
    {
        $accessKey = $this->config['access_key'];
        $secretKey = $this->config['secret_key'];
        $auth = new Auth($accessKey, $secretKey);
        return $auth;
    }

    /**
     * 获取上传配置
     * @param array $returnBody
     * @param int $expires
     * @return string
     */
    public function uploadToken(array $returnBody = [], int $expires = 3600): string
    {
        $auth = $this->getAuth();
        $putPolicy = [
            'saveKey' => $this->getPrefix() . '$(etag)$(ext)',
            'returnBody' => json_encode(array_merge([
                'file' => Str::finish($this->config['domain'], '/') . '$(key)',
                'key' => '$(key)',
                'name' => '$(fname)',
                'size' => '$(fsize)',
            ], $returnBody))
        ];
        $upToken = $auth->uploadToken($this->config['bucket'], null, $expires, $putPolicy);
        return $upToken;
    }

    /**
     * 上传文件
     * @param UploadedFile $file
     * @param array $returnBody
     * @return array
     * @throws UploadException
     */
    public function upload(UploadedFile $file, array $returnBody = []): array
    {
        $token = self::uploadToken($returnBody);
        $ext = $file->getClientOriginalExtension();
        $etg = Etag::sum($file->getPath() . '/' . $file->getFilename());
        $key = sprintf('%s%s.%s', $this->getPrefix(), array_shift($etg), $ext);
        $manager = new UploadManager();
        list($result, $error) = $manager->putFile($token, $key, $file);
        if (!empty($error)) {
            throw new UploadException($error);
        }
        $result['name'] = $file->getClientOriginalName();
        return $result;
    }

    /**
     * 获取私有文件下载链接
     * @param $url
     * @param int $expires
     * @return string
     */
    public function downloadUrl($url, $expires = 3600): string
    {
        return $this->getAuth()->privateDownloadUrl($url, $expires);
    }

}
