<?php

namespace App\Http\Controllers\Api;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;
use Illuminate\Http\Request;
use App\Http\Requests\Api\PayRequest;
use App\Models\{
    Posts,
    Members,
    TopSearchOrders,
    Configs,
    AccountLogs
};

class PayController extends Controller
{
    protected $config = [
        'app_id' => '2019092067583963',
        'return_url' => 'http://a784admin.mxnt.net/api/pays/alipay/return',
        'notify_url' => 'http://a784admin.mxnt.net/api/pays/alipay/natify' ,
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAhM6XySyT9jgXeb+Pk3NReT5yjWayPqOJDynX+FTJ9Vowbno8CoyQvN+5exDg3HlT7/WMk36ziBpQAx2pOvoIE7vqOn1Kzxe5YV9p034luc758n4bLSiGLT5fKWvjEoEYpOr7masgabLjcqdN5w1d8H9oFr3/2wLg2JwAKkNf3LIu5Ic9DHOrKeP05JvmknRbf8g+YEM2FOA8BhwNNfFyqxBbdQY/aBMEph+dwThM7ip7VK1EvC/uWYRxg3ED4ydDXfB9XO/bdf+TgDiHW/g7gMjRjtyHWjCyYWlsjo6vwAV+xs399bsjx8+K7kcSRA0lyNETJqy2sWIXQR8fwEGTiQIDAQAB',
        // 加密方式： **RSA2**
        'private_key' =>'MIIEpAIBAAKCAQEA35VNqYnXAy43AgXpyOrz/EkuNBaO4oRpdtRa1sX/O7k6QhM15nt1rwbQefZ59D7g53yksMsN6hmnoBGBgXfhkpNOMENQMarZTY3ZEG2rx9wx07KasQQ6LvQgxjqZj/KM9f7XPx19z1XZp9E0VsMAyWUXZLcoKxOdDPf0P8JSt8160ccZg/kRn44o1Lw8wp5/KdbrQN+4xkokFioZ21t04z8pF8+A68rKqfWwuFRPjDeNdSE4YHZX+NOhI9707jYRRTagyHPsGlx1WedSYjDT1k2Ra5OXNDKhpv3bXsy3MSzKpSV0+jdrOKrzGdlaTEOzZ+Hr0I1U1eBK76tyYwRLDwIDAQABAoIBAQDG70mSuBqfsdcv7aL+Kk+9AkAiCJBJ7BcKrBfHUZSvxzeW4xDqap9jhGSqoCwSrn/eeIDw7TsMOJd1TR413DzQ9lBzkPEhwCppXvTsMSjPQ5TyD9CkAGbksEMZHbrU4bOajY1nkw4GFRT8xKAVMpzYlSIjcvRCn8j1aQniUTzYXEpH/ZEfZnnXUAM3d/tBY3Qr0B3usuv2obV6r75/p4n27mGTbTO015MDxjiPoDP4t9Yf1Om8Qe1j7b2W5jEouf/qRlDEekwTMh1q/jOk9mQseEwQGtCKXe176wneY/Y09sOJYevfCUmrpunereWXCSssmGtZl1ZKWJ6MkcUD8dABAoGBAPMIbOQ0YguM2dK18uKtdH8uB4k6qi3fO2PFkS0iIRZnuVO8nWbBxD1MyGS1wkPJgim6h/doLR1kRJNet76IuRRJEI6ZvVaW2hWFpQu2ugf/fK79FDrbf2+LQcipyVKSmHXZxdA2kVcOq31aVhJ7vvvK+9/N9pMpb7uxr3UFVd4PAoGBAOuDNy/zXORvu6FsQBXe8TglYAFXFwumnrsh9+NHTYDNh59U4RS2HEsBQj7QV/B3mizw4g24+y6LBvOeksEGgY0av0rbLlh701yTVrAFJ7uithWjC0gYA1ftDlZPPaymP8qyReBlbjKOuTpeTDn9erKrq0D/32xbNWlAyusK58MBAoGAb/GCjr7eJmnTb24lmWnCDk66Y+hkuMppRbScAUkGKpbOU5a+fbVk2cODTng2KhkoXmYv+LLAjRhBSgxH4HiDn6dj+/surjK/80fi1PluyP5ShRvHdLDkCxH+1Bn4xJMHrMkJh7WKzqnQLeYtXUgomTxPNjBdkj103OSkZ+d0PNsCgYEAmmXyxz/f1W8e7kv+k5gOQkXWc+p5lEzO4VX6ooj7WYbk8+L8kMx3LgEMQgvqqx5t+CqPuHleSvwgOZTrFxrB0hUH9fZNovrC7X02pr0qeEvK3dJ/Met0Pa+O56yZfVecmLFZOCynGwQQkSCDDr2MNBhxdHKLMgl1saQlpAQJPwECgYB67vTz9K1IP140RBPDgJTllC9hCMyF448EBSndAoqneWUBz8IwyOk3oZVkhJmE6VMzpohNWp3KvSJRtWPkYNiOff1oOA5JU0MWM5J3cfeDO/QRIgqnzS5lsntJm5o9Np8gJtMfYFGZOGttLMXbj2qveWyqTsyu/MFzNROnLjqyqg==',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ];

    /* public function __construct() */
    /* { */
    /*     $this->config['notfy_url']      = "http://a784admin.mxnt.net/api/natify"; */
    /*     $this->config['ali_public_key'] = env('ALI_PUBLIC_KEY'); */
    /*     $this->config['private_key']    = env('PRIVATE_KEY'); */
        
    /* } */

    public function index(PayRequest $Request)
    {
        $out_trade_no = 'R'.date('YmdHis').mt_rand(100000,999999);

        $accountLog = AccountLogs::create([
            'type'      => 4,
            'out_trade_no'      => $out_trade_no,
            'money'     => $Request->expense,
            'status'    => 0,
            'transfer_type_id' => 3,
            'member_id' => $this->user()->id,
            'notice'    => '充值'
        ]);
        $order = [
            'out_trade_no' => $out_trade_no,
            'total_amount' => $Request->expense,
            'subject' => '充值'
        ];

        //
        $alipay = Pay::alipay($this->config)->app($order);

        return $this->responseData([
            'signture' => $alipay->getContent()
        ]);
    }

    public function return()
    {
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！

        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
    }

    public function notify(Request $Request)
    {
        $alipay = Pay::alipay($this->config);
        /* $data = $Request->toArray(); */
        try{
            $data = $alipay->verify(); // 是的，验签就这么简单！
            $data =  $data->toArray();
            if (in_array($data['trade_status'],['TRADE_SUCCESS','TRADE_FINISHED']))
            {
                $out_trade_no = $data['out_trade_no'];
                $accountLog = AccountLogs::where(['out_trade_no' =>$out_trade_no ,'status' => 0])->first();
                if (!empty($accountLog)) {
                    $accountLog->status = 1;
                    $accountLog->save();
                    $accountLog->member->balance += $data['buyer_pay_amount'];
                    $is_save = $accountLog->member->save();
                    
                }
            }
            Log::debug('Alipay notify', $data);
        } catch (\Exception $e) {
            /* Log::debug('Alipay notify',$e->getMessage()); */
            // ;
        }

        return $alipay->success();
    }

    /**
     *  置顶
     *
     */
    public function topsearchStore(Request $Request)
    {
        $Request->validate([
            'pay_type'  => 'required|in:1,3',
            'expense'  => 'required|gt:0'
        ]); 
        $Posts = Posts::where('id', $Request->post_id)
            ->where('content_type', 1)
            ->first();
        if(!$Posts) return $this->responseError('没有这个视频');
        // 龙币支付 
        if ($Request->pay_type == 1) {
            $Member = Members::find($this->user()->id);
            if ($Member->balance < $Request->expense) return  $this->responseError('余额不足额');
            $Member->balance -= $Request->expense;
            $Member->save();
            $Price = Configs::where('name', 'top_search_price')->first();
            TopSearchOrders::create([
                'post_id'        => $Request->post_id,
                'nickname'       => $Member->nickname,
                'title'          => $Posts->title,
                'member_id'      => $Member->id,
                'price'          => $Price->value . 's/元' ,
                'expense'        => $Request->expense,
                'top_time_limit' => $Request->expense * $Price->value,
                'top_end_time'   => date("Y-m-d H:i:s", time() + $Request->expense * intval($Price->value)),
                'transfer_type'  => 1
            ]);
            // 记帐
            AccountLogs::create([
                'status'    => 1,
                'notice'             => '热搜置顶',
                'member_id'          => $Member->id,
                'money'              => $Request->expense,
                'transfer_type_id'   => 1
            ]);
            return $this->responseSuccess();
        }
    }
}
