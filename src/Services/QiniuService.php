<?php

namespace Sczts\Upload\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
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

    public function __construct()
    {
        $this->config = config('upload.services.qiniu');
        $this->bucket = $this->config['bucket'];
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
        return Cache::remember('upload_token', 58, function () {
            $auth = $this->getAuth();
            $putPolicy = [
                'saveKey' => '$(etag)',
                'returnBody' => json_encode([
//                    'file' => $this->config['domain'] . '/$(etag)$(ext)'
                    'file' => $this->config['domain'] . '/$(etag)'
                ])
            ];
            $upToken = $auth->uploadToken($this->bucket, null, 3600, $putPolicy);
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
//            if (Str::endsWith($result['file'], '.tmp')) {
//                $url = str_replace('.tmp', ".$ext", $result['file']);
//            };
//            return ['file' => $url];
            return $result;
        } else {
            throw new UploadException($error);
        }
    }

    /**
     * @param $marker  上次列举返回的位置标记，作为本次列举的起点信息
     * @param $limit  本次列举的条目数
     * @param $prefix  要列取文件的公共前缀
     * @return mixed
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
            foreach ($result['items'] as $key => $value) {
                $data['items'][$key]['name'] = $this->config['domain'] . '/' . $value['key'];
                $data['items'][$key]['size'] = round($value['fsize'] / 1024, 2) . 'KB';
                $data['items'][$key]['mimeType'] = $value['mimeType'];
//                $data['items'][$key]['putTime'] = date('y-m-d H:i:s', $value['putTime']);
            }
            $data['marker'] = $result['marker'] ?? '';
            return $data;
        } else {
            throw new UploadException($error);
        }
    }
}
