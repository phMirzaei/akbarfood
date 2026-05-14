<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): arrayg
    {
        return [
            'name' => 'required|string|min:3|max:100|regex:/^[\p{Arabic}\s]+$/u',
            'phone' => 'required|digits:11|regex:/^09\d{9}$/'
        ];

    }
}
