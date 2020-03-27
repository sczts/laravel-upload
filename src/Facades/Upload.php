<?php

namespace Sczts\Upload\Facades;

use Illuminate\Support\Facades\Facade;
use Sczts\Upload\Upload as UploadManager;

/**
 * Class UploadFacade
 * @package Sczts\Upload\Facades
 * @method static array upload($file, $custom_prefix = '', $expire = 3600);
 * @method static array getUploadToken($custom_prefix = '', $expire = 3600)
 * @method static array fileList($marker, $limit, $prefix);
 * @see UploadManager::class
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
