<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/8/25 Time: 16:04
// +----------------------------------------------------------------------
namespace limx\tools\wx;

use limx\tools\wx\Utils;

class OAuth
{
    public $code = "";
    public $state = "";
    public $redirectUrl = "";

    protected $appId;
    protected $appSecret;

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function setRedirectUrl($url)
    {
        $this->redirectUrl = urlencode($url);
    }

    /**
     * [WxGetUserInfo 获取用户授权基本信息]
     * @author limx
     * @return array|mixed
     */
    public function getUserInfo()
    {
        // 获取AccessToken
        $accessToken = $this->getOAuthAccessToken();
        //获取基本信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $accessToken["access_token"] . "&openid=" . $accessToken["openid"] . "&lang=zh_CN";

        $res = json_decode(Utils::httpGet($url), true);

        return $res;
    }

    private function getOAuthAccessToken()
    {
        if ($this->code == "") {
            $this->getCode();
            exit;
        }
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appId&secret=$this->appSecret&code=$this->code&grant_type=authorization_code";
        $res = json_decode(Utils::httpGet($url), true);

        return $res;
    }

    private function getCode()
    {
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appId&redirect_uri=$this->redirectUrl&response_type=code&scope=snsapi_userinfo&state=$this->state#wechat_redirect";
        Header("Location: $url");
    }

}