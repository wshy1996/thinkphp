<?php


namespace app\api\validate;


use app\lib\exception\ParameterException;

class OrderPlace extends BaseValidate
{
    /**
     * 数组类型的校验
     * @var array
     */
    protected $rule = [
        'products' => 'checkProducts',
    ];
    protected $singleRule = [
        'product_id' => 'require|checkProduct',
        'count' => 'require|checkProduct',
    ];
    protected function checkProducts($values)
    {
        if (is_array($values)){
            throw new ParameterException(['msg' => '商品参数不正确']);
        }
        if (empty($values)){
            throw new ParameterException(['msg' => '商品列表不能为空']);
        }
        foreach ($values as $value){
            $this->checkProduct($value);
        }
        return true;
    }
    protected function checkProduct($value)
    {
        $validate = new BaseValidate($this->singleRule);
        $result = $validate->check($value);
        if (!$result){
            throw new ParameterException(['msg' => '商品列表参数错误']);
        }
    }

}