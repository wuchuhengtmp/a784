<?php
require 'vendor/autoload.php';
use Tymon\JWTAuth\Facades\JWTAuth;

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
        });

        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            if ($data = json_decode($frame->data, true)){
                switch($data['type']) {
                    // 登录
                case 'login' :
                    if (!isset($data['token'])) $this->server->close($frame->fd);
                    $member_id = self::parseToken($data['token'])['sub'];
                    // 登记连接
                    $Redis = $this->getRedisInstance();
                    $Redis->hset(getenv('REDIS_PREFIX') . $member_id, $frame->fd, $data['token']);
                    $Redis->hset(getenv('REDIS_PREFIX') . 'relevence', $frame->fd, $data['member_id']);
                    $data_format['likes']           = self::getMessageData($member_id, 1);
                    $data_format['follows']         = self::getMessageData($member_id, 2);
                    $data_format['comments']        = self::getMessageData($member_id, 3);
                    $data_format['replies']         = self::getMessageData($member_id, 4);
                    $data_format['system_messages'] = self::getSystemMessage($member_id);
                    $like_count            = $data_format['likes']['count'] ?? 0;
                    $follows_count         = $data_format['follows']['count'] ?? 0;
                    $comments_count        = $data_format['comments']['count'] ?? 0;
                    $replies_count         = $data_format['replies']['count'] ?? 0;
                    $system_messages_count = $data_format['system_messages']['count'] ?? 0;
                    $data_format['noread_count'] = intval($like_count) 
                        + intval($comments_count) 
                        + intval($replies_count) 
                        + intval($system_messages_count) 
                        + intval($follows_count);
                    $this->server->push($frame->fd, json_encode([
                        'type'    => 'data', 
                        'data'    =>$data_format
                    ]));
                    break;
                case 'ping' : 
                    /* var_dump('ping'); */
                    break;
                }
            }
        });

        $this->server->on('close', function ($ser, $fd) {
            // 剔除关闭的连接
            $Redis = $this->getRedisInstance();
            $member_id = $Redis->hGet(getenv('REDIS_PREFIX') . 'relevence', $fd);
            $Redis->hDel(getenv('REDIS_PREFIX') . 'relevence', $fd);
            $Redis->hDel(getenv('REDIS_PREFIX') . $member_id, $fd);
        });

        // 推送消息
        $this->server->on('request', function ($request, $response) {
            if ($member_id = $request->post['member_id']) {
                $Redis = $this->getRedisInstance();
                if ($Redis->exists(getenv('REDIS_PREFIX') . $member_id)) {
                    $all_ids = $Redis->hGetAll(getenv("REDIS_PREFIX") . $member_id);
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

    /**
     * pdo 实例
     *
     */
    public static function getPdoInstance()
    {
        try {
            $dbh = new PDO("mysql:host=".getenv('DB_HOST').";dbname=" . getenv('DB_DATABASE'),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD'),
                array(
                    PDO::ATTR_PERSISTENT => true
                )
            );
        } catch (PDOException $e) {
            return false;
        }
        return $dbh;
    }

    /**
     * 获取消息
     *
     */
    public static function getMessageData($member_id, $type)
    {
        $result = [
            'content'    => '',
            'type'       => '',
            'created_at' => '',
            'count'      =>''
        ];
        if (!$conn = self::getPdoInstance()) return false;
        $sql = "
            SELECT
                    count(id) as count
            FROM
                    messages
            WHERE
                    be_like_member_id = $member_id
            AND is_readed = 0
            AND type = $type";
        $data = $conn->query($sql)->fetch();
        if ($data['count'] == 0) return $result;
        $result['count']  = $data['count'];
        $sql = "
            SELECT
                content,
                created_at    
            FROM
                    messages
            WHERE
                    be_like_member_id ={$member_id}
            AND is_readed = 0
            AND type = $type 
            ORDER BY id desc
            LIMIT 1 ";
        $Message = $conn->query($sql);
        if ($Message) {
            $Message = $Message->fetch();
            $result['content'] = $Message['content'];
            $result['type'] = $type;
            $result['created_at'] = $Message['created_at'];
        } 
        return $result;
    }

    /**
     * 获取系统消息
     *
     */
    public static function getSystemMessage($member_id)
    {
        $result = [
            'title'      => '',
            'type'       => '',
            'created_at' => '',
            'count'      => ''
        ];
        $conn = self::getPdoInstance();
        $sql = "
            SELECT
                count(id) as count
            FROM
                messages M
            WHERE
                be_like_member_id = $member_id
            AND is_readed = 0
            AND type IN(5,6)
            ORDER BY id desc
            LIMIT 1 ";
        $data = $conn->query($sql)->fetch();
        if ($data['count'] == 0) return $result;
        $result['count']  = $data['count'];
        $sql = "
            SELECT
            S.title,
            S.created_at,
            M.type
            FROM
                messages M
            INNER JOIN system_message_details S ON S.id = M.system_message_detail_id
            WHERE
                M.be_like_member_id = $member_id
            AND M.is_readed = 0
            AND M.type IN(5,6)
            ORDER BY M.id desc
            LIMIT 1";
        $Message = $conn->query($sql)->fetch();
        $result['title'] = $Message['title'];
        $result['type'] = $Message['type'];
        $result['created_at'] = $Message['created_at'];
        return $result;
    }

    /**
     * 解析token
     *
     * @return payload 数组
     */
    public static function parseToken(string $token) 
    {
        list($header, $payload, $signature) = explode('.', $token); 
        return json_decode(base64_decode($payload), true);
    }
}

new WebsocketTest();
