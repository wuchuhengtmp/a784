### 绑定手机

> 绑定手机


#### 请求方式

* PUT `http://a784admin.mxnt.net/api/members/me/phone`

#### 请求参数说明

| 参数 | 必选 | 类型 | 说明  |
| ---  | ---  | ---- | ----- |
|verification_key| 是 | string |短信key |
| verification_code| 是 | int  | 验证码 | 

#### 说明 

> 先调用`获取验证码`获取`key`，再调用这个接口

#### 返回数据

``` 
{
    "message": "操作成功",
    "status_code": 200
}
```

