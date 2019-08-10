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
//新增帮助分类
Route::match(['get','post'],'helpCenter/addHelpCate','Api\HelpCenterController@addHelpCate');
//帮助分类列表
Route::match(['get','post'],'helpCenter/helpCateList','Api\HelpCenterController@helpCateList');
//帮助分类信息
Route::match(['get','post'],'helpCenter/HelpCateInfo','Api\HelpCenterController@HelpCateInfo');
//编辑帮助分类
Route::match(['get','post'],'helpCenter/editHelpCate','Api\HelpCenterController@editHelpCate');
//删除帮助分类
Route::match(['get','post'],'helpCenter/delHelpCate','Api\HelpCenterController@delHelpCate');
//新增帮助
Route::match(['get','post'],'helpCenter/addHelp','Api\HelpCenterController@addHelp');
//帮助列表
Route::match(['get','post'],'helpCenter/helpList','Api\HelpCenterController@helpList');
//编辑帮助
Route::match(['get','post'],'helpCenter/editHelp','Api\HelpCenterController@editHelp');
//删除帮助
Route::match(['get','post'],'helpCenter/delHelp','Api\HelpCenterController@delHelp');

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