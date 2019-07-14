<?php


namespace app\api\service;


use app\api\model\User as UserModel;
use app\api\validate\WxChatException;
use app\lib\enum\ScopeEnum;
use app\lib\exception\TokenException;
use think\Exception;

class UserToken extends Token
{
    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginUrl;
    public function __construct($code)
    {
        $this->code = $code;
        $this->wxAppID = config('wx.app_id');
        $this->wxAppSecret = config('wx.app_secret');
        $this->wxLoginUrl = sprintf(config('wx.login_url'),$this->wxAppID,$this->wxAppSecret,$this->code);
    }

    public function get()
    {
        $result = curl_get($this->wxLoginUrl);
        $wxResult = json_decode($result,true);
        if (empty($wxResult)){
            throw new Exception('获取session_key和openID时异常，微信内部错误');
        }else{
            $loginFail = array_key_exists('errcode',$wxResult);
            if ($loginFail){
                $this->processLoginError($wxResult);
            }else{
                return $this->getToken($wxResult);
            }
        }
    }
    private function getToken($wxResult)
    {
        /**
         * openid
         * 判断是否存在
         * 存在不处理，不存在新增user
         * 生成令牌，准备数据，写入缓存
         * key token
         * value wxResult uid scope
         */
        $openid = $wxResult['openid'];
        $user = UserModel::getByOpenID($openid);
        if ($user){
            $uid = $user->id;
        }else{
            $uid = $this->newUser($openid);
        }
        $cacheValue = $this->prepareCacheValue($wxResult,$uid);
        $token = $this->saveTCache($cacheValue);
        return $token;
    }
    private function saveTCache($cacheValue)
    {
        $key = self::generateToken();
        $value =json_encode($cacheValue);
        $expire_in = config('setting.token_time');
        $request = cache($key,$value,$expire_in);
        if (!$request){
            throw new TokenException([
                'msg'=> '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $key;
    }
    private function prepareCacheValue($wxResult,$uid)
    {
        $cacheValue = $wxResult;
        $cacheValue['uid'] = $uid;
        $cacheValue['scope'] = ScopeEnum::User;
        return $cacheValue;


    }
    private function newUser($openid)
    {
        $user = UserModel::create([
            'openid' => $openid
        ]);
        return $user->id;
    }
    private function processLoginError($wxResult)
    {
        throw new WxChatException([
            'msg' => $wxResult['errmsg'],
            'errorCode' => $wxResult['errcode']
        ]);
    }
}