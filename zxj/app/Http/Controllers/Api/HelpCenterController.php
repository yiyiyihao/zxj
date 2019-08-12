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
            $this->returnJson(1,'新增失败');
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

        $totalRows = DB::table('help_cate')->where('is_del',0)->count();
        if($totalRows == 0){
            $this->returnJson(0,'success',['total'=>0,'list'=>[]]);
        }
        $subTb = DB::table('help_cate')->select('id')->where([['is_del','=',0]])->orderBy('sort_order',$sortOrder)->offset($offset)->limit($size);

        $data = DB::table('help_cate')
            ->joinSub($subTb,'sub',function($join){
                $join->on('help_cate.id','=','sub.id');
            })->select('help_cate.id','help_cate.name','help_cate.sort_order','help_cate.status')
            ->get();

        $this->returnJson(0,'success',['total'=>$totalRows,'list'=>$data]);
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

        $helpExist = DB::table('help')
            ->where([
                ['id','=',$id],
                ['is_del','=',0],
            ])->first();

        if($helpExist != null){
            $this->returnJson(1,'分类旗下有帮助问题，先删除帮助问题');
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
        $this->_checkParams();
        $params = $this->params;
        $cateId = isset($params['cate_id']) ? intval($params['cate_id']) : 0;
        $title = isset($params['title']) ? trim($params['title']) : '';
        $answer = isset($params['answer']) ? trim($params['answer']) : '';
        $visible_store_type = isset($params['visible_store_type']) ? trim($params['visible_store_type']) : '';
        $sort = isset($params['sort_order']) ? intval($params['sort_order']) : 255;

        if(empty($cateId)){
            $this->returnJson(1,'所属分类不能为空');
        }
        if(empty($title)){
            $this->returnJson(1,'帮助问题不能为空');
        }
        $cateExist = DB::table('help_cate')
            ->where([
                ['id','=',$cateId],
                ['is_del','=',0],
            ])->first();

        if($cateExist === null){
            $this->returnJson(1,'帮助分类不存在');
        }

        $data = [
            'cate_id'=>$cateId,
            'title'=>$title,
            'answer'=>$answer,
            'visible_store_type'=>$visible_store_type .','.ADMIN_SYSTEM,
            'is_del'=>0,
            'status'=>1,
            'sort_order'=>$sort,
            'add_time'=>time(),
        ];
        $result = DB::table('help')->insertGetId($data);
        if($result === false){
            $this->returnJson(1,'新增失败');
        }
        $this->returnJson(0,'success',$result);
    }

    //列表
    public function helpList()
    {
        $this->_checkParams();
        $params = $this->params;
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $size = isset($params['size']) ? intval($params['size']) : 10;
        $offset = ($page-1) * $size;
        $sort = isset($params['sort_order']) ? intval($params['sort_order']) : 0; //默认0正序，1倒序
        $sort = $sort === 0 ? 'ASC' : 'DESC';
        $cateId = isset($params['cate_id']) ? intval($params['cate_id']) : 0;
        $where = [];
        if($cateId != 0){
            $where = [
                ['cate_id','=',$cateId],
            ];
        }
        //根据登入用户商户信息，获取商户可看的帮助问题
        #todo
        $userType = ADMIN_SYSTEM;



        $totalRows = DB::table('help')->where('is_del',0)->where($where)->whereRaw('FIND_IN_SET(?,visible_store_type)', [$userType])->count();
        if($totalRows == 0){
            $this->returnJson(0,'success',['total'=>0,'list'=>[]]);
        }

        $sub = DB::table('help')->select('id')->where('is_del',0)->where($where)->whereRaw('FIND_IN_SET(?,visible_store_type)', [$userType])->orderBy('sort_order',$sort)->offset($offset)->limit($size);
        $data = DB::table('help')
            ->joinSub($sub,'sub',function($join){
                $join->on('help.id','=','sub.id');
            })
            ->leftJoin('help_cate as hc','help.cate_id','=','hc.id')
            ->select('help.id','help.cate_id','hc.name as cate_name','help.title','help.visible_store_type','help.sort_order')
            ->where([
                ['hc.is_del','=',0],
            ])->get();
        foreach($data as $k => $v){
            $data[$k]->visible_store_type = str_replace(['1','2','3','4','5','6'],['厂商','渠道商','零售商','服务商','回响应用商户','新服务商'],$v->visible_store_type);
        }
        $this->returnJson(0,'success',['total'=>$totalRows,'list'=>$data]);
    }

    //详情
    public function helpInfo()
    {
        $this->_checkParams();
        $params = $this->params;
        $id = isset($params['id']) ? intval($params['id']) : 0;

        if($id === 0){
            $this->returnJson(1,'id缺失');
        }
        $data = DB::table('help')
            ->leftJoin('help_cate as hc','help.cate_id','=','hc.id')
            ->select('help.id','help.title','help.cate_id','hc.name as cate_name','help.visible_store_type','help.sort_order','help.answer')
            ->where([
                ['help.id','=',$id],
                ['help.is_del','=',0],
            ])
            ->first();
        if($data === null){
            $this->returnJson(0,'success',[]);
        }

        $data->visible_store_type = str_replace(['1','2','3','4','5','6'],['厂商','渠道商','零售商','服务商','回响应用商户','新服务商'],$data->visible_store_type);
        $this->returnJson(0,'success',$data);

    }

    //编辑
    public function editHelp()
    {
        $this->_checkParams();
        $params = $this->params;
        $id = isset($params['id']) ? intval($params['id']) : 0;
        $cateId = isset($params['cate_id']) ? intval($params['cate_id']) : 0;
        $title = isset($params['title']) ? trim($params['title']) : '';
        $answer = isset($params['answer']) ? trim($params['answer']) : '';
        $visible_store_type = isset($params['visible_store_type']) ? trim($params['visible_store_type']) : '';
        $sort = isset($params['sort_order']) ? intval($params['sort_order']) : 255;

        if($id === 0){
            $this->returnJson(1,'id缺失');
        }
        if(empty($cateId)){
            $this->returnJson(1,'所属分类不能为空');
        }
        if(empty($title)){
            $this->returnJson(1,'帮助问题不能为空');
        }
        $cateExist = DB::table('help_cate')
            ->where([
                ['id','=',$cateId],
                ['is_del','=',0],
            ])->first();

        if($cateExist === null){
            $this->returnJson(1,'帮助分类不存在');
        }

        $data = [
            'cate_id'=>$cateId,
            'title'=>$title,
            'answer'=>$answer,
            'visible_store_type'=>$visible_store_type,
            'sort_order'=>$sort,
            'update_time'=>time(),
        ];

        $result = DB::table('help')
            ->where('id',$id)
            ->update($data);

        if($result === false){
            $this->returnJson(1,'更新失败');
        }

        $this->returnJson(0,'success',$result);


    }

    //删除
    public function delHelp()
    {
        $this->_checkParams();
        $params = $this->params;
        $id = isset($params['id']) ? intval($params['id']) : 0;
        if($id === 0){
            $this->returnJson(1,'id缺失');
        }

        $result = DB::table('help')->where('id',$id)->update(['is_del'=>1]);

        if($result === false){
            $this->returnJson(1,'删除失败');
        }

        $this->returnJson(0,'seccess',$result);
    }

    //商户类型列表
    public function storeTypeList()
    {
        //商户类型(1厂商 2渠道商 3零售商/零售商 4服务商 5回响应用商户,6新服务商)
        //没有对应数据表
        $data = [
            ['id'=>STORE_FACTORY,'name'=>'厂商'],
            ['id'=>STORE_CHANNEL,'name'=>'渠道商'],
            ['id'=>STORE_DEALER,'name'=>'零售商'],
            ['id'=>STORE_SERVICE,'name'=>'服务商'],
            ['id'=>STORE_ECHODATA,'name'=>'回响应用商户'],
            ['id'=>STORE_SERVICE_NEW,'name'=>'新服务商'],
        ];

        $this->returnJson(0,'success',$data);
    }

}
