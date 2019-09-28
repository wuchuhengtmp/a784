### 用户协议 
> 用户协议

####  请求方式 

- GET `http://a784admin.mxnt.net/api/agreement`


##### 成功返回数据

``` JSON 
{
    "message": "操作成功",
    "status_code": 200,
    "data": {
        "title": "用户协议",
        "content": "<font color=\"#333333\">这是用户协议的内容 ，在后台修改 1</font>"
    }
}
```

##### 参数说明 

| 参数       | 类型 | 说明  |
| ---        | ---  | ---   |
| `title` |　string | 标题 |
| `content` |   html  | 内容  |


### 免责声明
> 免责声明

####  请求方式 

- GET `http://a784admin.mxnt.net/api/disclaimer`


##### 成功返回数据

``` JSON 
{
    "message": "操作成功",
    "status_code": 200,
    "data": {
        "title": "免责声明",
        "content": "这是免责声明的内容 ，在后台修改"
    }
}
```

##### 参数说明 

| 参数       | 类型 | 说明  |
| ---        | ---  | ---   |
| `title` |　string | 标题 |
| `content` |   html  | 内容  |

