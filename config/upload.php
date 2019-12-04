<?php

return [
    'default' => env('UPLOAD_DRIVER', ''),
    'services' => [
        'qiniu' => [
            'service' => \Sczts\Upload\Services\QiniuService::class,
            'access_key' => env('QINIU_AK', ''),
            'secret_key' => env('QINIU_SK', ''),
            'bucket' => env('QINIU_BUCKET', ''),
            'domain' => env('QINIU_DOMAIN', ''),
            'prefix'=>env('APP_NAME','')
        ],
    ],
    'max_size'=>env('UPLOAD_MAX_SIZE','2'), // 最大上传大小:/M
    'allowed_ext' => [
        'jpg',
        'jpeg',
        'png',
    ],
];
