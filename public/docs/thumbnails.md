## 视频截图

> 用于生成视频截图链接

### 请求方式

- POST `http://a784admin.mxnt.net/api/videos/thumbnails`

### 请求表单参数

| 参数 | 必选 |  类型 | 说明 |
| ---- | ---- | ---- | ---- |
| video_url | 是 | url | 视频链接 |


#### 成功响应
``` json
{
    "message": "操作成功",
    "status_code": 200,
    "data": {
        "thumbnails": "http://qiniuyun.yi8kk3.cn/uploads/frame_2019-10-19-11-01-20-7595.jpg"
    }
}
```

#### 参数说明 
| 参数 |   类型 | 说明 |
| ---- |  ---- | ---- |
| thumbnails|  url | 截图链接 |
