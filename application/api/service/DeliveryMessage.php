<?php


namespace app\api\service;


use app\lib\exception\OrderException;

class DeliveryMessage extends WxMessage
{
    const DELIVERY_MSG_ID = '';

    /**
     * @param $order
     * @param $tplJumPage
     * @throws OrderException
     */
    public function sendDeliveryMessage($order,$tplJumPage)
    {
        if (!$order){
            throw new OrderException();
        }
        $this->tpID =self::DELIVERY_MSG_ID;
        $this->formID = $order->prepay_id;
        $this->page = $tplJumPage;
        $this->preparMessageDate($order);
        $this->emphasisKeyWord = '';
        return ;
    }
    private function preparMessageDate($order)
    {
        $dt = new \DateTime();
        $data = [
            'keyword1' =>[
                'value' => '顺丰速运'
            ],
            'keyword2' => [
                'value' => $order->snap_name,
                'color' => '#274088'
            ],
            'keyword3' => [
                'value' => $order->order_no
            ],
            'keyword4' => [
                'value' => $dt->format("Y-m_d H:i")
            ]
        ];
        $this->data = $data;
    }

}