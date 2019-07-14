<?php


namespace app\api\validate;


use app\lib\exception\BaseException;

class WxChatException extends BaseException
{
    public $code = 500;
    public $msg = '微信服务器接口调用错误';
    public $errorCode = 999;

}