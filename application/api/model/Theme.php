<?php


namespace app\api\model;


class Theme extends BaseModel
{
    public function topicImg(){
        return $this->belongsTo('Image','topic_img_id','id');
    }
    public function headImg(){
        return $this->belongsTo('Image','head_img_id','id');
    }
    public static function getSimpeList($ids)
    {
        return self::with(['headImg','topicImg'])->select($ids);
    }
    public function products()
    {
        return $this->belongsToMany('Product','theme_product','product_id','theme_id');
    }
    public static function getThemeWithProducts($id)
    {
        return self::with(['products','topicImg','headImg'])->find($id);
    }

}