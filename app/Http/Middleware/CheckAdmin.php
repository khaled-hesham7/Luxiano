<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // نتأكد إن العميل مسجل دخول الأول، وإن الـ role بتاعه بيساوي admin
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request); // أدمن؟ عدي يا باشا لوحة التحكم بتاعتك
        }

        // لو مش أدمن (مثلاً customer) اصعقه بـ 403 غير مصرح لك
        return response()->json([
            'message' => 'عفواً، لا تمتلك الصلاحيات الكافية لدخول لوحة التحكم الحساسة هذه.'
        ], 403);
    }
}
