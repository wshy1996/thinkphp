<?php


namespace app\api\service;


use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\UserAddress;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use think\Db;
use think\Exception;

class Order
{
    //客户端参数
    protected $oProducts;
    //数据库参数
    protected $products;
    protected $uid;

    /**
     * 创建订单
     * @param $uid
     * @param $oProducts
     * @throws \think\exception\DbException
     */
    public function place($uid,$oProducts)
    {
        $this->oProducts = $oProducts;
        $this->uid = $uid;
        $this->products = $this->getProductsByOrder($oProducts);
        $status = $this->getOrderStatus();
        if (!$status['pass']){
            $status['order_id'] = -1;
            return $status;
        }
        //创建订单生成订单快照
        $orderSnap = $this->snapOrder($status);
        $order = $this->createOrder($orderSnap);
        $order['pass'] = true;
        return $order;

    }

    /**
     * 检查库存
     * @param $orderID
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkOrderStock($orderID)
    {
        $oProducts = OrderProduct::where('order_id','=',$orderID)->select();
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $status = $this->getOrderStatus();
        return $status;
    }
    private function createOrder($snap)
    {
        Db::startTrans();
        try{

            $orderNo = self::makerOrderNo();
            $order = new \app\api\model\Order();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapNmae'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatus']);
            $order->save();
            $orderID = $order->id;
            $createTime =$order->create_time;
            foreach ($this->oProducts as &$p){
                $p['id'] = $orderID;
            }
            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->oProducts);
            Db::commit();
            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $createTime
            ];
        }catch (Exception $e){
            Db::rollback();
            throw $e;
        }

    }

    /**
     * 生成订单号
     * @return string
     */
    public static function makerOrderNo()
    {
        $yCode = array('A','B','C','D','E','F','G','H','I','J');
        $orderSn =
            $yCode[intval(date('Y'))-2019].strtoupper(dechex(date('m'))).date('d').substr(time(),-5).substr(microtime(),2,5).sprintf('%02d',rand(0,999));
        return $orderSn;
    }
    private function snapOrder($status)
    {
        $snap = [
            'orderPrice' => 0,//订单价格
            'totalCount' => 0,//
            'pStatus' => [],//
            'snapAddress' => null,
            'snapName' => '',
            'snapImg' => ''
        ];
        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];
        if (count($this->products)>1){
            $snap['snapName'] .= '等';
        }
        return $snap;

    }
    private function getUserAddress()
    {
        $userAddress = UserAddress::where('user_id','=',$this->uid)->find();
        if (!$userAddress){
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => 80001
            ]);
        }
        return $userAddress;

    }

    /**
     * 获取订单状态
     */
    private function getOrderStatus()
    {
        $status = [
            'pass' => true,//订单状态
            'totalCount' =>0,//商品的总数量
            'orderPrice' => 0,//订单总价格
            'pStatusArray' => []//订单的所有商品详细信息
        ];
        foreach ($this->oProducts as $oProduct){
            $pStatus = $this->getProductStatus(
                $oProduct['product_id'],$oProduct['count'],$this->products
            );
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['count'];
            if (!$pStatus['haveStock']){
                $status['pass'] = false;
            }
            array_push($status['pStatusArray'],$pStatus);
        }
        return $status;
    }

    /**
     * 获取商品状态
     * @param $oPID
     * @param $count
     * @param $products数据库信息
     */
    private function getProductStatus($oPID,$oCount,$products)
    {
        /**
         * 商品的详细信息
         */
        $pIndex = -1;
        $pStatus = [
            'id' => null,
            'haveStock' => false,//库存量
            'count' => 0,//请求的数量
            'name' => '',//商品名称
            'totalPrice' =>0 //商品的总价格 数量*单价
        ];
        for ($i = 0; $i<count($products);$i++){
            if ($oPID == $products[$i]['id']){
                $pIndex =  $i;
            }
        }
        if ($pIndex == -1){
            //不存在
            throw new OrderException(['msg' => 'id为'.$oPID.'的商品不存在，创建订单失败']);
        }else{
            $product = $products[$pIndex];
            $pStatus['id'] = $product['id'];
            $pStatus['name'] = $product['name'];
            $pStatus['count'] = $oCount;
            $pStatus['totalPrice'] = $product['price']*$oCount;
            if (($product['stock']-$oCount)>=0){
                $pStatus['haveStock'] = true;
            }
            return $pStatus;
        }
    }

    /**
     * 获取商品信息
     * @param $oProducts
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getProductsByOrder($oProducts)
    {
        //不要循环查询数据库
        $oPIDs = [];
        foreach ($oProducts as $item){
            array_push($oPIDs,$item['product_id']);
        }
        $products = Product::all($oPIDs)
            ->visible(['id','price','stock','name','main_img_url'])
            ->toArray();
        return $products;
    }


}