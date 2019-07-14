<?php


namespace app\api\service;


use app\api\model\Product as ProductModel;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use think\Loader;
use think\Log;
use app\api\model\Order as OrderModel;

//extend/WxPay/WxPay.Api.php
Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');
class Pay
{
    private $orderID;
    private $orderNO;
    function __construct($orderID)
    {
        if (!$orderID){
            throw new Exception('订单号不允许为NULL');
        }
        $this->orderID = $orderID;
    }
    public function pay()
    {
        /**
         * 检查订单号存在
         * 与用户是否匹配
         * 订单是未支付状态
         * 库存量检测
         */
        $this->checkOrderValid();
        $orderService = new Order();
        $status = $orderService->checkOrderStock($this->orderID);
        if (!$status['pass']){
            return $status;
        }
        return $this->makeWxPreOrder($status['orderPrince']);
    }

    /**
     * 预订单
     */
    private function makeWxPreOrder($totalPrice)
    {
        /**
         * 用户的openid
         */
        $openid = Token::getCurrentTokenvar('openid');
        if (!$openid){
            throw new TokenException();
        }
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderID);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalPrice);//以分做单位
        $wxOrderData->SetBody('测试');
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(config('secure.pay_back_url'));//回调接口
        $this->getPaySingnature($wxOrderData);
        return null;
    }

    /**
     * 发送http请求
     * @param $wxOrderData
     * @throws \WxPayException
     */
    private function getPaySingnature($wxOrderData)
    {
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        if ($wxOrder['return_code'] != 'SUCCESS' ||
        $wxOrderData['result_code'] != 'SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
        }
        //prepay_id
        $this->recordPreOrder($wxOrder);
        $signature = $this->sign($wxOrder);
        return $signature;
    }
    private function sign($wxOrder)
    {
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());
        $rand= md5(time().mt_rand(0,1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        $sing = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySing'] = $sing;
        unset($rawValues['appId']);
        return $rawValues;
    }
    private function recordPreOrder($wxOrder)
    {
        OrderModel::where('id','=',$this->orderID)
            ->update([
                'prepay_id' => $wxOrder['prepay_id']
            ]);
    }
    private function checkOrderValid()
    {
        $order = ProductModel::where('id','=',$this->orderID)->find();
        if (!$order){
            throw new OrderException();
        }
        if (!Token::isValidOperate($this->orderID)){
            throw new TokenException(
                [
                    'msg'=> '用户与订单不匹配',
                    'errorCode'=> 10003
                ]);
        }
        if ($order->status != OrderStatusEnum::UNPAID){
            throw new OrderException([
                'msg'=> '订单已经支付',
                'errorCode'=> 80003,
                'code'=> 400
            ]);
        }
        $this->orderNO = $order->order_no;
        return true;
    }


}