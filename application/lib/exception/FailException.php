<?php


namespace app\lib\exception;


class FailException extends BaseException
{
    public $code = '';
    public $msg = '';
    public $errorCode = 0;

}