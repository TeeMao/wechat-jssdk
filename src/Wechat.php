<?php

namespace Teemao\WechatJssdk;

use GuzzleHttp\Client;

class Wechat
{
    /**
     * @var mixed
     */
    private static $appId;
    private static $appSecret;
    private $client;

    public function __construct(array $config = [])
    {
        if (!$config || !isset($config['appId']) || !isset($config['appSecret']))
            throw new \Exception('参数缺失');

        $this->client = new Client();

        self::$appId = $config['appId'];
        self::$appSecret = $config['appSecret'];
    }

    /**
     * User: TeeMao
     * DateTime: 2021/8/17 1:24 下午
     * Describe: 获取REFRESH TOKEN
     * Version: V1.0
     * @param string $code
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRefreshToken(string $code)
    {
        $url = $this->getUrl(__FUNCTION__, [self::$appId, self::$appSecret, $code]);

        $result = $this->get($url);

        if (isset($result['errcode']))
            throw new \Exception($result['errmsg']);

        return $result['refresh_token'];
    }

    /**
     * User: TeeMao
     * DateTime: 2021/8/17 1:26 下午
     * Describe: 刷新REFRESH TOKEN获取openid
     * Version: V1.0
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOpenid(string $refreshToken)
    {
        $url = $this->getUrl(__FUNCTION__, [self::$appId, $refreshToken]);

        $result = $this->get($url);

        if (isset($result['errcode']))
            throw new \Exception($result['errmsg']);

        return $result['openid'];
    }

    /**
     * User: TeeMao
     * DateTime: 2021/8/17 1:26 下午
     * Describe: 根据OPENID获取用户信息
     * Version: V1.0
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserInfo(string $accessToken, string $openId)
    {
        $url = $this->getUrl(__FUNCTION__, [$accessToken, $openId]);

        $result = $this->get($url);

        if (isset($result['errcode']))
            throw new \Exception($result['errmsg']);

        return $result;
    }

    /**
     * User: TeeMao
     * DateTime: 2021/8/17 1:28 下午
     * Describe: 获取ACCESS TOKEN
     * Version: V1.0
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccessToken()
    {
        $url = $this->getUrl(__FUNCTION__, [self::$appId, self::$appSecret]);

        $result = $this->get($url);

        if (isset($result['errcode']))
            throw new \Exception($result['errmsg']);

        return $result;
    }

    /**
     * User: TeeMao
     * DateTime: 2021/8/17 1:29 下午
     * Describe: 获得jsapi_ticket
     * Version: V1.0
     * @param string $accessToken
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTicket(string $accessToken)
    {
        $url = $this->getUrl(__FUNCTION__, [$accessToken]);

        $result = $this->get($url);

        if ($result['errcode'] != 0)
            throw new \Exception($result['errmsg']);

        return $result;
    }

    /**
     * User: TeeMao
     * DateTime: 2021/8/17 1:37 下午
     * Describe: 获取JSAPI配置参数
     * Version: V1.0
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getJsapiConfig(string $ticket)
    {

        $timestamp = time();

        $nonceStr = $this->createNonceStr();

        $url = 'http://www.baidu.com';


        $signature = sha1($this->ASCII(['jsapi_ticket' => $ticket, 'timestamp' => $timestamp, 'nonceStr' => $nonceStr, 'url' => $url]));

        return [
            'appId' => self::$appId,
            'timestamp' => $timestamp,
            'nonceStr' => $nonceStr,
            'signature' => $signature,
        ];
    }

    /**
     * User: TeeMao
     * DateTime: 2021/8/17 11:26 上午
     * Describe: 生成随机字符串
     * Version: V1.0
     * @param int $length
     * @return string
     */
    private function createNonceStr($length = 16) {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        $str = "";

        for ($i = 0; $i < $length; $i++) {

            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);

        }

        return $str;
    }

    /**
     * User: TeeMao
     * DateTime: 2021/8/17 11:24 上午
     * Describe: ASCII编码排序
     * Version: V1.0
     * @param array $params
     * @return false|string
     */
    private function ASCII($params = array()){

        if(!empty($params)){

            $p =  ksort($params);

            if($p){

                $str = '';

                foreach ($params as $k=>$val){

                    $str .= $k .'=' . $val . '&';
                }

                $strs = rtrim($str, '&');

                return $strs;
            }
        }

        return false;
    }

    /**
     * User: TeeMao
     * DateTime: 2021/8/17 2:07 下午
     * Describe: get 请求
     * Version: V1.0
     * @param $url
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function get($url)
    {
        $result = $this->client->get($url);

        $result = (string) $result->getBody();

        return json_decode($result, true);
    }

    /**
     * User: TeeMao
     * DateTime: 2021/8/17 1:57 下午
     * Describe: 获取请求API URL
     * Version: V1.0
     * @param string $type
     * @param array $params
     * @return string
     */
    private function getUrl(string $type, array $params)
    {
        switch ($type) {
            case 'getRefreshToken':

                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code";
                break;
            case 'getOpenid':

                $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=%s&grant_type=refresh_token&refresh_token=%s";
                break;
            case 'getUserInfo':

                $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN";
                break;
            case 'getAccessToken':

                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";
                break;
            case 'getTicket':

                $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi";
                break;
        }

        return sprintf($url, ...$params);
    }
}