<?php
/**
 *
 * 微信支付API异常类
 * @author widyhu
 *
 */
namespace limx\tools\wx\pay\lib;

use Exception;

class WxPayException extends Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}
