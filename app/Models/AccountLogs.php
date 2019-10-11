<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Members;
use App\Models\AccountTransferType;

class AccountLogs extends Model
{
    protected $table = 'account_logs';
    protected $fillable = [
        'type',
        'notice',
        'member_id',
        'money',
        'transfer_type_id',
        'status',
        'maney',
        'out_trade_no',
        'account_name',
        'withdraw_account'
    ];


    /**
     *  关联用户
     *
     */
    public function member()
    {
        return $this->hasOne(Members::class, 'id', 'member_id');
    }

    /**
     *  支付方式
     *
     */
    public function transferType()
    {
        return $this->hasOne(AccountTransferType::class, 'id', 'transfer_type_id');
    }

    /**
     * 计算区间时间最大的一笔支出
     *
     * @uid     init     用户id
     * @start   int      开始时间戳
     * @end     int      结束时间戳
     * @id      init     用户id
     * @return   int
     */
    public static function getMaxBetweenTimeByUid(int $id, int $start, int $end = null)
    {
        $start = date('Y-m-d H:i:s', $start);
        $end = $end ? $end : time();
        $end = date('Y-m-d H:i:s', $end);
        $has_data = self::
        whereBetween('created_at', ["$start", "$end"])
            ->Where('member_id', $id)
            ->WhereIn('type', [1,3])
            ->orderby('money', 'desc')
            ->first();
        if ($has_data)
            return $has_data->money;
        else
            return 0;
    }
}
