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
namespace limx\tools\wx\pay;

use limx\tools\wx\Utils;
use limx\tools\wx\pay\WxPayException;

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
        $data['nonce_str'] = Utils::getNonceStr();
        $data['sign'] = '';
        $data['mch_billno'] = $data['mch_id'] . Date('Ymd') . Date('His') . rand(1000, 9999);
        $data['client_ip'] = Utils::getIp();

        $data['sign'] = Utils::sign($data, $key);
        return $data;
    }

    public static function sendRedPack($data, $cert)
    {
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $xml = Utils::xml($data);
        $res = Utils::httpPost($url, $xml, 'data', NULL, $cert);
        return Utils::xmlToArray($res);
    }


}
