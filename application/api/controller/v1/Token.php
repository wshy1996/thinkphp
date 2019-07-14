<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\AppToken;
use app\api\service\UserToken;
use app\api\validate\AppTokenGet;
use app\api\validate\TokenGet;

class Token extends BaseController
{
    public function getToken($code = '')
    {
        (new TokenGet())->goCheck();
        $userToken = new UserToken($code);
        $token = $userToken->get();
        return [
            'token' => $token
        ];
    }

    /**
     * @param string $ac用户名
     * @param string $se密码
     * @url
     */
    public function getAppToken($ac='',$se ='')
    {
        (new AppTokenGet())->goCheck();
        $app = new AppToken();
        $token = $app->getToken($ac,$se);
        return [
            'token' => $token
        ];
    }

}