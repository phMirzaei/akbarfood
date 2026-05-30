<?php

namespace App\Http\Controllers\Auth\Restaurant;

use Illuminate\Support\Facades\DB;
use App\Exceptions\OtpBlockedException;
use App\Exceptions\OtpExpiredException;
use App\Exceptions\OtpNotFoundException;
use App\Exceptions\OtpTooManyAttemptsException;
use App\Exceptions\OtpTooManyRequestException;
use App\Exceptions\UserAlreadyExistsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\Restaurant\Restaurant;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class AuthController extends Controller
{
    public function sendRegistrationVerification(RegisterRequest $request,OtpService $otpService): JsonResponse
    {
        $validated=$request->validated();

        $path=$validated['permit_scan']->store('permits','public');
        $validated['permit_scan']=$path;

        try {
            $managerExists=Restaurant::where('phone',$validated['phone'])->exists();
            if($managerExists){
                return response()->json([
                    'message' => 'این شماره قبلا ثبت شده است.'
                ], 409);
            }

            $payload = [
                'name' => $validated['name'],
                'permit_scan' => $path,
                'landline_number' => $validated['landline_number'],
                'city' => $validated['city'],
                'street' => $validated['street'],
                'alley' => $validated['alley'],
                'management_full_name' => $validated['management_full_name'],
                'phone' => $validated['phone'],
            ];


            $otpService->send($validated['phone'],$payload);
            return response()->json([
                'message' => 'کد تایید ارسال شد',
            ], 200);
        }
        catch (OtpBlockedException $e) {
            \Storage::disk('public')->delete($path);
            return response()->json([
                'message' => 'به دلیل تلاش‌های ناموفق، تا ۱۲ ساعت مسدود هستید.',
            ], 403);
        }
        catch (OtpTooManyRequestException $e) {
            \Storage::disk('public')->delete($path);
            return response()->json([
                'message' => 'لطفاً 1 دقیقه صبر کنید و دوباره تلاش کنید.',
            ], 429);
        }

        catch (\Throwable $e){
            \Storage::disk('public')->delete($path);
            return response()->json([
                'message' => 'ارسال کد با مشکل مواجه شد. لطفا دوباره تلاش کنید.',
            ], 500);
        }
    }

    public function verifyRestaurantRegistrationOtp(VerifyOtpRequest $request,OtpService $otpService)
    {
        $validated=$request->validated();
        try {
            $otp=$otpService->verify($validated['phone'],$validated['code']);
            $payload = $otp->payload;
            $restaurant=DB::transaction (function() use ($otp,$payload){
                $restaurant = Restaurant::firstOrCreate(
                    ['phone' => $otp->phone],
                    [
                        'name' => $payload['name'],
                        'permit_scan' => $payload['permit_scan'],
                        'landline_number' => $payload['landline_number'],
                        'city' => $payload['city'],
                        'street' => $payload['street'],
                        'alley' => $payload['alley'],
                        'management_full_name' => $payload['management_full_name'],
                        'phone' => $payload['phone'],
                    ]
                );
                $otp->delete();
                return $restaurant;
            });
            $token = auth('restaurant')->login($restaurant);
            return response()->json([
                'message' => 'ثبت نام شما با موفقیت انجام شد.',
                'token' => $token,
            ]);
        }catch (OtpNotFoundException $e) {
            return response()->json([
                'message'=> 'کد وارد شده صحیح نیست.',
            ], 422);
        } catch (OtpExpiredException $e) {
            return response()->json([
                'message' => 'کد تایید منقضی شده است. لطفاً کد جدید درخواست دهید.'
            ], 410);
        } catch (OtpBlockedException $e) {
            return response()->json([
                'message' => 'به دلیل تلاش‌های ناموفق، تا ۱۲ ساعت مسدود هستید.'
            ], 403);
        } catch (OtpTooManyAttemptsException $e) {
            return response()->json([
                'message' => 'تعداد دفعات مجاز به پایان رسید. به مدت 12 ساعت بلاک شدید.'
            ], 403);
        }
        catch (\Throwable $e) {

            report($e);

            return response()->json([
                'message' => 'خطای سرور.'
            ], 500);
        }
    }
}
