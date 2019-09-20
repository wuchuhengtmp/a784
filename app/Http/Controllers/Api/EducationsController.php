<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Educations;

class EducationsController extends Controller
{
    /**
     * 学历列表
     * 
     * @http GET
     */
    public function index()
    {
        $Educations = Educations::get();
        return $this->responseData($Educations->toArray()) ??  $this->responseError('服务器内部错误');
    }
}
