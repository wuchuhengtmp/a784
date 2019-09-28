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
        'return_url' => '',

        /* 'ali_public_key' => env('ALI_PUBLIC_KEY'), */
        // 加密方式： **RSA2**
        /* 'private_key' => env('PRIVATE_KEY'), */
        'log' => [ // optional
            'file' => './logs/alipay.log',
            'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
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

    public function __construct()
    {
        $this->config['notfy_url'] = env('NOTIFY_URL');
        $this->config['ali_public_key'] = env('ALI_PUBLIC_KEY');
        $this->config['private_key'] = env('PRIVATE_KEY');
        
    }

    public function index(PayRequest $Request)
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => $Request->expense,
            'subject' => '充值',
        ];
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

    public function notify()
    {
        $alipay = Pay::alipay($this->config);

        try{
            $data = $alipay->verify(); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况

            Log::debug('Alipay notify', $data->all());
        } catch (\Exception $e) {
            // $e->getMessage();
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
                'is_transfer_out'    => 1,
                'notice'             => '热搜置顶',
                'member_id'          => $Member->id,
                'money'              => $Request->expense,
                'is_out_transaction' => 0,
                'transfer_type_id'   => 1
            ]);
            return $this->responseSuccess();
        }
    }
}
