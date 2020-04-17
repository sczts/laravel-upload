<?php

namespace Sczts\Upload\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Qiniu\Auth;
use Qiniu\Etag;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Illuminate\Support\Facades\Cache;
use Sczts\Upload\Exceptions\UploadException;
use Sczts\Upload\UploadService;

class QiniuService implements UploadService
{
    private $config;
    private $bucket;
    private $prefix;
    const SUFFIX_JPEG = 'jpeg';
    const SUFFIX_JPG = 'jpg';

    public function __construct($config)
    {
        $this->config = $config;
        $this->bucket = $this->config['bucket'];
    }


    /**
     * 设置前缀，拷贝对象
     * @param $prefix
     * @return QiniuService
     */
    public function setPrefix($prefix)
    {
        $service = clone $this;
        $service->prefix = $prefix;
        return $service;
    }

    /**
     * 获取前缀
     * @return string
     */
    public function getPrefix()
    {
        if ($this->prefix) {
            return Str::finish($this->config['prefix'] . '/' . $this->prefix, '/');
        }
        return Str::finish($this->config['prefix'], '/');
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
     * @return string
     */
    public function uploadToken($returnBody = []): string
    {
        $auth = $this->getAuth();
        $putPolicy = [
            'saveKey' => $this->getPrefix() . '$(etag)$(ext)',
            'returnBody' => json_encode(array_merge([
                'file' => Str::finish($this->config['domain'],'/') . '$(key)',
                'key' => '$(key)',
                'name' => '$(fname)'
            ], $returnBody))
        ];
        $upToken = $auth->uploadToken($this->bucket, null, 3600, $putPolicy);
        return $upToken;
    }

    /**
     * 上传文件
     * @param UploadedFile $file
     * @param array $returnBody
     * @return array
     * @throws UploadException
     */
    public function upload(UploadedFile $file, $returnBody = []): array
    {
        $token = self::uploadToken($returnBody);

        $ext = $file->extension() == static::SUFFIX_JPEG ? static::SUFFIX_JPG : $file->extension();
        $etg = Etag::sum($file->getPath() . '/' . $file->getFilename());
        $key = sprintf('%s%s.%s', $this->getPrefix(), array_shift($etg), $ext);
        $manager = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($result, $error) = $manager->putFile($token, $key, $file);
        return static::createBackData($result, $error, $ext);
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

    /**
     * @param string $marker 上次列举返回的位置标记，作为本次列举的起点信息
     * @param int $limit 本次列举的条目数
     * @param string $prefix 要列取文件的公共前缀
     * @return array
     * @throws UploadException
     */
    public function fileList(string $marker, int $limit, string $prefix): array
    {
        $bucket_manager = new BucketManager($this->getAuth());

        $delimiter = '/';

        // 列举文件
        list($result, $error) = $bucket_manager->listFiles($this->bucket, $prefix, $marker, $limit, $delimiter);
        if (empty($error)) {

            $data = [];
            $data['items'] = array_map(function ($value) {
                $item['name'] = Str::finish($this->config['domain'],'/') . $value['key'];
                $item['size'] = round($value['fsize'] / 1024, 2) . 'KB';
                $item['mimeType'] = $value['mimeType'];
                $item['putTime'] = $this->putTimeFormat($value['putTime']);
                return $item;
            }, $result['items']);

            $data['marker'] = $result['marker'] ?? '';
            return $data;
        } else {
            throw new UploadException($error);
        }
    }

    /**
     * @param $put_time // 修改时间  秒/毫秒
     * @return false|string
     */
    protected function putTimeFormat($put_time)
    {
        $put_time = number_format($put_time, 0, '', '');
        $time_str = (int)substr($put_time, 0, 10);
        return Carbon::createFromTimestamp($time_str)->toDateTimeString();
    }

    /**
     * 处理返回的数据
     * @param $result
     * @param $error
     * @return array
     * @throws UploadException
     */
    protected static function createBackData($result, $error, string $ext): array
    {
        if (empty($error)) {
            if (Str::endsWith($result['file'], '.tmp')) {
                $result['file'] = str_replace('.tmp', ".$ext", $result['file']);
            };
            return $result;
        } else {
            throw new UploadException($error);
        }
    }
}
