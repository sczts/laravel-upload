<?php

namespace Sczts\Upload\Facades;

use Illuminate\Support\Facades\Facade;
use Sczts\Upload\Upload;

/**
 * Class UploadFacade
 * @package Sczts\Upload\Facades
 * @method static array upload($file);
 * @see \Sczts\Upload\Upload::class
 */
class UploadFacade extends Facade
{
    /**
     * 获取组件的注册名称。
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Upload::class;
    }
}
