## 前言

微信公众号JSAPI相关操作，目前支付API如下：

- 获取REFRESH TOKEN
- 刷新REFRESH TOKEN获取OPENID
- 根据OPENID获取用户信息
- 获取ACCESS TOKEN
- 获得TICKET
- 获取JSAPI配置参数

## 安装

```shell
composer require teemao/wechat-jssdk
```

## 用法

```php
<?php

namespace App\Http\Controllers;

use Teemao\WechatJssdk\Wechat;

class WechatController
{
    protected $config = [
        'appId' => 'xxxxxxxxxxxxx',
        'appSecret' => 'xxxxxxxxxxxxxxxxxxxxx',    
    ];
    
    // 获取用户信息
    public function getUserInfo(string $code)
    {
        $wechat = new Wechat($this->config);
        
        $refreshToken = $wechat->getRefreshToken($code);
        
        $openId = $wechat->getOpenid($refreshToken);
        
        $accessResult = $wechat->getAccessToken();
        
        $userInfo = $wechat->getUserInfo($accessResult['access_token'], $openId);
        
        return $userInfo;
    }   
    
    // 获取JSAPI配置
    public function getJsapiConfig()
    {
        $wechat = new Wechat($this->config);
        
        $accessResult = $wechat->getAccessToken();
        
        $ticketResult = $wechat->getTicket($accessResult['access_token']);
        
        $result = $wechat->getJsapiConfig($ticketResult['ticket']);
        
        return $result;
    }
}
```

## LICENSE

MIT