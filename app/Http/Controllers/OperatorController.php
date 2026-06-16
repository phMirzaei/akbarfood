<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OperatorController extends Controller
{
    public function addOperator(Request $request, $userId)
    {
        try {
            if (auth()->user()->role !== 'admin') {
                return response()->json([
                    'message' => 'شما اجازه انجام این عملیات را ندارید.'
                ], 403);
            }
            $user = User::findOrFail($userId);
            $user->role = 'operator';
            $user->save();

            return response()->json([
                'message' => 'اوبراتور با موفقیت اضافه شد.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'کاربر یافت نشد.'
            ], 404);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'خطای سرور.'
            ], 500);

        }


    }

}
