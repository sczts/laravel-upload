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

    public function __construct()
    {
        $this->config = config('upload.services.qiniu');
        $this->bucket = $this->config['bucket'];
        $this->prefix = Str::finish($this->config['prefix'], '/');
    }

    public function getAuth()
    {
        $accessKey = $this->config['access_key'];
        $secretKey = $this->config['secret_key'];

        $auth = new Auth($accessKey, $secretKey);
        return $auth;
    }


    public function getUploadToken()
    {
        $auth = $this->getAuth();
        $putPolicy = [
            'saveKey' => $this->prefix . '$(etag)$(ext)',
            'returnBody' => json_encode([
                'file' => $this->config['domain'] . '/' . '$(key)',
                'key' => '$(key)'
            ])
        ];
        $upToken = $auth->uploadToken($this->bucket, null, 3600, $putPolicy);
        return $upToken;
    }

    /**
     * @param UploadedFile $file
     * @return array
     * @throws UploadException
     */
    public function upload(UploadedFile $file): array
    {
        $token = self::getUploadToken();

        $ext = $file->extension() == static::SUFFIX_JPEG ? static::SUFFIX_JPG : $file->extension();
        $etg = Etag::sum($file->getPath() . '/' . $file->getFilename());
        $key = sprintf('%s%s.%s', $this->prefix, array_shift($etg), $ext);
        $manager = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($result, $error) = $manager->putFile($token, $key, $file);
        return static::createBackData($result, $error, $ext);
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
                $item['name'] = $this->config['domain'] . '/' . $value['key'];
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
