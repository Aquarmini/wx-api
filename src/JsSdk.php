<?php
namespace limx\tools;

use limx\tools\Utils;

class JsSdk
{
    protected $appId;
    protected $appSecret;
    public $ticket_path = '';
    public $token_path = '';

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket($this->ticket_path);

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = Utils::getNonceStr(16);

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array("appId" => $this->appId, "nonceStr" => $nonceStr, "timestamp" => $timestamp, "url" => $url, "signature" => $signature, "rawString" => $string);
        return $signPackage;
    }

    private function getJsApiTicket($file = 'cache/jsapi_ticket.php')
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        if (!file_exists($file)) {
            $data['jsapi_ticket'] = '';
            $data['expire_time'] = 0;
            Utils::setCache($file, $data);
        }

        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = Utils::getCache($file);
        if ($data['expire_time'] < time()) {
            $accessToken = $this->getAccessToken($this->token_path);
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode(Utils::httpGet($url), true);
            $ticket = $res['ticket'];
            if ($ticket) {
                $data['expire_time'] = time() + 7000;
                $data['jsapi_ticket'] = $ticket;
                Utils::setCache($file, $data);
            }
        } else {
            $ticket = $data['jsapi_ticket'];
        }

        return $ticket;
    }

    protected function getAccessToken($file = 'cache/access_token.php')
    {
        /** access_token 应该全局存储与更新 */
        if (!file_exists($file)) {
            /** access_token文件初始化 */
            $data['access_token'] = '';
            $data['expire_time'] = 0;
            Utils::setCache($file, $data);
        }

        $data = Utils::getCache($file);
        if ($data['expire_time'] < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode(Utils::httpGet($url), true);
            $access_token = $res['access_token'];
            if ($access_token) {
                $data['expire_time'] = time() + 7000;
                $data['access_token'] = $access_token;
                Utils::setCache($file, $data);
            }
        } else {
            $access_token = $data['access_token'];
        }
        return $access_token;
    }
}
