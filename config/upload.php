<?php

return [
    'default' => env('UPLOAD_DRIVER', ''),
    'drivers' => [
        'qiniu' => [
            'access_key' => env('QINIU_AK', ''),
            'secret_key' => env('QINIU_SK', ''),
            'bucket' => env('QINIU_BUCKET', ''),
            'domain' => env('QINIU_DOMAIN', ''),
        ],
    ],
    'allowed_ext' => [
        'jpg',
        'jpeg',
        'png',
    ],
];
