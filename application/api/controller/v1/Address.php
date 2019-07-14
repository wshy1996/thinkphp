<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\User as UserModel;
use app\api\service\Token as TokenService;
use app\api\validate\AddressNew;
use app\lib\exception\SuccessException;
use app\lib\exception\UserException;

class Address extends BaseController
{
    protected $beforeActionList = [
        'checkPrimaryScope' => [ 'only' =>'createOrUpdateAddress']
    ];


    public function createOrUpdateAddress()
    {
        $validate = new AddressNew();
        $validate->goCheck();
        /**
         * 通过token获取uid
         * 判断用户
         * 获取数据
         * 判断地址
         */
        $uid = TokenService::getCurrentUid();
        $user = UserModel::get($uid);
        if (!$user){
            throw new UserException();
        }
        $dataArray = $validate->getDataByRule(input('post.'));
        $userAddress = $user->address;
        if (!$userAddress){
            $user->address()->save($dataArray);//创建有括号
        }else{
            $user->address->save($dataArray);//更新无括号
        }
        return json(new SuccessException(),201);
    }

}