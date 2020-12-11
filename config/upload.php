<?php

use Sczts\Upload\Services\QiniuService;

return [
    /**
     * 默认上传通道
     */
    'default' => env('UPLOAD_CHANNEL', 'qiniu'),

    /**
     * 上传通道配置，通道中可复写 settings 中的默认配置
     */
    'channels' => [
        'qiniu' => [
            'service' => QiniuService::class,
            'access_key' => env('QINIU_AK', ''),
            'secret_key' => env('QINIU_SK', ''),
            'bucket' => env('QINIU_BUCKET', ''),
            'domain' => env('QINIU_DOMAIN', ''),
            'callback_url' => env('QINIU_CALLBACK_URL', ''),
        ],
        'qiniu_private' => [
            'service' => QiniuService::class,
            'access_key' => env('QINIU_AK', ''),
            'secret_key' => env('QINIU_SK', ''),
            'bucket' => env('QINIU_BUCKET_PRIVATE', ''),
            'domain' => env('QINIU_DOMAIN_PRIVATE', ''),
            'callback_url' => env('QINIU_CALLBACK_URL_PRIVATE', ''),
        ],
    ],


    /**
     * 默认上传配置
     */
    'settings' => [
        /**
         * 文件上传统一前缀
         */
        'prefix' => env('UPLOAD_PREFIX', null),   // 文件前缀


        /**
         * 前缀是否附加上传日期
         */
        'append_upload_date' => false,


        /**
         * 文件上传大小限制 单位MB
         */
        'max_size' => env('UPLOAD_MAX_SIZE', 10),


        /**
         * 允许上传的文件后缀类型
         */
        'allowed_ext' => [
            'jpg',
            'jpeg',
            'png',
            'xls',
            'xlsx',
            'doc',
            'docx',
            'ppt',
            'txt',
        ],
    ]
];
