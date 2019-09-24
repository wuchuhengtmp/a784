### 答案评论

> 获取答案评论

#### 请求方式

* GET `http://a784admin.mxnt.net/api/v2/answercomments/{answer_id}`

#### 请求参数说明

| 参数 | 必选 | 类型 | 说明  |
| ---  | ---  | ---- | ----- |
|answer_id | 是 | 答案id| 
| page | 否 | int  |  分页码|

#### 返回数据

``` 
{
    "count": 3,
    "data": [
        {
            "nickname": " 不拨刀-老师B",
            "id": 6,
            "avatar": "http://cdn.hbbhsjz.cn/public/upload/image1/8d21180abb7cad39047c6f043360ca35.png",
            "pid": 0,
            "created_at": "2019-09-20 14:30:49",
            "level": "男爵",
            "answer_comment_likes_count": 2,
            "is_like": false,
            "is_questionee": false,
            "is_questioner": true,
            "content": "这个答案真不错",
            "replies": {
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
        },
        {
            "nickname": "我是学生A",
            "id": 11,
            "avatar": "http://cdn.hbbhsjz.cn/public/K2OiY8A8KxbarxcFXGSzcohCk3AQEzy5TE54zVAx.jpeg",
            "pid": 0,
            "created_at": "2019-09-20 14:55:28",
            "level": null,
            "answer_comment_likes_count": 5,
            "is_like": true,
            "is_questionee": false,
            "is_questioner": false,
            "content": "添加一条评论",
            "replies": {
                "count": 8,
                "data": [
                    {
                        "nickname": "A",
                        "id": 71,
                        "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                        "pid": 11,
                        "created_at": "2019-09-20 14:59:09",
                        "level": null,
                        "answer_comment_likes_count": 2,
                        "is_like": true,
                        "is_questionee": false,
                        "is_questioner": false,
                        "content": "1",
                        "PTOC": "A 回复 我是学生A"
                    }
                ]
            }
        }
    ]
}
```

#### 参数说明 

| 参数       | 类型 | 说明  |
| ---        | ---  | ---   |
|id| int| id 评论id|
|member_id| int | 用户id|
|nickname| string| 用户名|
|content  | string | 评论内容  |
|avatar | url | 用户头像|
|level | string |会员等级|
|answer_comment_likes_count| int|评论总数|
| created_at | date | 时间|
|is_questioner | boolean| 是否答者|
|is_questionee | boolean  | 是否作者|
|is_like  | boolean |是否点赞|
|content| array | 评论内容|
|replies | array | 回复,没有时为空数组|
