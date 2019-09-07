<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountLogs extends Model
{
    protected $table = 'account_logs';

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
            ->Where('uid', $id)
            ->Where('is_third_party_transfer', 1)
            ->orderby('money', 'desc')
            ->first();
        if ($has_data)
            return $has_data->money;
        else
            return 0;
    }
}
