### 上传文件

####  请求方式 

- POST `http://a784admin.mxnt.net/api/resource`

##### 请求参数

| 参数  | 必选 | 类型 | 说明  |
| ---   |  --- | ---  | ---   |
| `resource` | 是  | file |  要上传的文件| 

##### 成功返回数据

``` JSON 
{
    "message": "操作成功",
    "status_code": 200,
    "data": {
        "url": "http://cdn.hbbhsjz.cn/public/4sS6GZouqvuxGrY97nqJjFwqj5AtgSDDrKrJ715S.png"
    }
}
```

