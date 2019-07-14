<?php


namespace app\api\controller;


use app\api\service\Token as TokenService;
use think\Controller;

class BaseController extends Controller
{
    /**
     * @throws \app\lib\exception\ForbiddenException
     * @throws \app\lib\exception\TokenException
     */
    protected function checkPrimaryScope()
    {
        TokenService::needPrimaryScope();

    }
    protected function checkExclusiveScope()
    {
        TokenService::needExclusiveScope();
    }

}