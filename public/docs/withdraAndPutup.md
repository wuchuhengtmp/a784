### 我的充值和提现



####  请求方式 

- GET `http://a784admin.mxnt.net/api/accountLogs`

##### 请求参数

| 参数  | 必选 | 类型 | 说明  |
| ---   |  --- | ---  | ---   |
| `type` | 是  | int| 1充值记录， 2提现记录 | 
| `page` | 否  | int|分页码默认1 | 

##### 成功返回数据

``` JSON 
{
    "message": "操作成功",
    "status_code": 200,
    "data": {
        "data": [
            {
                "id": 179,
                "money": 0.01,
                "created_at": "2019-10-11 12:05:25",
                "out_trade_no": "R20191011120521863261",
                "notice": "热搜置顶"
            },
            {
                "id": 176,
                "money": 0.01,
                "created_at": "2019-10-11 12:01:46",
                "out_trade_no": "R20191011120140116278",
                "notice": "热搜置顶"
            },
            {
                "id": 168,
                "money": 0.01,
                "created_at": "2019-10-11 11:40:54",
                "out_trade_no": "R20191011114047969913",
                "notice": "热搜置顶"
            },
            {
                "id": 167,
                "money": 0.01,
                "created_at": "2019-10-11 11:40:15",
                "out_trade_no": "R20191011114008701555",
                "notice": "热搜置顶"
            },
            {
                "id": 166,
                "money": 0.01,
                "created_at": "2019-10-11 11:38:56",
                "out_trade_no": "R20191011113839514246",
                "notice": "热搜置顶"
            },
            {
                "id": 159,
                "money": 0.01,
                "created_at": "2019-10-11 11:25:03",
                "out_trade_no": "R20191011112457570545",
                "notice": "热搜置顶"
            },
            {
                "id": 158,
                "money": 0.01,
                "created_at": "2019-10-11 11:24:02",
                "out_trade_no": "R20191011112341549739",
                "notice": "热搜置顶"
            },
            {
                "id": 157,
                "money": 0.01,
                "created_at": "2019-10-11 11:22:53",
                "out_trade_no": "R20191011112238659473",
                "notice": "热搜置顶"
            },
            {
                "id": 156,
                "money": 0.01,
                "created_at": "2019-10-11 11:19:17",
                "out_trade_no": "R20191011111859423859",
                "notice": "热搜置顶"
            },
            {
                "id": 155,
                "money": 0.01,
                "created_at": "2019-10-11 11:16:54",
                "out_trade_no": "R20191011111647747765",
                "notice": "充值"
            },
            {
                "id": 139,
                "money": 0.01,
                "created_at": "2019-10-11 09:41:02",
                "out_trade_no": "R20191011094041695961",
                "notice": "充值"
            },
            {
                "id": 138,
                "money": 0.01,
                "created_at": "2019-10-11 09:39:11",
                "out_trade_no": "R20191011093906432640",
                "notice": "充值"
            }
        ],
        "count": 17
    }
}
```

##### 参数说明 

| 参数       | 类型 | 说明  |
| ---        | ---  | ---   |
| `id` |   int  | id |
| `money` |　float| 额度|
| `created_at` |  date| 时间 |
| `out_trade_no` |  string |  订单号|
| `notice` |  string | 备注 |
| `type` |  int | 1支出2收入3提现4充值 |
| `count` |  int | 总量 |

