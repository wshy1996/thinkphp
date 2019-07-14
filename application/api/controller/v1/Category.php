<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\lib\exception\CategorException;
use app\api\model\Category as CategoryModel;

/**
 * 分类
 * Class Category
 * @package app\api\controller\v1
 */
class Category extends BaseController
{
    /**
     * 获取所有的分类信息
     */
    public function getAllCategories()
    {
        $result = CategoryModel::with('img')->select();
        if ($result->isEmpty()){
            throw new CategorException();
        }
        return $result;


    }

}