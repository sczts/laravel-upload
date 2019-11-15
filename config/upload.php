<?php

return [
    'default' => env('UPLOAD_DRIVER', ''),
    'drivers' => [
        'qiniu' => [
            'upload_max_size'=>env('UPLOAD_MAX_SIZE','2'), // 单位:/M
            'access_key' => env('QINIU_AK', ''),
            'secret_key' => env('QINIU_SK', ''),
            'bucket' => env('QINIU_BUCKET', ''),
            'domain' => env('QINIU_DOMAIN', ''),
            'allowed_ext' => [
                'jpg',
                'jpeg',
                'png',
            ],
        ],
    ],
];
