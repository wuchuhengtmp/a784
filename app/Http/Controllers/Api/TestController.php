<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function index(Request $Request)
    {
        $Store = Storage::disk('qiniu');
        $path = $Request->file->store('file', 'qiniu');
        dd($Store->url($path));
    }
}
