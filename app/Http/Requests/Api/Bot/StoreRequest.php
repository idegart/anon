<?php

namespace App\Http\Requests\Api\Bot;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'token' => [
                'required',
                'string',
                'regex:/[0-9]{9}:[a-zA-Z0-9_-]{35}/',
            ],
        ];
    }

    public function getToken()
    {
        return $this->input('token');
    }
}
