<?php

namespace App\Http\Controllers\Menu;

use App\DTOs\AddMenuItem;
use App\DTOs\RemoveMenuItem;
use App\DTOs\UpdateMenuItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\MenuItemRequest;
use App\Http\Requests\Menu\UpdateMenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;
use App\Services\AddMenuItemService;
use App\Services\RemoveMenuItemService;
use App\Services\UpdateMenuItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuController extends Controller
{
    public function addMenuItems(MenuItemRequest $request, AddMenuItemService $menuService, Restaurant $restaurant): JsonResponse
    {
        $imagePath = $request->hasFile('image') ? $request->file('image')->store('itemsPic', 'public') : null;
        $menuItem = new AddMenuItem(
            actorId: auth()->id(),
            restaurantId: $restaurant->id,
            name: $request->validated('name'),
            description: $request->validated('description'),
            category: $request->validated('category'),
            imagePath: $imagePath,
            is_available: $request->validated('is_available'),
            price: $request->validated('price'),
        );
        $menuService->execute($menuItem);

        return response()->json([
            'message' => 'آیتم با موفقیت اضافه شد .',
        ], 201);
    }

    public function listMenuItems(Restaurant $restaurant): AnonymousResourceCollection
    {
        return MenuItemResource::collection(
            $restaurant->menuItems()->where('is_available', true)->paginate(10));
    }

    public function updateMenuItems(UpdateMenuItemRequest $request, UpdateMenuItemService $updateMenuItemService, Restaurant $restaurant, Menu $menuItem): JsonResponse
    {
        $imagePath = $request->hasFile('image') ? $request->file('image')->store('itemPic', 'public') : null;
        $updateMenuItem = new UpdateMenuItem(
            actorId: auth()->id(),
            restaurantId: $restaurant->id,
            menuId: $menuItem->id,
            name: $request->validated('name'),
            description: $request->validated('description'),
            category: $request->validated('category'),
            imagePath: $imagePath,
            is_available: $request->validated('is_available'),
            price: $request->validated('price'),
        );
        $updateMenuItemService->execute($updateMenuItem);

        return response()->json([
            'message' => 'آیتم با موفقیت آپدیت شد.',
        ]);
    }

    public function removeMenuItems(Restaurant $restaurant, Menu $menuItem, RemoveMenuItemService $removeMenuItemService): JsonResponse
    {
        $removeMenuItem = new RemoveMenuItem(
            actorId: auth()->id(),
            menuId: $menuItem->id,
            restaurantId: $restaurant->id
        );
        $removeMenuItemService->execute($removeMenuItem);

        return response()->json([
            'message' => 'آیتم با موفقیت حذف شد.',
        ]);

    }
}
