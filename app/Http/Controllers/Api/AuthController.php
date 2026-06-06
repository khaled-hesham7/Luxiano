<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    // 1. تسجيل حساب جديد (Register)
    public function register(RegisterRequest $request)
    {
        // الداتا هنا جاية بعد ما اتعملها Validate تلقائياً
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'customer',
        ]);

        $token = $user->createToken('luxiano_auth_token')->plainTextToken;

        // دمج الكارت للضيف
        $this->mergeGuestCart($request, $user);

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => new UserResource($user),
            'token'   => $token,
        ], 201);
    }

    // 2. تسجيل الدخول (Login)
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $token = $user->createToken('luxiano_auth_token')->plainTextToken;

        // دمج الكارت للضيف
        $this->mergeGuestCart($request, $user);

        return response()->json([
            'message' => 'Logged in successfully',
            'user'    => new UserResource($user),
            'token'   => $token,
        ], 200);
    }

    // 3. تسجيل الخروج (Logout)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    // 4. تعديل الملف الشخصي للعميل (Profile Update)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        $data = $request->only('name', 'email');

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'تم تحديث بيانات ملفك الشخصي بنجاح',
            'user'    => new UserResource($user),
        ], 200);
    }

    /**
     * دمج سلة تسوق الضيف مع سلة المستخدم المسجل بعد تسجيل الدخول.
     */
    private function mergeGuestCart(Request $request, $user)
    {
        $guestCartKey = "luxiano_cart_" . $request->ip();
        $guestCart = Cache::get($guestCartKey, []);

        if (!empty($guestCart)) {
            $userCartKey = "luxiano_cart_" . $user->id;
            $userCart = Cache::get($userCartKey, []);

            foreach ($guestCart as $variantId => $quantity) {
                if (isset($userCart[$variantId])) {
                    $userCart[$variantId] += $quantity;
                } else {
                    $userCart[$variantId] = $quantity;
                }
            }

            Cache::put($userCartKey, $userCart, now()->addDays(1));
            Cache::forget($guestCartKey); // مسح كاش سلة الضيف بعد النقل
        }
    }
}
