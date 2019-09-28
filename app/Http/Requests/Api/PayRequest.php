<?php

namespace App\Http\Requests\Api;

use Dingo\Api\Http\FormRequest;

class PayRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'expense' => [
                'required',
                'numeric',
                'gt:0',
                'regex:/^[0-9]+(.[0-9]{1,2})?$/']
        ];
    }

    public function messages()
    {
        return [
            'expense.regex' => '支付金额小数不能超过2位',
        ];
    }
}
