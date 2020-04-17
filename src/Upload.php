<?php

namespace Sczts\Upload;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Sczts\Upload\Exceptions\UploadException;

class Upload
{
    /**
     * @var UploadService
     */
    private  $service;
    private $channels = [];
    private $settings;

    public function __construct()
    {
        $channel = config('upload.default');
        $this->channel($channel);
    }

    /**
     * 切换上传通道
     * @param $channel
     * @return mixed
     * @throws UploadException
     */
    public function channel($channel){
        if (!Arr::exists($this->channels,$channel)){
            $config = config('upload.channels.'.$channel,false);
            if (!$config){
                throw new UploadException('上传通道"'.$channel.'"不存在');
            }
            $this->settings = array_merge(config('upload.settings'),$config);
            $this->channels[$channel] = new $config['service']($this->settings);
        }
        $this->service = $this->channels[$channel];
        return $this;
    }


    /**
     * 获取上传token
     * @param array $returnBody
     * @return mixed
     */
    public function uploadToken($returnBody = [])
    {
        return $this->service->uploadToken($returnBody);
    }


    /**
     * 获取下载链接
     * @param $url
     * @param int $expires
     * @return string
     */
    public function downloadUrl($url, $expires = 3600)
    {
        return $this->service->downloadUrl($url, $expires);
    }

    /**
     * 文件上传
     * @param UploadedFile $file
     * @param string $prefix
     * @return mixed
     * @throws UploadException
     */
    public function upload(UploadedFile $file, $prefix = "")
    {
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
        return $this->service->upload($file, $prefix);
    }




    public function fileList($marker, $limit, $prefix)
    {
        return $this->service->fileList($marker, $limit, $prefix);
    }

}
