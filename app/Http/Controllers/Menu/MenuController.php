<?php

namespace App\Http\Controllers\Menu;

use App\DTOs\AddMenuItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\MenuItemRequest;
use App\Models\Restaurant\Restaurant;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function addMenuItems(MenuItemRequest $request,MenuService $menuService,Restaurant $restaurant): JsonResponse
    {
        $path=$request->file('image')->store('itemsPic','public');
        $menuItem=new AddMenuItem(
            name: $request->validated('name'),
            description: $request->validated('description'),
            category: $request->validated('category'),
            image: $path,
            is_available: $request->validated('is_available'),
            price: $request->validated('price'),
        );
        $menuService->addItems($menuItem,$restaurant);
        return response()->json([
            'message'=>'آیتم با موفقیت اضافه شد .'
        ],200);
    }
}
