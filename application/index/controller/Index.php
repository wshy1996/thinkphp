<?php
namespace app\index\controller;

use think\Request;

class Index
{
    public function index()
    {
        return 10;
    }
    public function hello($id,$name)
    {
        var_dump(Request::instance()->route());
        echo $id;
        echo $name;
    }
}
