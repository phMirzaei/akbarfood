<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' =>'required|string|min:3|max:50|regex:/^[\p{Arabic}\s\x{200C}\-]+$/u',
            'permit_scan'=>'required|file|mimes:jpeg,jpg,png,pdf|max:2048',
            'landline_number'=>'required|digits:8|regex:/^55\d{6}$/|unique:restaurants,landline_number',
            'city'=>'required|string|min:2|max:19|regex:/^[\p{Arabic}\s\x{200C}\-]+$/u',
            'street'=>'required|string|min:2|max:30|regex:/^[\p{Arabic}\s\x{200C}\-]+$/u',
            'alley'=>'required|string|min:2|max:30|regex:/^[\p{Arabic}\s\x{200C}\-]+$/u',
            'management_full_name'=>'required|string|min:3|max:50|regex:/^[\p{Arabic}\s\x{200C}\-]+$/u',
            'management_phone'=>'required|digits:11|regex:/^09\d{9}$/',
        ];
    }
    public function messages(): array{
        return [
            'landline_number.unique' => 'این شماره قبلا ثبت نام شده است.',
        ];
    }
}
