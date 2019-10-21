### 发布视频

####  请求方式 

- POST `http://a784admin.mxnt.net/api/videos`

##### 请求参数

| 参数  | 必选 | 类型 | 说明  |
| ---   |  --- | ---  | ---   |
| `video` | 是  | url | 已经上传的文件url | 
|title |	是 |	string|	视频标题|
|tag_id |	是 |	int|	标签id|
|image |	是 |	string|	要上传的图片文件|

##### 成功返回数据

``` JSON 
{
    "message": "操作成功",
    "status_code": 200,
}
```

