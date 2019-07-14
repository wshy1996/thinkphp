<?php


namespace app\api\validate;


use app\lib\exception\ParameterException;
use think\Exception;
use think\Request;
use think\Validate;

class BaseValidate extends Validate
{
    public function goCheck()
    {
        /**
         * 传入参数
         * 对参数校验
         */
        $request = Request::instance();
        $params = $request->param();
        $result = $this->batch()->check($params);
        if (!$result){
            throw new ParameterException(['msg' => $this->error,]);
        }else{
            return true;
        }
    }
    public function getDataByRule($arrays)
    {
        if (array_key_exists('user_id',$arrays)||array_key_exists('uid',$arrays)){
            new ParameterException([
                'msg' => '参数中含有非法的参数user_id或uid'
            ]);
        }
        $newArray = [];
        foreach ($this->rule as $key=>$value){
            $newArray[$key] = $arrays[$key];
        }
        return $newArray;
    }
    protected function isPositiveInteger($value,$rule = '',$data = '',$field = '')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value+0)>0 ){
            return true;
        }else{
            return false;

        }
    }
    protected function checkIDs($value)
    {
        $values = explode(',',$value);
        if (empty($value)){
            return false;
        }
        foreach ($values as $id){
            if (!$this->isPositiveInteger($id)){
                return false;
            }
        }
        return true;
    }
    protected function isNotEmpty($value,$rule = '',$data = '',$field = '')
    {
        if (empty($value)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 手机号校验
     * @param $value
     * @return bool
     */
    protected function isMobile($value)
    {
        $rule = '^1(3|4|5|6|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule,$value);
        if ($result){
            return true;
        }else{
            return false;
        }
    }
}