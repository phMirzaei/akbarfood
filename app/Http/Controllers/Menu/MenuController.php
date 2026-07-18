<?php

namespace App\Http\Controllers\Menu;

use App\DTOs\AddMenuItem;
use App\DTOs\UpdateMenuItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\MenuItemRequest;
use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;
use App\Services\AddMenuItemService;
use App\Services\RemoveMenuItemService;
use App\Services\UpdateMenuItemService;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function addMenuItems(MenuItemRequest $request, AddMenuItemService $menuService, Restaurant $restaurant): JsonResponse
    {
        $menuItem=new AddMenuItem(
            name: $request->validated('name'),
            description: $request->validated('description'),
            category: $request->validated('category'),
            image: $request->validated('image'),
            is_available: $request->validated('is_available'),
            price: $request->validated('price'),
        );
        $menuService->execute($menuItem,$restaurant);
        return response()->json([
            'message'=>'آیتم با موفقیت اضافه شد .'
        ],201);
    }

    public function listMenuItems(Restaurant $restaurant): JsonResponse
    {
        return response()->json([
            'نام رستوران:'=>$restaurant->name,
            'منو:'=>$restaurant->menuItems,
        ]);
    }

    public function updateMenuItems(MenuItemRequest $request,UpdateMenuItemService $updateMenuItemService,Restaurant $restaurant,Menu $menuItem): JsonResponse
    {
        $updateMenuItem=new UpdateMenuItem(
            name: $request->validated('name'),
            description: $request->validated('description'),
            category: $request->validated('category'),
            image: $request->validated('image'),
            is_available: $request->validated('is_available'),
            price: $request->validated('price'),
        );
        $updateMenuItemService->execute($updateMenuItem,$restaurant,$menuItem);
        return response()->json([
            'message'=>'آیتم با موفقیت آپدیت شد.'
        ]);
    }

    public function removeMenuItems(Restaurant $restaurant,Menu $menuItem,RemoveMenuItemService $removeMenuItemService):JsonResponse
    {
        $removeMenuItemService->execute($menuItem,$restaurant);
        return response()->json([
            'message'=>'آیتم با موفقیت حذف شد.'
        ]);

    }
}
