<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\{
    Message,
    SystemMessageDetails
};

class MessagesController extends Controller
{
    /**
     *  系统消息详情
     */
    public function show(Request $Request)
    {
        $data = SystemMessageDetails::find($Request->id);
        return view('messages/show', 
            [
                'title'      => $data->title,
                'created_at' => $data->created_at,
                'content'    => $data->content
            ]
    );
    }
}
