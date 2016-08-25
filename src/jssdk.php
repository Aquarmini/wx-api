<?php

class JSSDK
{
    protected $appId;
    protected $appSecret;

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array("appId" => $this->appId, "nonceStr" => $nonceStr, "timestamp" => $timestamp, "url" => $url, "signature" => $signature, "rawString" => $string);
        return $signPackage;
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket($file = 'cache/jsapi_ticket.php')
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        if (!file_exists($file)) {
            $this->set_php_file($file, '{"jsapi_ticket":"","expire_time":0}');
        }

        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file($file));
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $this->set_php_file($file, json_encode($data));
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }

    protected function getAccessToken($file = 'cache/access_token.php')
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        if (!file_exists($file)) {
            $this->set_php_file($file, '{"access_token":"","expire_time":0}');
        }

        $data = json_decode($this->get_php_file($file));
        if ($data->expire_time < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $this->set_php_file($file, json_encode($data));
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }

    protected function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        /*
            CURLOPT_SSL_VERIFYHOST
            设置为 1 是检查服务器SSL证书中是否存在一个公用名(common name)。
            译者注：公用名(Common Name)一般来讲就是填写你将要申请SSL证书的域名 (domain)或子域名(sub domain)。
            设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配。 在生产环境中，这个值应该是 2（默认值）。
            值 1 的支持在 cURL 7.28.1 中被删除了。
        */
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    protected function get_php_file($filename)
    {
        return trim(substr(file_get_contents($filename), 15));
    }

    protected function set_php_file($filename, $content)
    {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }

    protected function lfnCurlGet($url, $headerData = array())
    {

        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($headerData)) {
            $headerArr = array();
            foreach ($headerData as $i => $v) {
                $headerArr[] = $i . ':' . $v;
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArr);
        }
        //执行命令
        $result = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);

        return $result;
    }

    /**
     * 通过CURL发送HTTP请求
     * @param string $url //请求URL
     * @param array $postFields //请求参数
     * @return mixed
     */
    protected function lfnCurlPost($url, $postFields, $headerData = array())
    {

        $postFields = http_build_query($postFields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        if (!empty($headerData)) {
            $headerArr = array();
            foreach ($headerData as $i => $v) {
                $headerArr[] = $i . ':' . $v;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    protected function lfnCurlPostJson($url, $data)
    {
        $data_string = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }

    /**
     * [lfnGetAccessToken 获取授权的AccessToken]
     * @author limx
     * @param string $file
     * @return mixed
     */
    protected function lfnGetAccessToken($file = 'cache/access_token.php')
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        if (!file_exists($file)) {
            $this->set_php_file($file, '{"access_token":"","expire_time":0}');
        }
        $data = json_decode($this->get_php_file($file));

        if ($data->expire_time < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            //$res = json_decode($this -> httpGet($url));
            $res = json_decode($this->lfnCurlGet($url));

            $access_token = $res->access_token;
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $this->set_php_file($file, json_encode($data));
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }

}
