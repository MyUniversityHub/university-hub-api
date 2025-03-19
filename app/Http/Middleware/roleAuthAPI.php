<?php

namespace App\Http\Middleware;

use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class roleAuthAPI
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function __construct(
        public RoleRepositoryInterface $roleRepository
    ){}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('api')->user();
        $roleId = $user->getRoleId();

        $role = $this->roleRepository->find($roleId);
        if ($role->getStatus() == ROLE_STATUS_ACTIVE) {
            return $next($request);
        }
        return $this->errorResponse('Tài khoản này không có quyền sử dụng mục này!', Response::HTTP_FORBIDDEN, 'This API Unauthorized');
    }
}
