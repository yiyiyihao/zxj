<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class HelpCenterController extends BasicController
{
    /************分类***************/
    //添加分类
    public function addHelpCate()
    {
        $this->_checkParams();
        $params = $this->params;
        $name = isset($params['name']) ? trim($params['name']) : '';
        $sort = isset($params['sort_order']) ? intval($params['sort_order']) : 255;
        if(empty($name)){
            $this->returnJson(1,'分类名称不能为空');
        }
        $data = [
          'name'=>$name,
          'is_del'=>0,
          'status'=>1,
          'sort_order'=>$sort,
          'add_time'=>time(),
        ];
        $result = DB::table('help_cate')->insertGetId($data);
        if($result === false){
            $this->returnJson(1,'新增失败，服务器繁忙');
        }
        $this->returnJson(0,'success',$result);
    }

    //分类列表
    public function helpCateList()
    {
        
        return 'C.id,C.name,C.sort_order,C.status';
    }

    //编辑分类
    public function editHelpCate()
    {
        return 11111;
    }

    //删除分类
    public function delHelpCate()
    {
        return 11111;
    }

    /*************帮助问题*********************/

    //新增
    public function addHelp()
    {
        return 11111;
    }

    //列表
    public function helpList()
    {
        return 11111;
    }

    //编辑
    public function editHelp()
    {
        return 11111;
    }

    //删除
    public function delHelp()
    {
        return 11111;
    }

}
