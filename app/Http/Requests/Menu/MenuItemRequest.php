<?php

namespace App\Http\Requests\Menu;

use App\Models\Menu\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[\p{Arabic}\s\x{200C}\-]+$/u',
                Rule::unique(Menu::class, 'name')
                    ->where('restaurant_id', $this->route('restaurant')->id),
            ],
            'description' => 'required|string|min:8|max:100|regex:/^[\p{Arabic}\s\x{200C}\-]+$/u',
            'category' => 'required|in:Drink,Iranian_food,Fast_food,Desert',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_available' => 'required|boolean',
            'price' => 'required|integer|min:10000|max:5000000',
        ];
    }
}
