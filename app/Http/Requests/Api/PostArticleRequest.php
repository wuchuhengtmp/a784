<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PostArticleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tag_id' => [
                'required', 'numeric'
            ],
            'title' => 'required|string',
            'image1' => 'required|image',
            'image2' => 'image',
            'image3' => 'image',
            'content' => 'required',
        ];
    }
}
