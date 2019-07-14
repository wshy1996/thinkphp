<?php


namespace app\lib\exception;


class TokenException extends BaseException
{
    public $code = 401;
    public $msg ='Token已经过期或无效Token';
    public $errorCode = 10001;

}