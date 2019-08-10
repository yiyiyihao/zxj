<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function () {
    $where9 = [
        ['S.is_del','=',0],
        ['SD.ostore_id','=',2],
        ['S.store_type','=',STORE_DEALER]
    ];
    $totalAddDealer = DB::table('store as S')
        ->join('store_dealer as SD','S.store_id','=','SD.store_id')
        ->where($where9)
        ->count();

    return $totalAddDealer;
});

Route::prefix('admin')->group(function(){
   Route::get('test/test1','Admin\TestController@test1');

});

Route::prefix('home')->group(function(){
   Route::get('test/test1','Home\TestController@test1');

});

