<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AccountTransferType;

class TopSearchOrders extends Model
{
    protected $fillable = [
        'post_id',
        'nickname',
        'title',
        'member_id',
        'price',
        'expense',
        'top_time_limit',
        'top_end_time',
        'transfer_type'  ,
        'account_log_id',
        'is_pay'
    ];

     
    /**
     *  支付方式
     *
     */
    public function transferType()
    {
        return $this->hasOne(AccountTransferType::class, 'id', 'transfer_type_id');
    }
}
