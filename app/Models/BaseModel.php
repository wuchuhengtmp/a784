<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public static $_redis;
    /**
     * redis实例
     *
     */
    public static function getRedisInstance()
    {
        if(!isset(self::$_redis)) {
            $redis = new \Redis();
            $redis->connect(
                getenv('REDIS_HOST'),
                getenv('REDIS_PORT')
            );
            $redis->select(env('REDIS_DB'));
            self::$_redis = $redis;
        }
        return self::$_redis;
    }
}
