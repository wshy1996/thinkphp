<?php


namespace app\lib\exception;


class CategorException extends BaseException
{
    public $code = 404;
    public $msg = '分类不存在';
    public $errorCode = 50000;

}