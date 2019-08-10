<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Input;

class BasicController extends Controller
{
    //
    protected $requestTime;
    protected $visitMicroTime;
    protected $params;

    protected function _checkParams()
    {
        $this->requestTime = time();
        $this->visitMicroTime = $this->_getMillisecond();//会员访问时间(精确到毫秒)
        $data = file_get_contents('php://input');
        if ($data) {
            $tempData = json_decode($data, true);
        }else{
            $tempData = [];
        }
        if (!$data || !empty($tempData) ) {
            $data = Input::get();
        }
        if (!$tempData) {
            $tempData = $data ? $data : (isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '');
        }
        if(!is_array($tempData)) {
            $this->params = json_decode($tempData, true);
        }else{
            $this->params = $tempData;
        }

    }

    protected function _getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }

    protected function returnJson($code,$message = '',$data = array())
    {
        if(!is_numeric($code))
        {
            return '非法的code';
        }

        $result= [
            'code' => $code,
            'msg' => $message,
            'data' => $data
        ];
        header('Content-Type:application/json');
        echo json_encode($result);
        die;

    }



}
