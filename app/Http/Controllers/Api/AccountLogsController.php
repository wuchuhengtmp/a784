<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\AccountLogs;
use App\Models\Members;

class AccountLogsController extends Controller
{
    /**
     * 账户记录
     *
     * @http    GET
     */
    public function index(Request $Request)
    {
        $Request->validate([
            'type' => 'required|in:1,2'
        ]);

        // 充值
        if ($Request->type == 1) {
            $data =  AccountLogs::whereIn('transfer_type_id', [3,2])
                ->where('member_id', $this->user()->id)
                ->whereIn('type', [1,4])
                ->where('status', 1)
                ->orderBy('id', 'desc')
                ->select(['id', 'money', 'created_at', 'out_trade_no', 'notice', 'type'])
                ->paginate(12);
        } else if($Request->type == 2) {
            $data =  AccountLogs::whereIn('transfer_type_id', [3,2])
                ->where('member_id', $this->user()->id)
                ->whereIn('type', [3])
                ->orderBy('id', 'desc')
                ->select(['id', 'money', 'created_at', 'out_trade_no', 'notice', 'type'])
                ->paginate(12);
        }
        $result['data'] = $data->toArray()['data'];
        $result['count'] = $data->total();
        return $this->responseData($result);
    }

    /**
     *  提现申请
     *
     * @http post
     */
    public function withdrawStore(Request $Request)
    {
        $Request->validate([
            'action'   => 'required|in:2,3',
            'account'  => 'required',
            'expense' => [
                'required',
                'numeric',
                'gt:0',
                'regex:/^[0-9]+(.[0-9]{1,2})?$/'],
            'name'  => 'required'
        ]);
        $Member = Members::find($this->user()->id);
        if ($Request->expense > $Member->balance) return $this->responseError('余额不足'); 
        $data['transfer_type_id'] = $Request->action;
        $data['withdraw_account'] = $Request->account;
        $data['money']            = $Request->expense;
        $data['account_name']     = $Request->name;
        $data['status']           =  0;
        $data['member_id']        = $Member->id;
        $data['notice']        =  '提现';
        $data['type']             = 3;
        $has_data = AccountLogs::create($data);
        $Member->balance -= $Request->expense;
        $Member->save();
        if ($has_data){
            return $this->responseSuccess();
        } 
    }
}
