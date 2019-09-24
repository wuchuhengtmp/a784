<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class NotifyController extends Controller
{
    public function index(Request $Request)
    {
        dd($Request->toArray());   
    }
}
