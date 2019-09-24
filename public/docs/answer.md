### 问题详情


> 获取问题详情

####  请求方式 

- GET `http://a784admin.mxnt.net/api/v2/questions/{answer_id}`

##### 请求参数

| 参数  | 必选 | 类型 | 说明  |
| ---   |  --- | ---  | ---   |
| `answer_id` | 是  | int | 资源id | 

##### 成功返回数据

``` JSON 
{
    "message": "操作成功",
    "status_code": 200,
    "data": {
        "id": 36,
        "title": "批量制造问题1",
        "nickname": " 不拨刀-老师A",
        "avatar": "http://cdn.hbbhsjz.cn/public/upload/image1/99b12eb4389baa8d12b98fa429296454.png",
        "answer_comments_count": 12,
        "member_id": 1,
        "is_follow": false
    }
}
```

##### 参数说明 

| 参数       | 类型 | 说明  |
| ---        | ---  | ---   |
| `id` |   int  | 答案id |
| `title` |　string | 标题 |
| `nickname` |  string | 名字 |
| `avatar` | url | 头像  |
| `answer_comments_count` |  int | 评论总量|
| `member_id` | int |用户id |
| `is_follow` | boolean |  是否关注 |

### 答案列表

> 获取答案列表

####  请求方式 

- GET `http://a784admin.mxnt.net/api/v2/questions/{question_id}/answers`

##### 请求参数

| 参数  | 必选 | 类型 | 说明  |
| ---   |  --- | ---  | ---   |
| `question_id` | 是  | int | 资源id | 

##### 成功返回数据
``` JOSN
{
    "message": "操作成功",
    "status_code": 200,
    "data": {
        "data": [
            {
                "member_id": 1,
                "id": 1,
                "nickname": " 不拨刀-老师A",
                "content": "刷的时候牙刷毛放在牙龈与牙齿交界的位置，与牙齿呈45度角，刷毛横向短距离移动，每次2-3颗牙，来回刷约15下，再向上挑出牙缝中的污物。刷前牙内侧面时可以把牙刷竖起来刷。刷牙顺序由后牙向前刷，先刷两侧再刷咬合面",
                "avatar": "http://cdn.hbbhsjz.cn/public/upload/image1/99b12eb4389baa8d12b98fa429296454.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": {
                    "count": 2,
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
            },
            {
                "member_id": 3,
                "id": 2,
                "nickname": " 不拨刀-老师B",
                "content": "我是回答2",
                "avatar": "http://cdn.hbbhsjz.cn/public/upload/image1/8d21180abb7cad39047c6f043360ca35.png",
                "level": "男爵",
                "is_follow": true,
                "is_like": false,
                "comments": {
                    "count": 3,
                    "data": [
                        {
                            "nickname": " 不拨刀-老师A",
                            "id": 7,
                            "avatar": "http://cdn.hbbhsjz.cn/public/upload/image1/99b12eb4389baa8d12b98fa429296454.png",
                            "pid": 0,
                            "created_at": "2019-09-20 14:30:53",
                            "level": null,
                            "answer_comment_likes_count": 0,
                            "is_like": false,
                            "is_questionee": false,
                            "is_questioner": false,
                            "content": "谢谢",
                            "replies": {
                                "count": 4,
                                "data": [
                                    {
                                        "nickname": "A",
                                        "id": 69,
                                        "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                                        "pid": 7,
                                        "created_at": "2019-09-20 14:33:37",
                                        "level": null,
                                        "answer_comment_likes_count": 0,
                                        "is_like": false,
                                        "is_questionee": false,
                                        "is_questioner": false,
                                        "content": "1234",
                                        "PTOC": "A 回复  不拨刀-老师A"
                                    }
                                ]
                            }
                        },
                        {
                            "nickname": "A",
                            "id": 28,
                            "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                            "pid": 0,
                            "created_at": "2019-09-20 12:59:42",
                            "level": null,
                            "answer_comment_likes_count": 0,
                            "is_like": false,
                            "is_questionee": false,
                            "is_questioner": false,
                            "content": "回答儿",
                            "replies": {
                                "count": 4,
                                "data": [
                                    {
                                        "nickname": "A",
                                        "id": 69,
                                        "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                                        "pid": 7,
                                        "created_at": "2019-09-20 14:33:37",
                                        "level": null,
                                        "answer_comment_likes_count": 0,
                                        "is_like": false,
                                        "is_questionee": false,
                                        "is_questioner": false,
                                        "content": "1234",
                                        "PTOC": "A 回复  不拨刀-老师A"
                                    }
                                ]
                            }
                        }
                    ]
                }
            },
            {
                "member_id": 36,
                "id": 3,
                "nickname": "我是学生C",
                "content": "我是学生c，我正在回答《刷牙的正确方式应该是怎样的？》这个问题，尽管我什么也没回答上，却在瞎说没用的",
                "avatar": "http://cdn.hbbhsjz.cn/public/K2OiY8A8KxbarxcFXGSzcohCk3AQEzy5TE54zVAx.jpeg",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 35,
                "id": 4,
                "nickname": "1",
                "content": "我是学生B，我也来回答《刷牙的正确方式应该是怎样的？》反正干货是没有的",
                "avatar": "http://cdn.hbbhsjz.cn/public/qkZqEMwRoI0k3jgHC1a6CkeI368jcJcZJwTTuOZq.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": {
                    "count": 2,
                    "data": [
                        {
                            "nickname": "我是学生A",
                            "id": 8,
                            "avatar": "http://cdn.hbbhsjz.cn/public/K2OiY8A8KxbarxcFXGSzcohCk3AQEzy5TE54zVAx.jpeg",
                            "pid": 0,
                            "created_at": "2019-09-16 09:53:51",
                            "level": null,
                            "answer_comment_likes_count": 0,
                            "is_like": false,
                            "is_questionee": false,
                            "is_questioner": false,
                            "content": "我是学生A,我要表态，学生b算什么回答啊《我是学生B，我也来回答《刷牙的正确方式应该是怎样的？》反正干货是没有的》说了等于没说",
                            "replies": {
                                "count": 21,
                                "data": [
                                    {
                                        "nickname": "1",
                                        "id": 9,
                                        "avatar": "http://cdn.hbbhsjz.cn/public/qkZqEMwRoI0k3jgHC1a6CkeI368jcJcZJwTTuOZq.png",
                                        "pid": 8,
                                        "created_at": "2019-09-16 10:23:41",
                                        "level": null,
                                        "answer_comment_likes_count": 0,
                                        "is_like": false,
                                        "is_questionee": true,
                                        "is_questioner": false,
                                        "content": "我是学生B,上面 的，我怎么评论关你屁事了",
                                        "PTOC": "1 回复 我是学生A"
                                    }
                                ]
                            }
                        },
                        {
                            "nickname": "我是学生A",
                            "id": 72,
                            "avatar": "http://cdn.hbbhsjz.cn/public/K2OiY8A8KxbarxcFXGSzcohCk3AQEzy5TE54zVAx.jpeg",
                            "pid": 0,
                            "created_at": "2019-09-20 16:07:53",
                            "level": null,
                            "answer_comment_likes_count": 0,
                            "is_like": false,
                            "is_questionee": false,
                            "is_questioner": false,
                            "content": "我是学A,先创建个答案先",
                            "replies": {
                                "count": 3,
                                "data": [
                                    {
                                        "nickname": " 不拨刀-老师A",
                                        "id": 73,
                                        "avatar": "http://cdn.hbbhsjz.cn/public/upload/image1/99b12eb4389baa8d12b98fa429296454.png",
                                        "pid": 72,
                                        "created_at": "2019-09-20 16:09:31",
                                        "level": null,
                                        "answer_comment_likes_count": 0,
                                        "is_like": false,
                                        "is_questionee": false,
                                        "is_questioner": false,
                                        "content": "我是学生A,你回答就是垃圾，我骂你怎么了？啊？想干架啊?你让我3招先。再让我2只手2条腿.我让你知道叫服气",
                                        "PTOC": " 不拨刀-老师A 回复 我是学生A"
                                    }
                                ]
                            }
                        }
                    ]
                }
            },
            {
                "member_id": 35,
                "id": 5,
                "nickname": "1",
                "content": "再写个回答呗",
                "avatar": "http://cdn.hbbhsjz.cn/public/qkZqEMwRoI0k3jgHC1a6CkeI368jcJcZJwTTuOZq.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 35,
                "id": 6,
                "nickname": "1",
                "content": "再写个回答呗",
                "avatar": "http://cdn.hbbhsjz.cn/public/qkZqEMwRoI0k3jgHC1a6CkeI368jcJcZJwTTuOZq.png",
                "level": null,
                "is_follow": false,
                "is_like": true,
                "comments": {
                    "count": 1,
                    "data": [
                        {
                            "nickname": "A",
                            "id": 32,
                            "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                            "pid": 0,
                            "created_at": "2019-09-20 13:06:55",
                            "level": null,
                            "answer_comment_likes_count": 0,
                            "is_like": false,
                            "is_questionee": false,
                            "is_questioner": false,
                            "content": "那就给你评论",
                            "replies": {
                                "count": 1,
                                "data": [
                                    {
                                        "nickname": "A",
                                        "id": 41,
                                        "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                                        "pid": 32,
                                        "created_at": "2019-09-20 13:55:54",
                                        "level": null,
                                        "answer_comment_likes_count": 0,
                                        "is_like": false,
                                        "is_questionee": false,
                                        "is_questioner": false,
                                        "content": "评论我",
                                        "PTOC": "A 回复 A"
                                    }
                                ]
                            }
                        }
                    ]
                }
            },
            {
                "member_id": 30,
                "id": 7,
                "nickname": "A",
                "content": "刷就刷",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 8,
                "nickname": "A",
                "content": "到底要记得俊男坊继续继续",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 9,
                "nickname": "A",
                "content": "点评",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 10,
                "nickname": "A",
                "content": "点评",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 11,
                "nickname": "A",
                "content": "点评",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 12,
                "nickname": "A",
                "content": "点评",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 13,
                "nickname": "A",
                "content": "点评",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 14,
                "nickname": "A",
                "content": "点评",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 15,
                "nickname": "A",
                "content": "点评",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 16,
                "nickname": "A",
                "content": "点评",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 17,
                "nickname": "A",
                "content": "点评",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            },
            {
                "member_id": 30,
                "id": 18,
                "nickname": "A",
                "content": "点评",
                "avatar": "http://cdn.hbbhsjz.cn/public/aounyYzlOwYVOL9RNz0GJDqNKaAu7H2NX35CuWub.png",
                "level": null,
                "is_follow": false,
                "is_like": false,
                "comments": []
            }
        ],
        "count": 36
    }
}

```

##### 一级参数说明 

| 参数       | 类型 | 说明  |
| ---        | ---  | ---   |
|count | int | 答案总量 |
|data  | array | 答案数据, 没有为空数组|

##### 二级数据说明 (答案详情)

| 参数       | 类型 | 说明  |
| ---        | ---  | ---   |
|id| int| 答案id|
|member_id| int | 用户id|
|nickname| string| 用户名|
|content  | string | 答案内容 |
|avatar | url | 用户头像|
|level | string |会员等级|
|is_follow | boolean | 是否关注 |
|is_like  | boolean |是否点赞|
|comments | array | 评论内容,没有为空数组|

#### 三级数据 (用户评论)


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

#### 四级数据 (回复)

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


