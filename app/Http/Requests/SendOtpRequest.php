<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:100|regex:/^[\p{Arabic}\s\x{200C}\-]+$/u',
            'phone' => 'required|digits:11|regex:/^09\d{9}$/',
        ];

    }
}
