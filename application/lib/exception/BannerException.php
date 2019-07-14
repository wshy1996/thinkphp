<?php


namespace app\lib\exception;


class BannerException extends BaseException
{
    public $code = 400;
    public $msg = 'banner 不存在';
    public $error_Code = 20000;


}