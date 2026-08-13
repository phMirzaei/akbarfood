<?php

namespace App\Http\Requests\Menu;

use App\Enums\MenuCategory;
use App\Models\Menu\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
                    ->where('restaurant_id', $this->route('restaurant')->id)
                    ->ignore($this->route('menuItem')->id),
            ],
            'description' => 'required|string|min:8|max:100|regex:/^[\p{Arabic}\s\x{200C}\-]+$/u',
            'category' => ['required', new Enum(MenuCategory::class)],
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_available' => 'required|boolean',
            'price' => 'required|integer|min:10000|max:5000000',

        ];
    }
}
