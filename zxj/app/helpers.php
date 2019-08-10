<?php
/**
 * Created by huangyihao.
 * User: Administrator
 * Date: 2019/8/8 0008
 * Time: 10:50
 */

function dataFormat($code = 0, $msg = '', $data = [])
{
    if (func_num_args() == 1) {
        $args = func_get_arg(0);
        $code = (string)array_shift($args);
        $msg = (string)array_shift($args);
        $data = array_shift($args);
    }
    $result = [
        'code' => (string)$code,
        'msg'  => (string)$msg,
    ];
    if (empty($data)) {
        return $result;
    }
    if (is_array($data) || is_object($data)) {
        $data = recursion(json_decode(json_encode($data), true));
    } else {
        $data=strval($data);
    }
    //数据中如果有一层data,则不再添加一层data
    if (is_array($data) && count($data) == 1 && key_exists('data', $data)) {
        return array_merge($result, $data);
    } else {
        $result['data'] = $data;
        return $result;
    }
}

function recursion($arr = [])
{
    if (empty($arr)) {
        return [];
    }
    $arr = json_decode(json_encode($arr), true);
    foreach ($arr as $k => $v) {
        if (is_array($v)) {
            $arr[$k] = recursion($v);
        } else {
            $arr[$k] = strval($v);
        }
    }
    return $arr;
}