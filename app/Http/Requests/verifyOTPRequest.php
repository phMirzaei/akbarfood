<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class verifyOTPRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'code' => 'required|digits:4',
            'phone' => 'required|digits:11|regex:/^09\d{9}$/',
            'name'=>'string|min:3|max:100|regex:/^[\p{Arabic}\s]+$/u',

        ];
    }
}
