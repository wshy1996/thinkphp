<?php


namespace app\lib\exception;


class SuccessException extends BaseException
{
    public $code = 201;
    public $msg = 'SUCCESS';
    public $errorCode = 0;

}