<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/8/25 Time: 16:35
// +----------------------------------------------------------------------
namespace limx\tools\wx;

use limx\tools\wx\JsSdk;

class TempMsg extends JsSdk
{
    public static function send($openid, $tem_id, $url, $ddata)
    {
        $jssdk = new JsSdk();
        $acc_token = $jssdk->lfnGetAccessToken();
        $data['touser'] = $openid;
        $data['template_id'] = $tem_id;
        $data['url'] = $url;
        $data['data'] = $ddata;
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $acc_token;
        $res = Utils::httpPost($url, $data, 'json');
        $res = json_decode($res, true);
        return $res;
    }
}