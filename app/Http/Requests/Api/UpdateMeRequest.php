<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nickname'        => 'filled|string',
            'sign'            => 'string',
            'sex'             => 'filled|numeric|in:1,2',
            'age'             => 'filled|numeric|gte:1|lt:140',
            'job'             => 'filled|numeric|in:1,2',
            'born'            => 'filled|date',
            'weixin'          => 'filled|string',
            'school'          => 'filled|string',
            'department'      => 'filled|string',
            'professional'    => 'filled|string',
            'education_id'    => 'filled|numeric|exists:educations,id',
            'email'           => 'email',
            'start_school_at' => 'filled|date',
            'hobby'           => 'string',
            'password'        => 'string'
        ];
    }

    public function messages()
    {
        return [
            'nickname.filled' => '提交的昵称不能为空',
            'sex.in' => '性别必需为1或2',
            'job.in' => '职业类别必需为1或2'
        ];

    }
}
