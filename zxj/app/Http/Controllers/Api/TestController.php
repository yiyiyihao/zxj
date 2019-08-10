<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BaseRequest;

class TestController extends Controller
{
    //
    public function test1(BaseRequest $request)
    {
        return 'nihao';
    }
}
