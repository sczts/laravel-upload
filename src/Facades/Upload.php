<?php

namespace Sczts\Upload\Facades;

use Illuminate\Support\Facades\Facade;
use Sczts\Upload\Upload as UploadManager;

/**
 * Class UploadFacade
 * @package Sczts\Upload\Facades
 * @method static array upload($file);
 * @method static array fileList($marker,$limit,$prefix);
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
