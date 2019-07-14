<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\Banner as BannerModel;
use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\BannerException;
use think\Validate;

class Banner extends BaseController
{


    /**
     * 根据ID获取banner信息
     * @url banner/:id
     * @id ID参数
     */
    public function getBanner($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $banner = BannerModel::getBannerByID($id);
        if (!$banner){
            throw new BannerException();
        }else{
            return $banner ;
        }


    }
}