<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\IDCollection;
use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\ThemeException;
use app\api\model\Theme as ThemeModel;

class Theme extends BaseController
{
    /**
     * @url /theme?id=id1,id2,id3.....
     */
    public function getSimpleList($ids = '')
    {
        (new IDCollection())->goCheck();
        $result = ThemeModel::getSimpeList($ids);
        if ($result->isEmpty()){
            throw new ThemeException();
        }
        return $result;

    }

    /**
     * @url /theme/:id
     * @id
     */
    public function getComplexOne($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $result = ThemeModel::getThemeWithProducts($id);
        if (!$result){
            throw new ThemeException();
        }
        return $result;

    }



}