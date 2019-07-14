<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\Token as TokenService;
use app\api\validate\IDMustBePostiveInt;
use app\api\validate\OrderPlace;
use app\api\validate\PagingParameter;
use app\api\model\Order as OrderModel;
use app\lib\exception\OrderException;

class Order extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
        'checkPrimaryScope' => ['only' => 'getDetail,getSummaryByUser']
    ];
    public function placeOrder()
    {
        /**
         * 用户选择商品，提交api
         * 接受信息，检查库存
         * 有库存，存库下单成功返回客户端信息
         * 客户端调用支付，进行支付
         * 再次检测库存量检测
         * 服务器调用调用支付进行支付
         * 接受微信的支付结果
         * 支付成功：扣除库存量
         * 失败：返回一个支付失败的结果
         */
        (new OrderPlace())->goCheck();
        $products = input('post.products/a');///a接受数组参数
        $uid = TokenService::getCurrentUid();
        $order = (new \app\api\service\Order())->place($uid,$products);
        return $order;
    }

    public function getSummaryByUser($page = 1,$size =15)
    {
        (new PagingParameter())->goCheck();
        $uid = TokenService::getCurrentUid();
        $pagingOrders =OrderModel::getSummaryByUser($uid,$page,$size);
        if ($pagingOrders->isEmpty()){
            return [
                'data' => [],
                'current_page' => $pagingOrders->currentPage()
            ];
        }
        $data = $pagingOrders->hidden(['snap_items','snap_address','prepay_id'])->toArray();
        return [
            'data' => $data,
            'current_page' => $pagingOrders->currentPage()
        ];
    }
    public function getSummaryByPage($page = 1,$size =15)
    {
        (new PagingParameter())->goCheck();
        $pagingOrders =OrderModel::getSummaryByPage($page,$size);
        if ($pagingOrders->isEmpty()){
            return [
                'data' => [],
                'current_page' => $pagingOrders->currentPage()
            ];
        }
        $data = $pagingOrders->hidden(['snap_items','snap_address','prepay_id'])->toArray();
        return [
            'data' => $data,
            'current_page' => $pagingOrders->currentPage()
        ];
    }

    public function getDetail($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $orderDetail = OrderModel::get($id);
        if (!$orderDetail){
            throw new OrderException();
        }
        return $orderDetail->hidden(['prepay']);

    }
}