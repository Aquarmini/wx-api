<?php
// +----------------------------------------------------------------------
// | Demo [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.lmx0536.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <http://www.lmx0536.cn>
// +----------------------------------------------------------------------
// | Date: 2016/8/25 Time: 17:01
// +----------------------------------------------------------------------
namespace limx\tools\wx\pay;

use Exception;

class WxPayException extends Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}