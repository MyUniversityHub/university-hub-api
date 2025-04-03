<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Lấy user đang đăng nhập
        $user = auth()->user();

        // Kiểm tra nếu user có role hợp lệ
        if ($user && in_array($user->role_id, $roles)) {
            return $next($request);
        }

        // Nếu không có quyền, trả về lỗi 403
        return response()->json(['message' => 'Bạn không có quyền truy cập!'], 403);
    }
}
