<?php

namespace ShaanXiNetworkFreight;

use Exception;

class NetworkFreightException extends Exception
{
    //陕西省网络货运服务请求异常
    const SERVICE_REQUEST   = '1004';
    //陕西省网络货运服务响应异常
    const SERVICE_RESPONSE  = '1005';
    //授权失败
    const AUTH_FAILED       = '1003';
    //请求超时
    const REQUEST_TIME_OUT  = '1002';

    public $messageData;

    function __construct($msg = '陕西省网络货运服务异常', int $code = null, array $messageData = [])
    {
        $code = $code ?: config('error.default');

        $this->messageData = [
            'requestType'   => data_get($messageData, 'requestType'),
            'requestData'   => data_get($messageData, 'requestData'),
            'responseData'  => data_get($messageData, 'responseData'),
        ];

        parent::__construct($msg, $code, null);
    }
}
