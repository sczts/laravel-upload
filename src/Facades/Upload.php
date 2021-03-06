<?php

namespace Sczts\Upload\Facades;

use Illuminate\Support\Facades\Facade;
use Sczts\Upload\Upload as UploadManager;

/**
 * Class UploadFacade
 * @package Sczts\Upload\Facades
 * @method static array upload($file, $returnBody = []);
 * @method static array uploadToken($returnBody = [], $expires = 3600);
 * @method static Upload channel($channel);
 * @method static string downloadUrl($url, $expires = 3600)
 * @method static Upload setPrefix($prefix = null)
 * @see \Sczts\Upload\Upload::class
 */
class Upload extends Facade
{
    /**
     * 获取组件的注册名称。
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return UploadManager::class;
    }
}
