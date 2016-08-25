<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/8/25 Time: 12:08
// +----------------------------------------------------------------------
require_once 'WxPay.Exception.php';

class RedPack
{
    public static function getData($data, $key)
    {
        if (empty($data['mch_id'])) {
            throw new WxPayException("缺少红包接口必填参数mcn_id！");
        }
        if (empty($data['wxappid'])) {
            throw new WxPayException("缺少红包接口必填参数wxappid！");
        }
        if (empty($data['send_name'])) {
            throw new WxPayException("缺少红包接口必填参数send_name！");
        }
        if (empty($data['re_openid'])) {
            throw new WxPayException("缺少红包接口必填参数re_openid！");
        }
        if (empty($data['total_amount'])) {
            throw new WxPayException("缺少红包接口必填参数total_amount！");
        }
        if (empty($data['total_num'])) {
            $data['total_num'] = 1;
        }
        if (empty($data['wishing'])) {
            $data['wishing'] = '红包祝福语';
        }
        if (empty($data['act_name'])) {
            $data['act_name'] = '活动名称';
        }
        if (empty($data['remark'])) {
            $data['remark'] = '备注信息';
        }
        $data['nonce_str'] = self::getNonceStr();
        $data['sign'] = '';
        $data['mch_billno'] = $data['mch_id'] . Date('Ymd') . Date('His') . rand(1000, 9999);
        $data['client_ip'] = self::getIp();

        $data['sign'] = self::MakeSign($data, $key);
        return $data;
    }

    public static function sendRedPack($data)
    {
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $xml = self::ToXml($data);
        dump($xml);
        return self::postXmlCurl($xml, $url);
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public static function MakeSign($data, $key)
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = self::ToUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    public static function ToUrlParams($data)
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

    public static function ToXml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            throw new WxPayException("数组数据异常！");
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

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        //如果有配置代理这里就设置代理
//        if (WxPayConfig::CURL_PROXY_HOST != "0.0.0.0"
//            && WxPayConfig::CURL_PROXY_PORT != 0
//        ) {
//            curl_setopt($ch, CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
//            curl_setopt($ch, CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
//        }
        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($useCert == true) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }
}
