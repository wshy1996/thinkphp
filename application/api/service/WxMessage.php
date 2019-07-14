<?php


namespace app\api\service;


class WxMessage
{
    private $sendUrl = "";
    private $touser;
    private $color = 'black';

    protected $tplID;
    protected $page;
    protected $formID;
    protected $data;
    protected $emphasisKeyWord;


    function __construct()
    {
        $accessToken = new AccessToken();
        $token = $accessToken->get();
        $this->sendUrl =sprintf($this->sendUrl,$token);
    }
    protected function sendMessage($openid)
    {

    }
}