<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Levels;

class LevelsController extends Controller
{
    /**
     * 会员等级公告 
     *
     *
     */
    public function index()
    {
        $Levels = Levels::orderBy('order_num', 'desc')->get(['name', 'annotation']);
        return $Levels ? $this->responseData($Levels->toArray()) : [];
    }
}
