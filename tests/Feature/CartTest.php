<?php

namespace Tests\Feature;

use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_separate_cart_for_each_restaurant(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'phone' => '0140026305',
        ]);

        $restaurantA = Restaurant::create([
            'name' => 'Restaurant',
            'permit_scan' => 'jjjj',
            'landline_number' => 5531633,
            'city' => 'ااااا',
            'street' => 'شسشسش',
            'alley' => 'سسسسس',
            'vendor_type' => 'cafe',
            'status' => 'approved',
        ]);
        $restaurantB = Restaurant::create([
            'name' => 'alizade',
            'permit_scan' => 'uuuuuuu/mm',
            'landline_number' => 5531643,
            'city' => 'ااااا',
            'street' => 'شسشسش',
            'alley' => 'سسسسس',
            'vendor_type' => 'cafe',
            'status' => 'approved',
        ]);
        $menuA = Menu::create([
            'name' => 'Menu',
            'description' => 'Menu description',
            'category' => 'Fast_food',
            'image' => 'menu.png',
            'is_available' => true,
            'price' => 100,
            'restaurant_id' => $restaurantA->id,
        ]);
        $menuB = Menu::create([
            'name' => 'Menu',
            'description' => 'Menu description',
            'category' => 'Fast_food',
            'image' => 'menu.png',
            'is_available' => true,
            'price' => 100,
            'restaurant_id' => $restaurantB->id,
        ]);

        $this->actingAs($user);

        $this->postJson(
            "api/v1/restaurants/{$restaurantA->id}/cart-items",
            [
                'menu_id' => $menuA->id,
                'quantity' => 1,
            ]
        );

        $this->postJson(
            "api/v1/restaurants/{$restaurantB->id}/cart-items",
            [
                'menu_id' => $menuB->id,
                'quantity' => 1,
            ]
        );

        $this->assertDatabaseCount('carts', 2);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'restaurant_id' => $restaurantA->id,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'restaurant_id' => $restaurantB->id,
        ]);
    }

    public function test_same_user_and_restaurant_use_same_cart(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'phone' => '0140026305',
        ]);

        $restaurant = Restaurant::create([
            'name' => 'Restaurant',
            'permit_scan' => 'jjjj',
            'landline_number' => 5531633,
            'city' => 'ااااا',
            'street' => 'شسشسش',
            'alley' => 'سسسسس',
            'vendor_type' => 'cafe',
            'status' => 'approved',
        ]);

        $menu = Menu::create([
            'name' => 'Menu',
            'description' => 'Menu description',
            'category' => 'Fast_food',
            'image' => 'menu.png',
            'is_available' => true,
            'price' => 100,
            'restaurant_id' => $restaurant->id,
        ]);

        $this->actingAs($user);

        $this->postJson(
            "api/v1/restaurants/{$restaurant->id}/cart-items",
            [
                'menu_id' => $menu->id,
                'quantity' => 1,
            ]
        );

        $this->postJson(
            "api/v1/restaurants/{$restaurant->id}/cart-items",
            [
                'menu_id' => $menu->id,
                'quantity' => 1,
            ]
        );

        $this->assertDatabaseCount('carts', 1);
    }
}
