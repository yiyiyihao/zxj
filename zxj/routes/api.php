<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['test'])->match(['get','post'],'test/test1','Api\TestController@test1');

/*******************************************公共pi**********************************************************/


/*******************************************零售商api**********************************************************/
//概况
Route::match(['get','post'],'home/dealerHome','Api\HomeController@dealerHome');

/*******************************************服务商api**********************************************************/
//概况
Route::match(['get','post'],'home/serviceHome','Api\HomeController@serviceHome');
//异步获取图表数据
Route::match(['get','post'],'home/serviceChartData','Api\HomeController@serviceChartData');

/*******************************************厂商api**********************************************************/
//概况
Route::match(['get','post'],'home/factoryHome','Api\HomeController@factoryHome');
//异步获取图表数据
Route::match(['get','post'],'home/factoryChartData','Api\HomeController@factoryChartData');
//旗下服务商列表
Route::match(['get','post'],'home/serviceList','Api\HomeController@serviceList');