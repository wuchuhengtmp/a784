### 提现

####  请求方式 

- POST `http://a784admin.mxnt.net/api/withdraw`

##### 请求参数

| 参数  | 必选 | 类型 | 说明  |
| ---   |  --- | ---  | ---   |
|`action` |  是 | int |  2微信1支付宝|
|`account` |  是 | string| 账号|
|`expense` |  是 | numeric| 额度|
|`name` |  是 | string  |  账号用户名|


##### 成功返回数据

``` JSON 
{
    "message": "操作成功",
    "status_code": 200
}
```

