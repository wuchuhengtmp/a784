<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{
    Messages,
    Members
};

class MessagesController extends Controller
{
    /**
     * 推送消息过来 
     *
     */
    public function send()
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
        return $this->responseSuccess();
    }

    /**
     *  消息标记为读
     *
     */
    public function update(Request $Request)
    {
        $Request->validate([
            'type' => 'in:1,2,3,4,5,6'
        ]);
        Messages::where('be_like_member_id', $this->user()->id)
            ->where('type', $Request->type)
            ->update(['is_readed' => 1]);
        return $this->responseSuccess();
    }

    /**
     *  消息列表
     *
     */ 
    public function index(Request $Request)
    {
        $result = [];
        $Request->validate([
            'type' => 'required|in:1,2,3,4,5,6'
        ]);
         
        if (in_array($Request->type, [1,2,3,4])) {
            $Messages = Messages::where('be_like_member_id', $this->user()->id)
                ->where('type', $Request->type)
                ->orderBy('id', 'desc')
                ->paginate(18);
        }
        if (in_array($Request->type, [5,6])) {
            $Messages = Messages::where('be_like_member_id', $this->user()->id)
                ->whereIn('type', [5,6])
                ->orderBy('id', 'desc')
                ->paginate(18);
        }
        if($Messages->isEmpty()) return $this->responseData($result);
        // 非系统消息
        if (in_array($Request->type, [1,2,3,4])) {
            foreach($Messages as $el) {
                $tmp['member_id']  = $el->member->id;
                $tmp['nickname']   = $el->member->nickname;
                $tmp['avatar']     = $el->member->avatar->url;
                $tmp['level']      = Members::getLevelNameByMemberId($el->member_id);
                $tmp['content']    = $el->content;
                $tmp['created_at'] = $el->created_at->toDateTimeString();
                $tmp['post_id']    = $el->post_id;
                $tmp['content_type'] = $el->content_type;
                // 关注消息
                if ($Request->type == 2) {
                    $tmp['is_follow'] = in_array($el->member_id, Members::getFollowIds($el->be_like_member_id));
                }
                $result['data'][] = $tmp;
                // 标记为已读
                $el->update(['is_readed' => 1]);
            }
        }
        // 系统消息
        if (in_array($Request->type, [5,6])) {
            foreach($Messages as $el) {
                $tmp['title']      = $el->systemMessageDetail->title;
                $tmp['url']        = $el->systemMessageDetail->avatar->url ?? '';
                $tmp['id']         = $el->systemMessageDetail->id;
                $tmp['created_at'] = $el->created_at->toDateTimeString();
                $tmp['type']       = $el->type;
                $result['data'][]  = $tmp;
                $el->update(['is_readed' => 1]);
            }
        }
        $result['count'] = $Messages->total();
        // 标记已读
        return $this->responseData($result);
    }
}
