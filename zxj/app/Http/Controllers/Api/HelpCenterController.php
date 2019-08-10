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
        $this->_checkParams();
        $params = $this->params;
        $sortOrder = isset($params['sort']) ? intval($params['sort']) : 0;  //sort=0,1;  0sort_order正序，1sort_order倒序
        $sortOrder = $sortOrder===0 ? 'ASC' : 'DESC';
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $size = isset($params['size']) ? intval($params['size']) : 10;
        $offset = ($page-1) * $size;

        $subTb = DB::table('help_cate')->select('id')->where([['is_del','=',0]])->orderBy('sort_order',$sortOrder)->offset($offset)->limit($size);

        $data = DB::table('help_cate')
            ->joinSub($subTb,'sub',function($join){
                $join->on('help_cate.id','=','sub.id');
            })->select('help_cate.id','help_cate.name','help_cate.sort_order','help_cate.status')
            ->get();

        $this->returnJson(0,'success',$data);
    }

    //分类信息
    public function HelpCateInfo()
    {
        $this->_checkParams();
        $params = $this->params;
        $id = isset($params['id']) ? intval($params['id']) : 0;
        if(empty($id)){
            $this->returnJson(1,'缺失id');
        }
        $data = DB::table('help_cate')
            ->select('id','name','sort_order','status')
            ->where([
            ['is_del','=',0],
            ['id','=',$id],
        ])->first();
        $this->returnJson(0,'success',$data);
    }
    //编辑分类
    public function editHelpCate()
    {
        $this->_checkParams();
        $params = $this->params;
        $id = isset($params['id']) ? intval($params['id']) : 0;
        $name = isset($params['name']) ? trim($params['name']) : 0;
        $sort = isset($params['sort_order']) ? intval($params['sort_order']) : 255;

        if(empty($id)){
            $this->returnJson(1,'缺失id');
        }
        if(empty($name)){
            $this->returnJson(1,'分类名称不能为空');
        }
        $data = DB::table('help_cate')
            ->where('id',$id)
            ->update([
                'name'=>$name,
                'sort_order'=>$sort,
            ]);
        if($data === false){
            $this->returnJson(1,'更新失败');
        }
        $this->returnJson(0,'success',$data);
    }

    //删除分类
    public function delHelpCate()
    {
        $this->_checkParams();
        $params = $this->params;
        $id = isset($params['id']) ? intval($params['id']) : 0;

        if(empty($id)){
            $this->returnJson(1,'缺失id');
        }

        $data = DB::table('help_cate')
            ->where('id',$id)
            ->update([
                'is_del'=>1,
            ]);
        if($data === false){
            $this->returnJson(1,'删除失败');
        }
        $this->returnJson(0,'success',$data);
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
