<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\Count;
use app\api\model\Product as ProductModel;
use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\ProductException;

class Product extends BaseController
{
    public function getRecent($count = 15)
    {
        (new Count())->goCheck();
        $result = ProductModel::getMostRecent($count);
        if ($result->isEmpty()){
            throw new ProductException();
        }
        $result = $result->hidden(['summary']);
        return $result;
    }
    public function getAllInCategory($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $products = ProductModel::getAllProductInCategory($id);
        if ($products->isEmpty()){
            throw new ProductException();
        }
        return $products;
    }
    public function getOne($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $product = ProductModel::getProductDetail($id);
        if (!$product){
            throw new ProductException();
        }
        return $product;
    }

}