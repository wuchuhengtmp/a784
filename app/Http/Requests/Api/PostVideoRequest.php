<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PostVideoRequest extends FormRequest
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
            'image' => 'required|image',
            'video' => 'required|mimes:mp4'
        ];
    }
}
