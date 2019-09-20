<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{
    Messages
};

class MessagesController extends Controller
{
    /**
     * 推送消息过来 
     *
     */
    public function index()
    {
        $data_format['likes']           = Messages::getData($this->user()->id, 1);
        $data_format['follows']         = Messages::getData($this->user()->id, 2);
        $data_format['comments']        = Messages::getData($this->user()->id, 3);
        $data_format['replies']         = Messages::getData($this->user()->id, 4);
        $data_format['system_messages'] = Messages::getSystemMessage($this->user()->id);
        Messages::sendMessage([
            'member_id'  => $this->user()->id,
            'data'       => json_encode($data_format)
        ]);
    }

    /**
     *  消息标记为读
     *
     */
    public function update(Request $Request)
    {
        $Request->validate([
            'type' => 'in:1,2,3,4,5'
        ]);
        Messages::where('be_like_member_id', $this->user()->id)
            ->where('type', $Request->type)
            ->update(['is_readed' => 1]);
        return $this->responseSuccess();
    }
}
