<?php


namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Request;

class Token
{
    public static function generateToken()
    {
        $randChars = getRandChars();
        $timestamp = $_SERVER['REQUEST_TIME'];
        $salt = config('secure.token_salt');
        return md5($randChars.$timestamp.$salt);
    }
    public static function getCurrentTokenvar($key)
    {
        $token = Request::instance()->header('token');
        $vars = Cache::get($token);
        if (!$vars){
            throw new TokenException();
        }else{
            if (!is_array($vars)){
                $vars = json_decode($vars);
            }
            if (array_key_exists($key,$vars)){
                return $vars[$key];
            }else{
                throw new Exception('尝试获取的token变量不存在');
            }
        }
    }
    public static function getCurrentUid()
    {
        $uid = self::getCurrentTokenvar('uid');
        return $uid;

    }

    /**
     * 用户管理员权限
     * @return bool
     * @throws Exception
     * @throws ForbiddenException
     * @throws TokenException
     */
    public static function needPrimaryScope()
    {
        $scope = self::getCurrentTokenvar('scope');
        if ($scope){
            if ($scope>=ScopeEnum::User){
                return true;
            }else{
                throw new ForbiddenException();
            }
        }else{
            throw new TokenException();
        }
    }

    /**
     * 用户权限
     * @return bool
     * @throws Exception
     * @throws ForbiddenException
     * @throws TokenException
     */
    public static function needExclusiveScope()
    {
        $scope = self::getCurrentTokenvar('scope');
        if ($scope){
            if ($scope == ScopeEnum::User){
                return true;
            }else{
                throw new ForbiddenException();
            }
        }else{
            throw new TokenException();
        }
    }
    public static function isValidOperate($checkedUID)
    {
        if (!$checkedUID){
            throw new Exception('检测UID必须传入被检测的UID');
        }
        $currentOperateUID = self::getCurrentUid();
        if ($checkedUID == $currentOperateUID){
            return true;
        }
        return false;
    }

}