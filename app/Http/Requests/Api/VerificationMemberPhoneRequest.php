<?php

namespace App\Http\Requests\Api;

use Dingo\Api\Http\FormRequest;

class VerificationMemberPhoneRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phone' => 'exists:members'
        ];
    }
}
