<?php


namespace app\api\model;


class Product extends BaseModel
{
    protected $hidden = [
        'delete_time', 'main_img_id', 'pivot', 'from', 'category_id',
        'create_time', 'update_time'];
    public function getMainImgUrlAttr($value,$data)
    {
        return $this->prefixImgUrl($value,$data);
    }
    public static function getMostRecent($count)
    {
        return self::limit($count)->order('create_time desc')->select();
    }
    public static function getAllProductInCategory($id)
    {
        return self::where('category_id','=',$id)->select();
    }
    public function imgs()
    {
        return  $this->hasMany('ProductImage','product_id','id');
    }
    public function properties()
    {
        return $this->hasMany('ProductProperty','product_id','id');
    }
    public static function getProductDetail($id)
    {
        return self::where('id','=',$id)
            ->with(['imgs'=>function($query){
                $query->with(['imgUrl'])->order('order','asc');
            }])
            ->with(['properties'])->find();
    }
}
