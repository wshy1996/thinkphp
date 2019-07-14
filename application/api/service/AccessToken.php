<?php


namespace app\api\service;


use think\Exception;

class AccessToken
{
    private $tokenUrl;
    const TOKEN_CACHED_KEY = 'ACCESS';
    const TOKEN_EXPIRE_IN = '7000';
    function __construct()
    {
        $url = config('wx.access_token_url');
        $url =sprintf($url,config('wx.app_id'),config('wx.app_secret'));
        $this->tokenUrl = $url;
    }
    public function get()
    {
        $token = $this->getFromeCache();
    }
    private function getFromeCache()
    {

    }
    private function getFromeWxServer()
    {
        $token =curl_get($this->tokenUrl);
        $token =json_decode($token,true);
        if (!$token){
            throw new Exception('获取AccessToken异常');
        }
        if (!empty($token['errcode'])){
            throw new Exception($token['errmsg']);
        }
        $this->saveCache($token);
        return $token['access_token'];
    }
    private function saveCache($token)
    {

    }

}