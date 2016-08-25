<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/8/25 Time: 14:58
// +----------------------------------------------------------------------
namespace limx\tools\wx;
class Utils
{
    /**
     * [getNonceStr 获取随机时间戳]
     * @desc
     * @author limx
     * @param int $length
     * @return string
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * [setCache desc]
     * @desc
     * @author limx
     * @param $filename
     * @param $content
     */
    public static function setCache($filename, $content)
    {
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        file_put_contents($filename, serialize($content));
    }

    /**
     * [getCache desc]
     * @desc
     * @author limx
     * @param $filename
     * @return mixed
     */
    public static function getCache($filename)
    {
        return unserialize(trim(file_get_contents($filename)));
    }

    /**
     * [httpGet desc]
     * @desc
     * @author limx
     * @param $url
     * @return mixed
     */
    public static function httpGet($url)
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

    /**
     * [httpPost desc]
     * @desc
     * @author limx
     * @param $url
     * @param $data
     * @param string $type
     * @param array $header
     * @param bool $ssl
     * @return mixed
     */
    public static function httpPost($url, $data, $type = 'url', $header = NULL, $ssl = false)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        if ($ssl) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $ssl['sslcert_path']);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $ssl['sslkey_path']);
        }

        switch (strtolower($type)) {
            case 'url':
                $postFields = http_build_query($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                break;
            case 'json':
                $postFields = json_encode($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($postFields))
                );
                break;
            case 'data':
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                $postFields = http_build_query($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                break;
        }

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public static function sign($data, $key)
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = self::toUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * [getIp desc]
     * @desc
     * @author limx
     * @return string
     */
    public static function getIp()
    {
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;

    }

    public static function toUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    public static function xml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            return false;
        }

        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    public static function xmlToArray($xml)
    {
        if (!$xml) {
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
}