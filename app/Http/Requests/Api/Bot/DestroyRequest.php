<?php

namespace App\Http\Requests\Api\Bot;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            recaptchaFieldName() => [
                'required',
                'string',
                recaptchaRuleName(),
            ],
        ];
    }
}
