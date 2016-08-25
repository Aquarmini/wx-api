<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | MSG: 使用官方SDK完成统一下单 然后再通过此类进行签名
// +----------------------------------------------------------------------
// | Date: 2016/8/25 Time: 17:29
// +----------------------------------------------------------------------
namespace limx\tools\wx\pay;

use limx\tools\wx\Utils;

class AppPay
{
    public static function getPayData($input, $key)
    {
        if (empty($input)) {
            return false;
        }

        if ($input['result_code'] != 'SUCCESS' || $input['return_code'] != 'SUCCESS' || $input['return_msg'] != 'OK') {
            return false;
        }

        $data = array(
            "appid" => $input['appid'],
            "noncestr" => Utils::getNonceStr(),
            "package" => "Sign=WXPay",
            "partnerid" => $input['mch_id'],
            "prepayid" => $input['prepay_id'],
            "timestamp" => time()
        );

        $data['sign'] = Utils::sign($data, $key);
        return $data;

    }
}