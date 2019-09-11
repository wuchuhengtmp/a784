<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Posts;
use App\Transformers\VideosTransformer;
use App\Models\Tags;

class TagsController extends Controller
{
    /**
     * 输出分类标签
     *
     */
    public function index(Tags $Tags)
    {
        $Tags = $Tags->get();
        return $this->responseData($Tags->toArray());
    }
}
