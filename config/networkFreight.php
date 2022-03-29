<?php

return [
    // 陕西网络货运
    'shaanxi'   => [
        // 环境变量: 开发dev, 测试qa, 正式production, 会对应不同的请求地址
        'env'           => env('NETWORK_FREIGHT_ENV', 'production'),
        'username'      => env('NETWORK_FREIGHT_USERNAME'),
        'password'      => env('NETWORK_FREIGHT_PASSWORD'),
        'signKey'       => env('NETWORK_FREIGHT_SIGN_KEY'),
        'signSecret'    => env('NETWORK_FREIGHT_SIGN_SECRET'),

        'iv'            => env('NETWORK_FREIGHT_SIGN_IV'),
    ],
];
