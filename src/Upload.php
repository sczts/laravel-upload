<?php

namespace Sczts\Upload;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Sczts\Upload\Exceptions\UploadException;

class Upload
{
    /**
     * @var UploadService
     */
    private $service;
    private $channels = [];
    private $settings;
    private $config;

    public function __construct()
    {
        $channel = config('upload.default');
        $this->settings = config('upload.settings');
        $this->channel($channel);
    }

    /**
     * 切换上传通道
     * @param $channel
     * @return $this
     * @throws UploadException
     * @throws \ReflectionException
     */
    public function channel($channel)
    {
        if (!Arr::exists($this->channels, $channel)) {
            $config = config('upload.channels.' . $channel, false);

            if (!$config) {
                throw new UploadException('上传通道"' . $channel . '"不存在');
            }

            $interfaceNames = (new \ReflectionClass($config['service']))->getInterfaceNames();
            if (!in_array(UploadService::class, $interfaceNames)) {
                throw new UploadException($config['service'] . ' must instanceof UploadService');
            }

            // channel中的配置可覆盖settings的配置
            foreach ($this->settings as $key => $setting){
                if (key_exists($key,$config)){
                    $this->settings[$key] = $config[$key];
                }
            }

            $this->channels[$channel] = new $config['service']($config);
        }
        $this->service = $this->channels[$channel];
        $this->setPrefix();

        return $this;
    }


    /**
     * 获取上传token
     * @param array $returnBody
     * @param int $expires
     * @return mixed
     */
    public function uploadToken($returnBody = [], $expires = 3600)
    {
        return $this->service->uploadToken($returnBody, $expires);
    }


    /**
     * 获取临时下载链接
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
     * @param array $returnBody
     * @return mixed
     * @throws UploadException
     */
    public function upload(UploadedFile $file, $returnBody = [])
    {
        $max_size = $this->settings['max_size'];
        $size = round($file->getSize() / 1024 / 1024, 2);
        if ($size > $max_size) {
            throw new UploadException($size . 'M超过最大允许值');
        }

        $allow_ext = $this->settings['allowed_ext'];
        $ext = $file->extension();
        if (!(in_array($ext, $allow_ext) || in_array('*', $allow_ext))) {
            throw new UploadException('不允许上传后缀为 ' . $ext . ' 的文件');
        }

        return $this->service->upload($file, $returnBody);
    }

    /**
     * @param null $prefix
     * @return $this
     */
    public function setPrefix($prefix = null)
    {
        $default = $this->settings['prefix'] ?? '';

        if ($default != '') {
            $default = Str::finish($default, '/');
        }
        if ($this->settings['append_upload_date']) {
            $default = $default . date('Ymd') . '/';
        }
        $prefix = $default . $prefix;
        $this->service->setPrefix($prefix);
        return $this;
    }
}
