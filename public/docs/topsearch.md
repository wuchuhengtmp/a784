### 置顶

> 置顶视频

#### 请求方式

* POST `http://a784admin.mxnt.net/api/topsearch/{post_id}`

#### 请求参数说明

| 参数 | 必选 | 类型 | 说明  |
| ---  | ---  | ---- | ----- |
|post_id| 是 | int | 视频id| 
|pay_type | 是 | int | 1龙币3支付宝|
|expense| 是 |  int | 支付的金额 |


#### 返回数据

``` 
{
    "message": "操作成功",
    "status_code": 200
}
```

#### 支付宝支付 返回数据 
``` json

{
    "message": "操作成功",
    "status_code": 200,
    "data": {
        "signture": "app_id=2019092067583963&format=JSON&charset=utf-8&sign_type=RSA2&version=1.0&return_url=http%3A%2F%2Fa784admin.mxnt.net%2Fapi%2Fpays%2Falipay%2Freturn&notify_url=http%3A%2F%2Fa784admin.mxnt.net%2Fapi%2Fpays%2Falipay%2Fnatify&timestamp=2019-10-11+00%3A06%3A30&biz_content=%7B%22out_trade_no%22%3A%22R20191011000630416448%22%2C%22total_amount%22%3A%2210%22%2C%22subject%22%3A%22%5Cu5145%5Cu503c%22%2C%22is_topsearch%22%3Atrue%2C%22post_id%22%3A2%2C%22product_code%22%3A%22QUICK_MSECURITY_PAY%22%7D&method=alipay.trade.app.pay&sign=sTzGdQVqwk1uLKCSxEwd0pLsXMWxGzfWkz46ynMUSfmSj%2FTrhHYDpU5HmwQK74HXLVZGIAb2coGQeTmqiQ3vsbqz9RRVE%2F9H%2Fo2TTcD7l9gvuLntugm3Ur67vtKCM6%2FYoJm83jSa59Qb8c2lARmVmz9Pv%2BiuF4Q%2FJ%2Bboc7idlHMu9AKI3T5pBuyHds8PAwqJuVHgRum6ESZ8kjeD6Ccre6vgUENvgNstx66C7tcI3i7nnVlZ7wt4GmWOfytngjkcg4pBCsgVvqGWU%2Fli6MGALJtKZfwpsd5JzN0uhmK3Dqz8hG6D1RXhMrj8KcJkImCFOpRhz6384gS0yD0zNDFfLg%3D%3D"
    }
}
```

