<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Levels extends Model
{
    public $timestamps = false;

    /**
     * 通过粉丝量和充值金额获取等级
     *
     * @fans    init 粉丝量
     * @money   init 金额
     * @return mix
     */
    public static function  getLevelByFansAndMony(int $fans = null ,int $money = null )
    {
        $fans = $fans ?? 0;
        $money = $money ?? 0;
        if ($fans && $money) {
            $byFansDasta = self::where('eg_follows', '<=', $fans)
                ->orderby('eg_follows', 'desc')
                ->first();
            $byMoneyData = self::where('eg_money', '<=', $money)
                ->orderby('eg_money', 'desc')
                ->first();
            if ($byMoneyData  && $byFansDasta) {
                //比较粉丝和充值都有等级
                if ($byFansDasta->id  === $byMoneyData) return $byFansDasta;
                if ($byMoneyData->id > $byFansDasta->id)
                    return $byMoneyData;
                else
                    return $byFansDasta;
            } elseif($byFansDasta || $byMoneyData) {
                // 返回粉丝或者充值的等级
                if ($byMoneyData) return $byMoneyData;
                if ($byMoneyData) return $byMoneyData;
            } else {
                return null;
            }
        } elseif ($fans || $money) {
            $has_data = null;
            if ($fans)
                $has_data = self::where('eg_follows', '<=', $fans) ->orderby('eg_follows', 'desc') ->first();
            if ($money)
                $has_data = self::where('eg_money', '<=', $money) ->orderby('eg_money', 'desc') ->first();
            return $has_data;
        } else {
            return null;
        }
    }
}
