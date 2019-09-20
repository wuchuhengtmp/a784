<?php
require 'vendor/autoload.php';
class WebsocketTest {
    public static $_redis;
    public $redis_db = 1;
    public $server;

    public function __construct() {
        $dotenv = Dotenv\Dotenv::create(__DIR__);
        $dotenv->load();

        $this->server = new Swoole\WebSocket\Server("0.0.0.0", 7777);
        $this->server->set([
            'heartbeat_check_interval' => 60,
            'daemonize' => true,
            'log_file' => __DIR__ . '/server.log',
            'heartbeat_idle_time' => 600,
        ]);

        $this->server->on('open', function (swoole_websocket_server $server, $request) {
            if (!$request->get['member_id']) {
                $server->close($request->fd);
                return false;
            }
            // 登记连接
            $Redis = $this->getRedisInstance();
            $Redis->hset($request->get['member_id'], $request->fd, date('Y-m-d H:i:s', time()));
            // 关联连接id用于断开剔除
            $Redis->hset('relevence', $request->fd, $request->get['member_id']);
            $server->push($request->fd, json_encode([
                'type' => 'ping' ,
                'data' => [
                    'message' => 'welcome!'
                ]
            ]));
        });

        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            $server->push($frame->fd, json_encode(['type' =>'ping', 'data'=>['microtime' => microtime(true)]]));
        });

        $this->server->on('close', function ($ser, $fd) {
            // 剔除关闭的连接
            $Redis = $this->getRedisInstance();
            $member_id = $Redis->hGet('relevence', $fd);
            $Redis->hDel('relevence', $fd);
            $Redis->hDel($member_id, $fd);
        });

        // 推送消息
        $this->server->on('request', function ($request, $response) {
            if ($member_id = $request->post['member_id']) {
                $Redis = $this->getRedisInstance();
                if ($Redis->exists($member_id)) {
                    $all_ids = $Redis->hGetAll($member_id);
                    // 把消息推送到这个用户下的所有连接中
                    foreach($all_ids as $fd=>$time) {
                         if ($this->server->isEstablished($fd)) {
                             $ready_data['data'] = json_decode($request->post['data'],true);
                             $ready_data['type'] = 'data';
                             $this->server->push($fd, json_encode($ready_data));
                         }
                    }
                }
            }
        });

        $this->server->start();
    }


    /**
     * redis实例
     *
     */
    public function getRedisInstance()
    {
        if(!isset(self::$_redis)) {
            $redis = new \Redis();
            $redis->connect(
                getenv('REDIS_HOST'),
                getenv('REDIS_PORT')
            );
            $redis->select($this->redis_db);
            self::$_redis = $redis;
        }
        return self::$_redis;
    }
}


new WebsocketTest();
