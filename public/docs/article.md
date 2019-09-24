##### 描述

> 获取文章详情

####  请求方式 

- GET `http://a784admin.mxnt.net/api/v2/articles/{post_id}`

##### 请求参数

| 参数  | 必选 | 类型 | 说明  |
| ---   |  --- | ---  | ---   |
| `post_id` | 是  | int | 资源id | 

##### 成功返回数据

``` JSON 
    {
    "message": "操作成功",
    "status_code": 200,
    "data": {
        "id": 3,
        "member_id": 6,
        "title": "测试文章",
        "content": "文章内容",
        "created_at": "2019-09-12 17:13:53",
        "nickname": "亮剑",
        "avatar": "http://cdn.hbbhsjz.cn/public/upload/image1/605c4ac865d934b2e1b13794444cf9f2.png",
        "is_follow": false,
        "is_like": false,
        "is_favorite": false,
        "images": [
            {
                "url": "/uploads/images/u=3460739377,569424089&fm=26&gp=0.jpg"
            },
            {
                "url": "/uploads/images/vei.jpg"
            },
            {
                "url": "/uploads/images/video_test1.jpg"
            }
        ]
    }
}
```

##### 参数说明 

| 参数       | 类型 | 说明  |
| ---        | ---  | ---   |
| `id`  | int  | 资源id| 
| `member_id`  | int  | 用户id| 
|`title`|  string | 标题|
|`content`| string |内容 |
|`created_at`|  date | 时间|
|`nickname`| string |名字|
|`avatar`| url | 头像 |
|`is_follow`|  boolean | 是否关注 |
|`is_like`|  boolean  | 是否点赞|
|`is_favorite`|  boolean |是否收藏 |
|`images`|  array | 图片组|
