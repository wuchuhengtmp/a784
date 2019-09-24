<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{

    /**
     * 
     *
     */
    public function index()
    {
        /* $resutl = Redis::get(35); */
        $result = Redis::command('GET key', [35]);
        dd($result);
    }
}
