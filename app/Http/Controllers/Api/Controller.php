<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller as BaseController;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    use Helpers;

    public function responseCollection(Collection $collection, TransformerAbstract $transformer)
    {
        return $this->response->collection($collection, $transformer, [], function ($resource, Manager $fractal) {
            $fractal->setSerializer(new CustomSerializer());
        });
    }

    public function responseItem($item, TransformerAbstract $transformer)
    {
        return $this->response->item($item, $transformer, [], function ($resource, Manager $fractal) {
            $fractal->setSerializer(new CustomSerializer());
        });
    }

    public function responsePaginate(Paginator $paginator, TransformerAbstract $transformer)
    {
        return $this->response->paginator($paginator, $transformer, [], function ($resource, Manager $fractal) {
            $fractal->setSerializer(new CustomSerializer());
        });
    }

    public function responseData(array $data)
    {
        return $this->response()->array([
            'message' => '操作成功',
            'status_code' => 200,
            'data' => $data
        ], 200);
    }

    public function responseSuccess($message='操作成功')
    {
        return $this->response()->array([
            'message' => $message,
            'status_code' => 200
        ], 200);
    }

    public function responseFailed($message='操作失败')
    {
        return $this->response()->array([
            'message' => $message,
            'status_code' => 400
        ], 400);
    }

    public function responseError($message='未知错误')
    {
        return $this->response()->array([
            'message' => $message,
            'status_code' => 500
        ], 500);
    }


    /**
     * 转换图片url
     *
     */
    public function transferUrl(string $url) //: string
    {
        if(!isset(parse_url($url)['host'])) {
            return substr($url,0, 1) !== '/' ? env('APP_URL') . '/' . $url : env('APP_URL') . $url;
        } else {
            return $url;
        }
    }


    /**
     * 上传文件
     *
     */
    public function DNSupload(string $path)
    {
       $url =  Storage::url($path); 
       $disk = Storage::disk('qiniu');
       $disk->put($path, file_get_contents(".".$url));
       $full_path = str_replace(' ', '', $disk->getUrl($path));
       return $full_path;
    }

    /**
     * 将数组遍历为数组树
     * @arr     有子节点的目录树
     * @tree    遍历赋值的树
     * @return  array
     *
     */
    protected function _arrToTree($items, $pid = 'pid')
    {
        $result = [];
        $tmp = [];
        foreach($items as $k=>$el) {
            if ($k === 0 && $el['pid'] == 0) {
               $tmp = $el; 
            } elseif ($k !== 0 && $el['pid'] == 0 ) {
                $tmp['children'] = $tmp['children'] ?? [];
                $result[] = $tmp;
                unset($tmp);
                $tmp = $el;
            } else {
                if (!isset($tmp['children'])) {
                    $parent = $tmp;
                } else {
                    $parent = end($tmp['children']);
                }
                $el['PTOC'] = $parent['nickname'] . '回复' . $tmp['nickname'];
                $tmp['children'][] = $el;
            }
            // 补上最后一刀
            if (++$k === count($items)) {
                $tmp['children'] = $tmp['children'] ?? [];
                $result[] = $tmp;
            }

        }
        return $result;
         /* $map  = []; */
         /* $tree = []; */
         /* foreach ($items as &$it){ */
         /*   $el = &$it; */
         /*   $el['children'] = []; */
         /*   $map[$it['id']] = &$it; */
         /* }  //数据的ID名生成新的引用索引树 */
         /* foreach ($items as &$it){ */
         /*   $parent = &$map[$it[$pid]]; */
         /*   if($parent) { */
         /*     $parent['children'][] = &$it; */
         /*   }else{ */
         /*     $tree[] = &$it; */
         /*   } */
         /* } */
         /* return $tree; */
    }
}
