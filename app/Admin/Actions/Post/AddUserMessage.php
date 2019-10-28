<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Messages;

class AddUserMessage extends RowAction
{
    public $name = '添加系统消息';

    public function handle(Model $model, Request $Request)
    {
        $member_id = $Request->_key;
        $title     = $Request->title;
        $content   = $Request->content;
        $has_id = DB::table('system_message_details')->insertGetId([
            'title' => $title,
            'content' => $content
        ]);
        $data['be_like_member_id']        = $member_id;
        $data['system_message_detail_id'] = $has_id;
        $data['type']                     = 6;
        Messages::create($data);
        return $this->response()->success('Success message.')->refresh();
    }

    public function form()
    {
        $this->text('title', '标题')->rules('required');
        $this->textarea('content', '消息内容')->rules('required');
    }

    public function html()
    {
        return "<a class='report-posts btn btn-sm btn-danger'><i class='fa fa-info-circle'></i>举报</a>";
    }
}
