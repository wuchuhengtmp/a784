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

