### 答案评论的回复

> 获取答案评论的回复

#### 请求方式

* GET `http://a784admin.mxnt.net/api/v2/answercomments/{comment_id}/replies`

#### 请求参数说明

| 参数 | 必选 | 类型 | 说明  |
| ---  | ---  | ---- | ----- |
|comment_id| 是 | 评论的id| 
| page | 否 | int  |  分页码|
|  comment_limit| 否 | int  |  分页输出的量,默认每页输出1|

#### 返回数据

``` 
{
    "message": "操作成功",
    "status_code": 200,
    "data": {
        "count": 10,
        "data": [
            {
                "nickname": "A",
                "id": 38,
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "pid": 6,
                "created_at": "2019-09-20 14:31:09",
                "level": null,
                "answer_comment_likes_count": 0,
                "is_like": false,
                "is_questionee": false,
                "is_questioner": false,
                "content": "我",
                "PTOC": "A 回复  不拨刀-老师B"
            }
        ]
    }
}
```

#### 参数说明 

| 参数       | 类型 | 说明  |
| ---        | ---  | ---   |
|id| int| id 评论id|
|nickname| string | 用户名| 
|id|  int | 回复id |
|avatar|  url | 头像|
|created_at|  date | 时间|
|level| string | 等级 |
|is_like|  boolean| 是否点赞|
|is_questionee|  boolean | 是否答主|
|is_questioner| boolean | 是否题主|
|content|  string |  内容 |
|PTOC| sting | 谁回复 谁|
