<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;

class HomeController extends BasicController
{
    protected $storeId = 1;    //门店id
    protected $ostoreId = 0;    //上级门店id

    //零售商首页页面数据
    public function dealerHome()
    {
        $this->_checkParams();

        //统计
        $statistics = $this->dealerStatistics();

        //商品列表
        $goodsList = $this->goodsList();

        //返回数据
        $this->returnJson(0,'success',compact('statistics','goodsList'));

    }

    //服务商首页页面数据
    public function serviceHome()
    {
        //固定统计
        $statistics = $this->serviceStatistics();

        $storeId = $this->storeId;
        $startTime = date('Y-m-d',strtotime('this week Monday'));//图表统计开始时间
        $endTime = date('Y-m-d');//图表统计结束时间
        #todo
        $user = ['factory_id'=>$this->ostoreId,'admin_type'=>STORE_SERVICE_NEW,'store_id'=>$this->storeId];

        //工单概况
        $workOrderStaticsDuring=$this->workOrderOverView($startTime,$endTime,$storeId);

        //服务统计
        $serviceStaticsDuring=$this->work_order_assess($startTime,$endTime,$user,$this->ostoreId);

        //返回数据
        $this->returnJson(0,'success',compact('statistics','workOrderStaticsDuring','serviceStaticsDuring'));

    }

    //厂商首页页面数据
    public function factoryHome()
    {
        //固定统计
        $statistics = $this->factoryStatistics();

        $storeId = $this->storeId;
        $startTime = date('Y-m-d',strtotime('this week Monday'));//图表统计开始时间
        $endTime = date('Y-m-d');//图表统计结束时间
        #todo
        $user = ['factory_id'=>1,'admin_type'=>ADMIN_FACTORY,'store_id'=>$this->storeId];

        //订单概况
        $orderData=$this->orderOverView($startTime,$endTime,$storeId,$user);
        //工单评价
        $wOrderData=$this->work_order_assess($startTime,$endTime,$user,$this->ostoreId);
        //订单金额统计
        $orderPay=$this->orderAmount($startTime,$endTime,$storeId,$user);


        //返回数据
        $this->returnJson(0,'success',compact('statistics','orderData','wOrderData','orderPay'));
    }

    //零售商首页的统计信息
    private function dealerStatistics()
    {
        $storeId = $this->storeId;
        //获取当前零售商对应门店名称+门店地址
        $store = DB::table('store')->select('name', 'address')->where('store_id', $storeId)->where('is_del', 0)->first();
        if (empty($store)) {
            return response()->json(['errCode' => 1, 'errMsg' => '对应零售商不存在或已删除']);
        }
        $data['store'] = $store;
        //零售商上架数量
        $where = [
            ['GD.store_id', '=', $storeId],
            ['GD.is_del', '=', 0],
            ['GD.status', '=', 1],

            ['G.is_del', '=', 0],
            ['G.status', '=', 1],
        ];
        $onsaleNum = DB::table('goods_dealer as GD')->join('goods as G', 'G.goods_id','=','GD.goods_id')->where($where)->count();
        //零售商累计销量
        $where = [
            ['O.user_store_id', '=', $storeId],
            ['O.order_status', '<>', 2],
            ['O.pay_status', '=', 1],
            ['O.is_user_order', '=', 1],
        ];
        $totalSaleNum = DB::table('order as O')->join('order_sku as OS', 'OS.order_id', '=', 'O.order_id')->where($where)->sum('num');
        //门店客户数量
        $where = [
            ['is_del', '=', 0],
            ['store_id', '=', $storeId],
        ];
        $customerNum = DB::table('store_user')->where($where)->count();
        //申请工单数量
        $where = [
            ['post_store_id', '=', $storeId],
            ['work_order_status', '>', -1],
            ['is_del', '=', 0],
        ];
        $workOrderNum = DB::table('work_order')->where($where)->count();

        $data['statics'] = [
            'onsale_num'     => $onsaleNum,
            'total_sale_num' => $totalSaleNum,
            'customer_num'   => $customerNum,
            'work_order_num' => $workOrderNum,
        ];

        return $data;
    }

    //服务商首页统计数据
    private function serviceStatistics()
    {
        $storeId = $this->storeId;
        $beginToday = strtotime(date('Y-m-d 00:00:00'));

        $where = [
            ['store_id','=',$storeId],
            ['is_del','=',0],
            ['add_time','>=',$beginToday],
        ];

        //今日安装工单数
        $where1 = [
            ['work_order_type','=',1],
        ];
        $todayInstall = DB::table('work_order')->where(array_merge($where,$where1))->count();

        //今日维修工单数
        $where2 = [
            ['work_order_type','=',2],
        ];
        $todayRepair = DB::table('work_order')->where(array_merge($where,$where2))->count();

        //今日上门安装工单数
        $where3 = [
            ['work_order_type','=',1],
            ['sign_time','>=',$beginToday],
        ];
        $todayToInstall = DB::table('work_order')->where(array_merge($where,$where3))->count();

        //今日上门维修工单数
        $where4 = [
            ['work_order_type','=',2],
            ['sign_time','>=',$beginToday],
        ];
        $todayToRepair = DB::table('work_order')->where(array_merge($where,$where4))->count();


        $where5 = [
            ['store_id','=',$storeId],
            ['is_del','=',0],
            ['sign_time','>',0],
        ];

        //累计安装工单统计
        $totalInstall = DB::table('work_order')->where(array_merge($where5,$where1))->count();

        //累计维修工单统计
        $totalRepair = DB::table('work_order')->where(array_merge($where5,$where2))->count();

        $where6 = [
            ['S.is_del','=',0],
            ['S.store_type','=', STORE_SERVICE_NEW],
            ['S.store_id','=',$storeId],
            ['O.add_time','>=',$beginToday],
            ['O.order_status','<>',2],
            ['O.pay_status','=',1],
        ];

        //今日订单数
        $todayOrders = DB::table('order as O')
            ->join('store_dealer as SD','O.user_store_id','=','SD.store_id')
            ->join('store as S','SD.ostore_id','=','S.store_id')
            ->where($where6)
            ->count();

        //今日订单金额
        $todayAmount = DB::table('order as O')
            ->join('store_dealer as SD','O.user_store_id','=','SD.store_id')
            ->join('store as S','SD.ostore_id','=','S.store_id')
            ->where($where6)
            ->sum('real_amount');
        $todayAmount = isset($todayAmount) ? number_format($todayAmount,2) : 0;

        $where7 = [
            ['S.is_del','=',0],
            ['S.store_type','=', STORE_SERVICE_NEW],
            ['S.store_id','=',$storeId],
            ['O.order_status','<>',2],
            ['O.pay_status','=',1],
        ];

        //累计订单数
        $totalOrders = DB::table('order as O')
            ->join('store_dealer as SD','O.user_store_id','=','SD.store_id')
            ->join('store as S','SD.ostore_id','=','S.store_id')
            ->where($where7)
            ->count();

        //累计订单金额
        $totalAmount = DB::table('order as O')
            ->join('store_dealer as SD','O.user_store_id','=','SD.store_id')
            ->join('store as S','SD.ostore_id','=','S.store_id')
            ->where($where7)
            ->sum('real_amount');
        $totalAmount = isset($totalAmount) ? number_format($totalAmount,2) : 0;

        $where8 = [
            ['add_time','>=',$beginToday],
            ['store_type','=',STORE_DEALER],
            ['S.is_del','=',0],
            ['SD.ostore_id','=',$storeId],
        ];

        //今日新增零售商数量
        $todayAddDealer = DB::table('store as S')
            ->join('store_dealer as SD','S.store_id','=','SD.store_id')
            ->where($where8)
            ->count();

        $where9 = [
            ['S.is_del','=',0],
            ['SD.ostore_id','=',$storeId],
            ['S.store_type','=',STORE_DEALER]
        ];

        //累计零售商数量统计
        $totalAddDealer = DB::table('store as S')
            ->join('store_dealer as SD','S.store_id','=','SD.store_id')
            ->where($where9)
            ->count();

        return compact('todayInstall','todayRepair','todayToInstall','todayToRepair','totalInstall',
            'totalRepair','todayOrders','todayAmount','totalOrders','totalAmount','todayAddDealer','totalAddDealer');
    }

    //厂商首页统计数据
    private function factoryStatistics()
    {
        $storeId = $this->storeId;
        $beginToday = strtotime(date('Y-m-d 00:00:00'));

        $where1 = [
            ['add_time','>=',$beginToday],
            ['store_id','=',$storeId],
            ['order_status','<>',2],
            ['pay_status','=',1],
            ['user_store_type','IN',[STORE_SERVICE,STORE_SERVICE_NEW]],
        ];
        //电商
        $whereECommerce1 = [
          ['add_time','>=',$beginToday],
          ['pay_status','=',1],
          ['order_status','=',1],
          ['user_store_type','=',STORE_FACTORY],
          ['factory_id','=',$storeId],
        ];
        //分销
        $whereFenxiao1 = [
            ['add_time','>=',$beginToday],
            ['store_id','=',$storeId],
            ['order_type','=',2],
            ['pay_status','=',1],
            ['order_status','<>',2],
            ['udata_id','>',0],
            ['factory_id','=',$storeId],
        ];

        $todayOrder = DB::table('order')
            ->selectRaw('count(*) as order_count,sum(real_amount) as order_amount')
            ->where($where1)
            ->orWhere($whereFenxiao1)
            ->orWhere($whereECommerce1)
            ->get();

        //今日订单数
        $today['order_count'] = $todayOrder && isset($todayOrder['order_count']) ? intval($todayOrder['order_count']) : 0;
        //今日订单金额
        $today['order_amount'] = $todayOrder && isset($todayOrder['order_amount']) ? number_format($todayOrder['order_amount'],2) : 0;

        $where2 = [
            ['factory_id','=',$storeId],
            ['order_status','<>',2],
            ['pay_status','=',1],
            ['user_store_type','IN',[STORE_SERVICE,STORE_SERVICE_NEW]],
        ];
        //电商
        $whereECommerce2 = [
            ['pay_status','=',1],
            ['order_status','=',1],
            ['user_store_type','=',STORE_FACTORY],
            ['factory_id','=',$storeId],
        ];
        //分销
        $whereFenxiao2 = [
            ['store_id','=',$storeId],
            ['order_type','=',2],
            ['pay_status','=',1],
            ['order_status','<>',2],
            ['udata_id','>',0],
            ['factory_id','=',$storeId],
        ];

        $totalOrder = DB::table('order')
            ->selectRaw('count(*) as order_count,sum(real_amount) as order_amount')
            ->where($where2)
            ->orWhere($whereFenxiao2)
            ->orWhere($whereECommerce2)
            ->get();
        //累计订单数
        $total['order_count'] = $totalOrder && isset($totalOrder['order_count']) ? intval($totalOrder['order_count']) : 0;
        //累计订单金额
        $total['order_amount'] = $totalOrder && isset($totalOrder['order_amount']) ? number_format($totalOrder['order_amount'],2) : 0;

        //今日新增零售商数量
        $where3 = [
            ['factory_id','=',$storeId],
            ['add_time','>=',$beginToday],
            ['store_type','=',STORE_DEALER],
            ['is_del','=',0],
        ];
        $today['dealer_count'] = DB::table('store')->where($where3)->count();

        $where4 = [
            ['factory_id','=',$storeId],
            ['is_del','=',0],
        ];

        $channel_count = DB::table('store')
            ->where($where4)
            ->where([['store_type','=',STORE_CHANNEL]])
            ->count();

        //累计渠道商数量
        $total['channel_count'] = isset($channel_count) ? intval($channel_count) : 0;

        $dealer_count = DB::table('store')
            ->where($where4)
            ->where([['store_type','=',STORE_DEALER]])
            ->count();

        //累计零售商数量
        $total['dealer_count'] = isset($dealer_count) ? intval($dealer_count) : 0;

        $service_count = DB::table('store')
            ->where($where4)
            ->whereIn('store_type',[STORE_SERVICE_NEW,STORE_SERVICE])
            ->count();

        //累计服务商数量
        $total['service_count'] = isset($service_count) ? intval($service_count) : 0;

        $security_money_total= DB::table('store')
            ->where($where4)
            ->whereIn('store_type',[STORE_SERVICE_NEW,STORE_SERVICE,STORE_CHANNEL])
            ->sum('security_money');

        //累计保证金金额(渠道商+服务商)
        $total['security_money_total'] = isset($security_money_total) ? floatval($security_money_total) : 0;


        $totalInstaller = DB::table('user_installer')->where($where4)->count();
        //累计工程师数量
        $total['installer_count'] = intval($totalInstaller);

        $channel_withdraw_amount = DB::table('store as S')
            ->join('store_finance as SF','S.store_id','=','SF.store_id')
            ->where($where4)
            ->where([['S.store_type','=',STORE_CHANNEL]])
            ->sum('withdraw_amount');
        //渠道商累计提现金额
        $total['channel_withdraw_amount'] = isset($channel_withdraw_amount) ? floatval($channel_withdraw_amount) : 0;

        $servicer_withdraw_amount = DB::table('store as S')
            ->join('store_finance as SF','S.store_id','=','SF.store_id')
            ->where($where4)
            ->whereIn('S.store_type',[STORE_SERVICE,STORE_SERVICE_NEW])
            ->sum('withdraw_amount');
        //服务商累计提现金额
        $total['servicer_withdraw_amount'] = isset($servicer_withdraw_amount) ? floatval($servicer_withdraw_amount) : 0;



        $where5 = [
            ['store_id','=',$storeId],
            ['service_status','=',3],
        ];
        $refund_count=DB::table('order_sku_service')
            ->where($where5)
            ->distinct('order_id')
            ->count();

        //累计退款订单数
        $total['refund_count']=$refund_count;

        $refund_amount=DB::table('order_sku_service')
            ->where($where5)
            ->sum('refund_amount');

        //累计退款金额
        $total['refund_amount']=$refund_amount;


        //1.今日提交安装工单数量
        //2.今日上门安装工单数量

        //3.今日提交维修工单数量
        //4.今日提交维修工单数量
        $where5 = [
            ['factory_id','=',$storeId],
            ['is_del','=',0],
            ['add_time','>=',$beginToday],
        ];
        //今日安装工单数
        $where = [
            ['work_order_type','=',1],
        ];
        $todayInstall = DB::table('work_order')->where(array_merge($where5,$where))->count();

        //今日维修工单数
        $where = [
            ['work_order_type','=',2],
        ];
        $todayRepair = DB::table('work_order')->where(array_merge($where5,$where))->count();

        //今日上门安装工单数
        $where = [
            ['work_order_type','=',1],
            ['sign_time','>=',$beginToday],
        ];
        $todayToInstall = DB::table('work_order')->where(array_merge($where5,$where))->count();

        //今日上门维修工单数
        $where = [
            ['work_order_type','=',2],
            ['sign_time','>=',$beginToday],
        ];
        $todayToRepair = DB::table('work_order')->where(array_merge($where5,$where))->count();



        $today['todayInstall'] = $todayInstall ? intval($todayInstall) : 0;
        $today['todayRepair'] = $todayRepair ? intval($todayRepair) : 0;
        $today['todayToInstall'] = $todayToInstall ? intval($todayToInstall) : 0;
        $today['todayToRepair'] = $todayToRepair ? intval($todayToRepair) : 0;

        $where6 = [
            ['factory_id','=',$storeId],
            ['is_del','=',0],
        ];
        $where = [
            ['work_order_type','=',1],
        ];
        //累计提交安装工单统计
        $totalInstall = DB::table('work_order')->where(array_merge($where6,$where))->count();

        $where = [
            ['work_order_type','=',1],
            ['sign_time','>',0],
        ];
        //累计提交安装工单统计
        $totalToInstall = DB::table('work_order')->where(array_merge($where6,$where))->count();

        $where = [
            ['work_order_type','=',2],
        ];
        //累计维修工单统计
        $totalRepair = DB::table('work_order')->where(array_merge($where6,$where))->count();

        $where = [
            ['work_order_type','=',2],
            ['sign_time','>',0],
        ];
        //累计上门维修工单统计
        $totalToRepair = DB::table('work_order')->where(array_merge($where6,$where))->count();

        $total['totalInstall'] = $totalInstall ? intval($totalInstall) : 0;
        $total['totalRepair'] = $totalRepair ? intval($totalRepair) : 0;
        $total['totalToInstall'] = $totalToInstall ? intval($totalToInstall) : 0;
        $total['totalToRepair'] = $totalToRepair ? intval($totalToRepair) : 0;


        return compact('today','total');

    }

    //零售商首页商品列表
    private function goodsList()
    {
        $data = DB::table('goods_dealer as GD')
            ->join('goods as G', 'GD.goods_id','=','G.goods_id')
            ->join('goods_cate as C', 'G.cate_id','=','C.cate_id')
            ->join('goods_service as GS', 'GD.goods_id','=', 'GS.goods_id')
            ->where([
                ['GD.store_id','=',$this->storeId],
                ['GS.store_id','=',$this->ostoreId],
                ['GD.is_del','=',0],
            ])
            ->select(
                'G.goods_id',       //商品id
                'G.name',       //商品名称
                'G.thumb',      //商品图片
                'G.goods_sn',       //商品货号
                'G.sort_order',       //排序
                'C.name as cate_name',    //商品分类
                'GS.min_price_service',   //服务商最小价格
                'GS.max_price_service',   //服务商最大价格
                'GS.stock_service',   //服务商库存
                'GD.min_price_dealer',    //零售商最小价格
                'GD.max_price_dealer',    //零售商最大价格
                'GD.status as status_dealer',    //是否上架
                'GD.sales_dealer',    //销量
                'GD.stock_dealer'     //库存
            )
            ->get();
        //(CASE WHEN G.goods_stock <= G.stock_warning_num THEN 1 ELSE 0 END)
        return $data;
    }

    //服务商异步处理图表数据
    public function serviceChartData()
    {
        $this->_checkParams();
        $from=isset($this->params["start"]) ? trim($this->params["start"]) : '';//开始时间，如2018-02-01
        $to=isset($this->params["end"]) ? trim($this->params["end"]) : '';//结束时间，如 2018-02-10
        $chart_type=isset($this->params["type"]) ? intval($this->params["type"]) : 1;//1工单统计,2工单评价
        $storeId = $this->storeId;

        if($chart_type==1){
            //1工单概况
            $data=$this->workOrderOverView($from,$to,$storeId);
        }else{//2 工单评价
            #todo
            $user = ['factory_id'=>1,'admin_type'=>STORE_SERVICE_NEW,'store_id'=>2];
            $data=$this->work_order_assess($from,$to,$user,$this->ostoreId);
        }

        return $data;
    }

    //厂商异步处理图表数据
    public function factoryChartData()
    {
        $this->_checkParams();
        $startTime=isset($this->params["start"]) ? trim($this->params["start"]) : '';//开始时间，如2018-02-01
        $endTime=isset($this->params["end"]) ? trim($this->params["end"]) : '';//结束时间，如 2018-02-10
        $chart_type=isset($this->params["type"]) ? intval($this->params["type"]) : 1;//1 数据概况/工单统计,2工单评价,3金额统计
        $storeId = $this->storeId;

        #todo
        $user = ['factory_id'=>1,'admin_type'=>ADMIN_FACTORY,'store_id'=>$storeId];
        if ($chart_type==1) {//订单概况
            $data=$this->orderOverView($startTime,$endTime,$storeId,$user);
        }else if ($chart_type==2){//工单评价
            $data=$this->work_order_assess($startTime,$endTime,$user,$this->ostoreId);
        }else if ($chart_type==3){//订单金额统计
            $data=$this->orderAmount($startTime,$endTime,$storeId,$user);
        }

        return $data;
    }

    //厂商获取服务商列表(仅厂商使用)
    public function serviceList()
    {
        $store = DB::table('store')->select('store_id','name')
            ->where([
            ['factory_id'   ,'=', $this->storeId],
            ['status'       ,'=', 1],
            ['is_del'       ,'=', 0],
            ['check_status' ,'=', 1],
        ])
            ->whereIn('store_type',[STORE_SERVICE,STORE_SERVICE_NEW])
            ->get();

        $this->returnJson(0,'success',compact('store'));
    }


    /***************************************图表***********************************************/


    //工单概况
    protected function workOrderOverView($startTime,$endTime,$storeId)
    {
        $data=[];
        if ($startTime==$endTime){//单日数据
            $begin=strtotime($startTime.' 00:00:00');
            $endTime=strtotime($endTime.' 23:59:59');
            $i=0;
            while ($begin<=$endTime) {
                $end=$begin+3600;
                $where=[
                    ['add_time','>=',$begin],
                    ['add_time','<',$end],
                    ['store_id','=',$storeId],
                ];

                //以前数据加缓存7天
                //if ($now != $data[$i]['time']) {
                //    $query->cache($key,86400*7);
                //}
                $data[$i]['value']=DB::table('work_order')->where($where)->count();

                $data[$i]['time']=date('H:00',$begin);
                $i++;
                $begin=$end;
                if ($begin>strtotime(date('Y-m-d H:00'))) {
                    break;
                }
            }
        }else{
            $begin=strtotime($startTime.' 00:00:00');
            $endTime=strtotime($endTime.' 23:59:59');
            $i=0;
            while($begin<=$endTime){

                $end=$begin+86400;
                $where=[
                    ['add_time','>=',$begin],
                    ['add_time','<',$end],
                    ['store_id','=',$storeId],
                ];

                //以前数据加缓存7天
                //if ($today != $data[$i]['time']) {
                //    $query->cache($key,86400*7);
                //}
                $data[$i]['value']=DB::table('work_order')->where($where)->count();
                $data[$i]['time']=date('Y-m-d',$begin);
                $i++;
                $begin=$end;
            }
        }

        return $data;

    }

    //服务统计
    public function work_order_assess($from,$to,$user,$ostoreId)
    {
        $result=[
            'assess'=>[],
            'assess_score'=>[],
        ];
        if (!in_array($user['admin_type'],[ADMIN_SERVICE,ADMIN_SERVICE_NEW,ADMIN_FACTORY])) {
//            return $result;
        }
        $from=strtotime($from);
        $to=strtotime($to);
        $where=[
            ['p1.is_del','=',0],
            ['p1.work_order_status','=',4],
            ['p1.add_time','>=',$from],
            ['p1.add_time','<=',$to],
        ];
        if (in_array($user['admin_type'],[ADMIN_SERVICE,ADMIN_SERVICE_NEW])) {
            $where[]=['p1.store_id','=',$user['store_id']];
        } elseif ($user['admin_type']==ADMIN_FACTORY) {
            $where[]=['p1.factory_id','=',$user['store_id']];
        }

        //工单总数
        $totalWorder = DB::table('work_order as p1')
            ->where($where)
            ->count();

        //已评价工单数
        $assessWorder = DB::table('work_order as p1')
            ->leftJoin('work_order_assess as p2','p1.worder_id','=','p2.worder_id')
            ->where($where)
            ->where([['p2.is_del','=',0],['p2.type','=',1]])
            ->count('p2.assess_id');

        $perent = $totalWorder > 0 ? round($assessWorder / $totalWorder,2) * 100 : 0;
        $chartData=[
            ['name'=>'已评价工单','value'=>$assessWorder],
            ['name'=>'未评价工单','value'=>$totalWorder-$assessWorder],
        ];
        if (false) {
            $result['assess']=$chartData;
        }else{
//            $color=['#009688','#2db2ea'];
//            $label='工单评价';
//            $chart=new\app\common\service\Chart('pie',[''],$label,$chartData,$color,false);
//            $result['assess']=$chart->getOption();
            $result['assess']['data']=$chartData;
            //补充参数
            $result['assess']['title']=[
                'text'=>'已评价工单'.$perent.'%('.$assessWorder.'/'.$totalWorder.')',
                'subtext'=>'',
                'left'=>'left',
            ];
            //说明
            $result['assess']['legend']=[
                'data'=>['已评价工单','未评价工单'],
            ];
            //中心圈
            //$result['assess']['series']['label']['emphasis'] = [
            //    'show'      => true,
            //    'textStyle' => [
            //        'fontSize'   => 30,
            //        'fontWeight' => 'bold',
            //    ],
            //];
        }
        $result['assess_score']=[];

        //获取服务统计信息
        $ret=$this->getServiceStaticsDuring($ostoreId,$user['factory_id'],$from,$to);
        if ($ret['code'] !== '0') {
            $ret['data']['score_overall']=0;
            $title=$this->getAccessTitle($user['factory_id']);
            if ($title['code'] === '0') {
                $ret['data']['score_detail']=array_map(function ($item) {
                    return [
                        'name'  => $item,
                        'value' => 0,
                    ];
                },$title['data']);
                $ret['data']['score_detail'][]=['name'=>'解决率','value'=>0];
            }
        }

        //评分统计
        $chartData=[];

        //小标题
        $chartData['radar']['indicator']=[];
        $arr=[];
        foreach ($ret['data']['score_detail'] as $value) {
            $val=round($value['value'],2);
            $arr[]=$val;
            $chartData['radar']['indicator'][]=[
                'name'=>$value['name'],
                'max'=>5
            ];

        }
        $chartData['radar']['indicator'][] = $ret['data']['score_detail'];
        $result['assess_score']=$chartData;
        //补充参数-大标题
        $result['assess_score']['title']=[
            'text'=>"综合评分".$ret['data']['score_overall'],
            'subtext'=>'',
            'left'=>'left',
        ];


        return $result;

    }

    //订单概况
    protected function orderOverView($startTime,$endTime,$storeId,$user = [])
    {
        $data=[];
        if ($startTime==$endTime){//单日数据
            $begin=strtotime($startTime.' 00:00:00');
            $endTime=strtotime($endTime.' 23:59:59');
            $i=0;
            while ($begin<=$endTime) {
                $data[$i]['time']=date('H:00',$begin);
                $end=$begin+3600;
                $where=[
                    ['add_time','>=',$begin],
                    ['add_time','<',$end],
                    ['order_status','<>',2],
                    ['pay_status','=',1],
                ];
                if ($user['admin_type']==ADMIN_CHANNEL) {
                    //渠道商零售商数据据统计
                    $where=[
                        ['O.add_time','>=',$begin],
                        ['O.add_time','<',$end],
                        ['S.is_del','=',0],
                        ['S.store_type','=',2],
                        ['S.store_id','=',$storeId],
                        ['order_status','<>',2],
                        ['O.pay_status','=',1],
                    ];
                    $query = DB::table('order as O')
                        ->join('store_dealer as SD','O.user_store_id','=','SD.store_id')
                        ->join('store as S','SD.ostore_id','=','S.store_id')
                        ->where($where);
                }else if ($user['admin_type']==ADMIN_FACTORY){//厂商
                    $where[]=['store_id','=',$storeId];
                    $query=DB::table('order')->where($where);
                }else{
                    $where[]=['user_store_id','=',$storeId];
                    $query=DB::table('order')->where($where);
                }

                //以前数据加缓存7天
                //if ($now != $data[$i]['time']) {
                //    $query->cache($key,86400*7);
                //}
                $data[$i]['value']=$query->count();
                $i++;
                $begin=$end;
                if ($end>strtotime(date('Y-m-d H:00'))) {
                    break;
                }

            }
        }else{
            $begin=strtotime($startTime.' 00:00:00');
            $endTime=strtotime($endTime.' 23:59:59');
            $i=0;
            while($begin<=$endTime){
                $data[$i]['time']=date('Y-m-d',$begin);
                $end=$begin+86400;
                $where=[
                    ['add_time','>=',$begin],
                    ['add_time','<',$end],
                    ['order_status','<>',2],
                    ['pay_status','=',1],
                ];
                if ($user['admin_type']==ADMIN_CHANNEL) {
                    //渠道商零售商数据据统计
                    $where=[
                        ['O.add_time','>=',$begin],
                        ['O.add_time','<',$end],
                        ['S.is_del','=',0],
                        ['S.store_type','=',2],
                        ['S.store_id','=',$storeId],
                        ['order_status','<>',2],
                        ['O.pay_status','=',1],
                    ];

                    $query = DB::table('order as O')
                        ->join('store_dealer as SD','O.user_store_id','=','SD.store_id')
                        ->join('store as S','SD.ostore_id','=','S.store_id')
                        ->where($where);
                }else if ($user['admin_type']==ADMIN_FACTORY){//厂商
                    $where[]=['store_id','=',$storeId];
                    $query=DB::table('order')->where($where);
                }else{
                    $where[]=['user_store_id','=',$storeId];
                    $query=DB::table('order')->where($where);
                }
                $key='order_overview_'.$begin.'_'.$end.'_'.$storeId;
                //以前数据加缓存7天
                //if ($today != $data[$i]['time']) {
                //    $query->cache($key,86400*7);
                //}
                $data[$i]['value']=$query->count();
                $i++;
                $begin=$end;
            }
        }

        return $data;

    }

    //订单金额统计
    protected function orderAmount($startTime,$endTime,$storeId,$adminUser = [])
    {
        $data=[];
        if ($startTime==$endTime){//单日数据
            $begin=strtotime($startTime.' 00:00:00');
            $endTime=strtotime($endTime.' 23:59:59');
            $i=0;

            while ($begin<=$endTime) {
                $data[$i]['time']=date('H:00',$begin);
                $end=$begin+3600;
                $where=[
                    ['add_time','>=',$begin],
                    ['add_time','<',$end],
                    ['order_status','<>',2],
                    ['pay_status','=',1],
                ];

                if ($adminUser['admin_type']==ADMIN_CHANNEL) {
                    //渠道商零售商数据据统计
                    $where=[
                        ['O.add_time','>=',$begin],
                        ['O.add_time','<',$end],
                        ['S.is_del','=',0],
                        ['S.store_type','=',2],
                        ['S.store_id','=',$storeId],
                        ['order_status','<>',2],
                        ['O.pay_status','=',1],
                    ];

                    $query = DB::table('order as O')
                        ->join('store_dealer SD','O.user_store_id','=','SD.store_id')
                        ->join('store S','SD.ostore_id','=','S.store_id')
                        ->where($where);
                }else if ($adminUser['admin_type']==ADMIN_FACTORY){//厂商
                    $where[]=['store_id','=',$storeId];
                    $query=DB::table('order')->where($where);
                }else{
                    $where[]=['user_store_id','=',$storeId];
                    $query=DB::table('order')->where($where);
                }

                //以前数据加缓存7天
                //if ($now != $data[$i]['time']) {
                //    $query->cache($key,86400*7);
                //}
                $data[$i]['value']=$query->sum('real_amount');

                $data[$i]=$data[$i]['time'];//鼠标移动提示
                $data[$i]=$data[$i]['value'];//显示数据子元素值
                $i++;
                $begin=$end;
                if ($end>strtotime(date('Y-m-d H:00'))) {
                    break;
                }

            }
        }else{
            $begin=strtotime($startTime.' 00:00:00');
            $endTime=strtotime($endTime.' 23:59:59');
            $i=0;

            while($begin<=$endTime){
                $data[$i]['time']=date('Y-m-d',$begin);
                $end=$begin+86400;
                $where=[
                    ['add_time','>=',$begin],
                    ['add_time','<',$end],
                    ['order_status','<>',2],
                    ['pay_status','=',1],
                ];

                if ($adminUser['admin_type']==ADMIN_CHANNEL) {
                    //渠道商零售商数据据统计
                    $where=[
                        ['O.add_time','>=',$begin],
                        ['O.add_time','<',$end],
                        ['S.is_del','=',0],
                        ['S.store_type','=',2],
                        ['S.store_id','=',$storeId],
                        ['O.order_status','=',1],
                        ['O.pay_status','=',1],
                    ];
                    $join=[
                        ['store_dealer SD','O.user_store_id=SD.store_id'],
                        ['store S','SD.ostore_id=S.store_id'],
                    ];
                    $query = DB::table('order')->alias('O')->join($join)->where($where);
                }else if ($adminUser['admin_type']==ADMIN_FACTORY){//厂商
                    $where[]=['store_id','=',$storeId];
                    $query=DB::table('order')->where($where);
                }else{
                    $where[]=['user_store_id','=',$storeId];
                    $query=DB::table('order')->where($where);
                }

                //以前数据加缓存7天
                //if ($today != $data[$i]['time']) {
                //    $query->cache($key,86400*7);
                //}
                $data[$i]['value']=$query->sum('real_amount');
                $i++;
                $begin=$end;
            }
        }

        return $data;
    }

    //工单评分
    protected function getServiceStaticsDuring($storeId,$factoryId,$startTime,$endTime)
    {
        //取出第一个商品安装评价配置
        $installAssessKey = DB::table('config_form')->where([
            ['is_del', '=', 0],
            ['store_id', '=', $factoryId],
            ['key', 'like', 'installer_assess_%'],
        ])->value('key');
        if (empty($installAssessKey)) {
            return dataFormat(1, '厂商未配置安装工单评分信息');
        }
        //取出第一个商品评价维修配置
        $repairAssessKey = DB::table('config_form')->where([
            ['is_del', '=', 0],
            ['store_id', '=', $factoryId],
            ['key', 'like', 'repair_assess_%'],
        ])->value('key');
        if (empty($repairAssessKey)) {
            return dataFormat(1, '厂商未配置维工单评分信息');
        }
        $where=[
            ['p1.add_time','>=',$startTime],
            ['p1.add_time','<',$endTime],
            ['p1.is_del','=',0],
            ['p2.is_del','=',0],
            ['p3.is_del','=',0],
            ['p3.key','=',$repairAssessKey],
            ['p1.factory_id','=',$factoryId],
            //['p1.store_id','=',$storeId],
        ];
        if ($storeId > 0) {
            $where[]=['p1.store_id','=',$storeId];
        }
        $repairSql = DB::table('work_order as p1')
            ->select('p3.name','p2.config_value')
            ->leftJoin('config_form_logs as p2', 'p2.worder_id','=','p1.worder_id')
            ->leftJoin('config_form as p3', 'p3.id','=','p2.config_form_id')
            ->where($where);

        $where=[
            ['p4.add_time','>=',$startTime],
            ['p4.add_time','<',$endTime],
            ['p4.is_del','=',0],
            ['p5.is_del','=',0],
            ['p6.is_del','=',0],
            ['p6.key','=',$installAssessKey],
            ['p4.factory_id','=',$factoryId],
            //['p4.store_id','=',$storeId],
        ];
        if ($storeId > 0) {
            $where[]=['p4.store_id','=',$storeId];
        }

        $installSql = DB::table('work_order as p4')
            ->select('p6.name','p5.config_value')
            ->join('config_form_logs as p5', 'p5.worder_id','=','p4.worder_id')
            ->join('config_form as p6', 'p6.id','=','p5.config_form_id')
            ->where($where)
            ->unionAll($repairSql);


        $data = DB::table(DB::raw("({$installSql->toSql()}) as sub"))
            ->select('name',DB::raw('avg(config_value) as value'))
            ->groupBy('name')
            ->mergeBindings($installSql)
            ->get();


        if (count($data) == 0) {
            return dataFormat(1,'暂无数据');
        }
        //该服务商所有工单
        $where3=[
            ['add_time','>=',$startTime],
            ['add_time','<',$endTime],
            ['factory_id','=',$factoryId],
            ['is_del','=',0],
            //['store_id','=',$storeId],
        ];
        if ($storeId > 0) {
            $where3[]=['store_id','=',$storeId];
        }
        $countAll = DB::table('work_order')->where($where3)->count();
        $where4=[
            ['add_time','>=',$startTime],
            ['add_time','<',$endTime],
            ['factory_id','=',$factoryId],
            ['work_order_status','=',4],
            ['is_del','=',0],
        ];
        if ($storeId > 0) {
            $where4[]=['store_id','=',$storeId];
        }
        $countFinish = DB::table('work_order')->where($where4)->count();
        $rate=$countAll>0? round($countFinish/$countAll,2)*5:0;
        $data[]=[
            'name'=>'解决率',
            'value'=>$rate,
        ];
        $data = json_decode(json_encode($data),1);
        //综合分数
        $sum=array_sum(array_column($data,'value'));
        $count=count($data);
        $scoreOverall=0;
        if ($count > 0) {
            $scoreOverall=number_format($sum/$count,2,'.','');
        }
        return dataFormat(0, 'ok', [
            'score_overall' => $scoreOverall,
            'score_detail'  => $data,
        ]);
    }

    protected function getAccessTitle($factoryId)
    {
        //取出第一个商品安装评价配置
        $installAssessKey = DB::table('config_form')->where([
            ['is_del', '=', 0],
            ['store_id', '=', $factoryId],
            ['key', 'like', 'installer_assess_%'],
        ])->value('key');
        if (empty($installAssessKey)) {
            return dataFormat(1, '厂商未配置安装工单评分信息');
        }
        //取出第一个商品评价维修配置
        $repairAssessKey = DB::table('config_form')->where([
            ['is_del', '=', 0],
            ['store_id', '=', $factoryId],
            ['key', 'like', 'repair_assess_%'],
        ])->value('key');
        if (empty($repairAssessKey)) {
            return dataFormat(1, '厂商未配置维工单评分信息');
        }
        $arr = DB::table('config_form')->where([
            ['is_del', '=', 0],
            ['store_id', '=', $factoryId],
        ])->whereIn('key',[$installAssessKey, $repairAssessKey])->groupBy('name')->select('name')->get()->toArray();
        return dataFormat(0, 'ok', $arr);
    }


}
