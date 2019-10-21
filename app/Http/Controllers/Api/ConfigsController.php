<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Configs;

class ConfigsController extends Controller
{
    /**
     * 用户协议
     *
     */
    public function agreementShow()
    {
        $data = Configs::where('name', 'agreement')
            ->first(['title', 'value']);
        return $this->responseData([
            'title' => $data->title,
            'content' => $data->value
        ]);
    }  

    /**
     * 免责声明
     *
     */
    public function disclaimersShow()
    {
        $data = Configs::where('name', 'disclaimer')
            ->first(['title', 'value']);
        return $this->responseData([
            'title' => $data->title,
            'content' => $data->value
        ]);
    }  
}
