<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbacksController extends Controller
{
    /**
     * 意见反馈-保存
     *
     * @http    POST
     */
    public function store(Request $Request, Feedback $FeedBacks)
    {
        $Request->validate([
            'content' => 'required',
            'contact' => 'required'
        ]);
        $isCreate = $FeedBacks->create([
            'content' => $Request->content,
            'contact' => $Request->contact,
            'member_id' => $this->user()->id
        ]);
        return $isCreate ? $this->responseSuccess() : $this->responseError();
    }
}
