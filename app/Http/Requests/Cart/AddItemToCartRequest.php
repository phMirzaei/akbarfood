<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddItemToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_id' =>[
                'required',
                'integer',
                Rule::exists('menus', 'id')->
                    where('restaurant_id',$this->route('restaurant')->id),
            ]
            'quantity' => 'required|integer|min:1',
        ];
    }
}
