<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/8/16 Time: 14:21
// +----------------------------------------------------------------------
require_once 'WxPay.Api.php';

class AppPay
{
    public static function getPayData($input)
    {
        if (empty($input)) {
            return false;
        }

        if ($input['result_code'] != 'SUCCESS' || $input['return_code'] != 'SUCCESS' || $input['return_msg'] != 'OK') {
            return false;
        }

        $data = array(
            "appid" => $input['appid'],
            "noncestr" => WxPayApi::getNonceStr(),
            "package" => "Sign=WXPay",
            "partnerid" => $input['mch_id'],
            "prepayid" => $input['prepay_id'],
            "timestamp" => time()
        );

        $data['sign'] = self::MakeSign($data);
        return $data;

    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public static function MakeSign($data)
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = self::ToUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . WxPayConfig::KEY;
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
}