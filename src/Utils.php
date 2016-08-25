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
namespace limx\tools;
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
}