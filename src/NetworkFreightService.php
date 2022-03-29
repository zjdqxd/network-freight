<?php

namespace ShaanXiNetworkFreight;

use App\Exceptions\NetworkFreightException;
use GuzzleHttp\Client;

class NetworkFreightService
{
    // 缓存token对应的key
    const NETWORK_FREIGHT_CACHE_TOKEN_KEY   = 'network_freight_cache_token_key';
    // 缓存扩展数据对应的key
    const NETWORK_FREIGHT_CACHE_EXTEND      = 'network_freight_cache_extend';

    // 业务编码
    const  PROVINCIAL_TRANSPORT = 'WLHY_YD1001';
    const  PROVINCIAL_AMOUNT    = 'WLHY_ZJ1001';
    const  PROVINCIAL_DRIVER    = 'WLHY_JSY1001';
    const  PROVINCIAL_VEHICLE   = 'WLHY_CL1001';
    const  REPORT_TYPE_MAP = [
        self::PROVINCIAL_TRANSPORT  => '上传运单',
        self::PROVINCIAL_AMOUNT     => '上传流水',
        self::PROVINCIAL_DRIVER     => '上传司机',
        self::PROVINCIAL_VEHICLE    => '上传车辆',
    ];
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $tokenSignUrl;

    /**
     * @var string
     */
    protected $refreshTokenUrl;

    /**
     * @var string
     */
    protected $sendRequestUrl;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $signKey;

    /**
     * @var string
     */
    protected $signSecret;

    /**
     * @var string
     */
    protected $iv;

    /**
     * @var bool
     */
    protected $isDebug = false;

    /**
     * 请求数据
     * @var array
     */
    protected $requestData;

    /**
     * 响应数据
     * @var array
     */
    protected $responseData;

    /**
     * @var string
     */
    protected $requestType;

    /**
     * TaxSuiDeService constructor.
     * @param array $config
     * @throws NetworkFreightException
     */
    public function __construct(array $config)
    {
        $this->setEnv($config);
        $this->username = data_get($config, 'username');
        $this->password = data_get($config, 'password');
        $this->signKey = data_get($config, 'signKey');
        $this->signSecret = data_get($config, 'signSecret');
        $this->iv = data_get($config, 'iv');
        $this->requestData = [];
        $this->requestType = 0;
        $this->responseData = [];
    }

    /**
     * @param $config
     * @throws NetworkFreightException
     */
    protected function setEnv($config)
    {
        $env    = data_get($config, 'env');
        switch ($env) {
            case 'dev':
                $this->tokenSignUrl     = 'http://sn.wccyjc.com:81/wccy-dc/token/sign';
                $this->refreshTokenUrl  = 'http://sn.wccyjc.com:81/wccy-dc/token/refresh';
                $this->sendRequestUrl   = 'http://sn.wccyjc.com:81/wccy-dc/send4Scts/v1/send';
                $this->isDebug          = true;
                break;
            case 'qa':
                $this->tokenSignUrl     = 'http://sn.wccyjc.com:81/test-wccy-dc/token/sign';
                $this->refreshTokenUrl  = 'http://sn.wccyjc.com:81/test-wccy-dc/token/refresh';
                $this->sendRequestUrl   = 'http://sn.wccyjc.com:81/test-wccy-dc/send2Logink/v1/send';
                break;
            case 'production':
                $this->tokenSignUrl     = 'http://sn.wccyjc.com:81/wccy-dc/token/sign';
                $this->refreshTokenUrl  = 'http://sn.wccyjc.com:81/wccy-dc/token/refresh';
                $this->sendRequestUrl   = 'http://sn.wccyjc.com:81/wccy-dc/send2Logink/v1/send';
                break;
            default:
                $this->_makeApiException('环境变量未设置', 0);
                break;
        }
    }

    /**
     * 上报驾驶员
     * @param array $data
     * @return mixed
     * @throws \Throwable
     */
    public function reportDriver(array $data)
    {
        $this->requestType = self::PROVINCIAL_DRIVER;
        $this->requestData = $data;
        return $this->basePost();
    }

    /**
     * 上报车辆
     * @param array $data
     * @return mixed
     * @throws \Throwable
     */
    public function reportVehicle(array $data)
    {
        $this->requestType = self::PROVINCIAL_VEHICLE;
        $this->requestData = $data;
        return $this->basePost();
    }

    /**
     * 上报资金流水
     * @param array $data
     * @return mixed
     * @throws \Throwable
     */
    public function reportCapitalFlow(array $data)
    {
        $this->requestType = self::PROVINCIAL_AMOUNT;
        $this->requestData = $data;
        return $this->basePost();
    }

    /**
     * 上报运单数据
     * @param array $data
     * @return mixed
     * @throws \Throwable
     */
    public function reportTransport(array $data)
    {
        $this->requestType = self::PROVINCIAL_TRANSPORT;
        $this->requestData = $data;
        return $this->basePost();
    }

    /**
     * @return mixed
     * @throws NetworkFreightException
     * @throws \Throwable
     */
    protected function basePost()
    {
        $this->getToken();
        $body = json_encode($this->getRootText());
        $userId = $this->getUserId();
        // 顺序不可修改
        $originData = [
            'actionType' => $this->requestType,
            'data'      => $body,
            'userId'    => $userId,
        ];
        $string     = $this->signKey
            // 顺序问题, 如需要修改请注意key顺序
            . "actionType={$this->requestType}&data={$body}&userId={$userId}"
            . $this->signKey;
        $originData['sign'] = md5(sha1($string));
        return $this->post($this->urlQuery($originData), []);
    }

    /**
     * @param array $originData
     * @return string
     */
    protected function urlQuery(array $originData): string
    {
        return $this->sendRequestUrl . "?" . http_build_query($originData);
    }

    /**
     * @return array[]
     */
    protected function getRootText(): array
    {
        $messageReferenceNumber     = substr(\Str::uuid()->toString(), 0, 35);
        $messageSendingDateTime     = now()->format('YmdHis');

        /** debug状态下报文自测试
        return [
        'root'  => [
        'body'  => $data,
        'header'    => [
        'messageReferenceNumber'    => $messageReferenceNumber,
        'messageSendingDateTime'    => $messageSendingDateTime,
        'senderCode'                => $this->username,
        ],
        ],
        ];
         */

        return [
            'root'  => [
                'body'  => [
                    'encryptedContent'  => $this->encryptPassword(json_encode($this->requestData)),
                ],
                'header'    => [
                    'messageReferenceNumber'    => $messageReferenceNumber,
                    'messageSendingDateTime'    => $messageSendingDateTime,
                    'senderCode'                => $this->username,
                ],
            ],
        ];
    }

    /**
     * @return string|mixed
     * @throws \Exception
     */
    protected function getUserId(): string
    {
        return data_get($this->getExtend(), 'userId');
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getExtend()
    {
        return  json_decode(cache(self::NETWORK_FREIGHT_CACHE_EXTEND, '{}'), true);
    }

    /**
     * 获取token
     * @throws \Exception
     */
    public function getToken()
    {
        return cache(self::NETWORK_FREIGHT_CACHE_TOKEN_KEY, function () {
            $data       = $this->_getToken();
            $token      = data_get($data, 'accessToken');
            $expire     = data_get($data, 'expire') ?: '7200';
            cache([
                self::NETWORK_FREIGHT_CACHE_TOKEN_KEY   => $token,
            ], now()->addSeconds($expire));
            cache([
                self::NETWORK_FREIGHT_CACHE_EXTEND      => json_encode($data),
            ], now()->addSeconds($expire));
            return $token;
        });
    }

    /**
     * 获取token
     */
    private function _getToken()
    {
        return $this->post($this->tokenSignUrl, [
            'userName'  => $this->username,
            'passWord'  => $this->encryptPassword($this->password),
        ], true);
    }

    /**
     * @param $string
     * @return string
     */
    protected function encryptPassword($string): string
    {
        $key    = substr(
            openssl_digest(
                openssl_digest(
                    $this->signSecret,
                    'sha1',
                    true
                ),
                'sha1',
                true
            ),
            0,
            16
        );
        return bin2hex(openssl_encrypt(
            $string,
            'AES-128-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $this->iv
        ));
    }

    /**
     * @param string $url
     * @param array $data
     * @param bool $isGetToken
     * @return mixed
     * @throws NetworkFreightException
     * @throws \Throwable
     */
    protected function post(string $url, array $data, $isGetToken=false)
    {
        $response = $this->client()->post(
            $url,
            array_filter(['form_params' => $data,] + $this->headers($isGetToken))
        );
        $contents = $response->getBody()->getContents();
        $this->responseData = [
            'code'      => $response->getStatusCode(),
            'headers'   => $response->getHeaders(),
            'body'      => $contents,
        ];
        $result = $this->_getResult($contents);
        if (200 != $response->getStatusCode()) {

            $this->_makeApiException('陕西省网络货运服务请求异常', NetworkFreightException::SERVICE_REQUEST);
        }
        $this->_isSuccess($result);

        return $result['content'];
    }

    /**
     * @param $isGetToken
     * @return array|\string[][]
     * @throws \Exception
     */
    protected function headers($isGetToken): array
    {
        if ($isGetToken) {

            return [];
        }
        if ($token  = $this->getToken()) {

            return [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ];
        }
        return [];
    }

    /**
     * @throws \Throwable
     */
    protected function client(): Client
    {
        return $this->client  = new Client([
            'timeout'         => 10,
//            'proxy'           => '192.168.1.3:8899',
        ]);
    }

    /**
     * 获取结果
     *
     * @param string $response 响应内容
     * @return  mixed               结果
     */
    private function _getResult(string $response)
    {
        return  json_decode($response, true);
    }

    /**
     * 是否成功
     *
     * @param array $result API访问结果
     * @return void
     * @throws \Exception
     * @throws NetworkFreightException
     */
    private function _isSuccess(array $result)
    {
        switch (data_get($result, 'code', '')) {
            case '0000': // 成功
                break;
            case '1003': // 授权失败, 可重新获取 token
                cache()->forget(self::NETWORK_FREIGHT_CACHE_TOKEN_KEY);
                cache()->forget(self::NETWORK_FREIGHT_CACHE_EXTEND);
                $this->isDebug
                    ? $this->_makeApiException(data_get($result, 'message'), NetworkFreightException::AUTH_FAILED)
                    : $this->_makeApiException('陕西省网络货运服务授权失败', NetworkFreightException::AUTH_FAILED);
                break;
            default:
                $this->isDebug
                    ? $this->_makeApiException(data_get($result, 'message'), NetworkFreightException::SERVICE_RESPONSE)
                    : $this->_makeApiException('陕西省网络货运服务响应异常', NetworkFreightException::SERVICE_RESPONSE);
                break;
        }
    }

    /**
     * 抛出接口异常
     *
     * @param string $message
     * @param int $code
     * @throws NetworkFreightException
     */
    private function _makeApiException(string $message, int $code)
    {
        $message = $message ?: '陕西省网络货运服务异常';
        throw   new NetworkFreightException($message, $code, [
            'requestType' => $this->requestType ?? $code,
            'requestData' => $this->requestData,
            'responseData' => $this->responseData,
        ]);
    }
}
