<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

//Route::rule('路由表达式','路由地址','请求类型','路有参数','变量规则（数组）');
/**
 * 路由顺序匹配
 */
//banner
Route::get('api/:version/banner/:id','api/:version.Banner/getBanner');
//theme
Route::get('api/:version/theme/:id','api/:version.Theme/getComplexOne');
Route::get('api/:version/theme','api/:version.Theme/getSimpleList');
//product
Route::get('api/:version/product/recent','api/:version.Product/getRecent');
Route::get('api/:version/product/by_category','api/:version.Product/getAllInCategory');
Route::get('api/:version/product/:id','api/:version.Product/getOne',[],['id'=>'\d+']);
//category
Route::get('api/:version/category/all','api/:version.Category/getAllCategories');

//token
Route::post('api/:version/token/user','api/:version.Token/getToken');
//Route::post('api/:version/token/user','api/:version.Token/getToken');
Route::post('api/:version/token/app','api/:version.Token/getAppToken');

//address
Route::post('api/:version/address','api/:version.Address/createOrUpdateAddress');
//order
Route::post('api/:version/order','api/:version.Order/placeOrder');
Route::post('api/:version/by_user','api/:version.Order/getSummaryByUser');
Route::post('api/:version/order','api/:version.Order/getDetail',[],['id'=>'\d+']);
Route::get('api/:version/order/paginate','api/:version.Order/getSummaryByPage');
//pay
Route::post('api/:version/pay/pre_order','api/:version.Pay/getPreOrder');
Route::post('api/:version/pay/notify','api/:version.Pay/receiveNotify');