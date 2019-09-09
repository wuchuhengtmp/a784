<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SocialAuthorizationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * 微信jwt授权参数验证
     *
     * @return array
     */
    public function rules()
    {
        if ($this->social_type  == 'weixin') {
            $rules = [
                'code' => 'required_without:access_token|string',
                /* 'access_token' => 'required_without:code|string', */
            ];
        }
        if ($this->social_type == 'code') {
            $rules = [
                'verification_key' => 'required|string',
                'verification_code' => 'required|string',
            ];
        }
        return $rules;
    }

}
