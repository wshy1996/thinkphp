<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\WxNotify;
use app\api\validate\IDMustBePostiveInt;
use app\api\service\Pay as PayService;
class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];

    /**
     * 预订单
     */
    public function getPreOrder($id = '')
    {
        (new IDMustBePostiveInt())->goCheck();
        $pay = new PayService($id);
        return $pay->pay();
    }

    /**
     * 支付的回调方法
     */
    public function receiveNotify()
    {
        /**
         * 检查库存，超卖
         * 更新订单的status状态
         * 减库存
         * 成功处理返回微信；否则会一直调用
         */
        //post xml 不能在路由中传递参数
        $notify = new WxNotify();
        $notify->Handle();

    }
}