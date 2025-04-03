<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Student;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateController extends Controller
{
    //
    use ApiResponse;
    public function __construct(
        public UserRepositoryInterface $userRepository,
        public RoleRepositoryInterface $roleRepository,
        public StudentRepositoryInterface $studentRepository
    )
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userData = $request->all();
            $userData['password'] = Hash::make($userData['password']);
            $userData['active'] = 1;
            $user = $this->userRepository->create($userData);
            if (!$user) {
                return $this->errorResponse('Đăng ký tài khoản thất bại!', Response::HTTP_UNAUTHORIZED, 'Error register');
            }
            if($user->role_id === 'STUDENT') {
                $this->studentRepository->create([
                    'user_id' => $user->id,

                ]);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Đăng ký tài khoản thất bại!', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($user, 'Tài khoản đã được đăng ký thành công!');
    }

    public function login(LoginRequest $request)
    {
        $userName = $request->input('user_name');
        $password = $request->input('password');

        if (!Auth::attempt(['user_name' => $userName, 'password' => $password])) {
            return $this->errorResponse('Đăng nhâp thất bại!', Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        return $this->addTokenLogin($userName, $password);
    }

    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();
        return $this->successResponse('', 'Đăng xuất thành công!', Response::HTTP_NO_CONTENT);
    }

    public function addTokenLogin($userName, $password): JsonResponse
    {
        $user = $this->userRepository->findBy('user_name', $userName);

        if($user) {
            $role = $this->roleRepository->find($user->role_id);
            if ($role->active == ROLE_STATUS_DEACTIVATE) {
                return $this->errorResponse('Tài khoản không hợp lệ!', Response::HTTP_UNAUTHORIZED);
            }
        }
        $response = passportResponse($userName, $password);
        if ($response->failed()) {
            return $this->errorResponse('Đăng nhâp thất bại!', Response::HTTP_UNAUTHORIZED, $response->json());
        }

        $userToken = $response->json();
//        if (!Cache::has(ROLE_PERMISSION_CACHE_KEY)) {
//            $allPermission = $this->rolePermissionRepository->allPermissions();
//            Cache::put(ROLE_PERMISSION_CACHE_KEY, jsonResponse($allPermission), now()->addDays(PERMISSION_CACHE_TIME));   // cache theo ngày
//        }

        // Xóa rate limit sau khi đăng nhập thành công
        RateLimiter::clear($userName);
        return $this->successResponse($userToken, 'Đăng nhập thành công!');
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user();
        $roleId = $user->getRoleId();

//        Cache::forget(ROLE_PERMISSION_CACHE_KEY);
//        if (!Cache::has(ROLE_PERMISSION_CACHE_KEY)) {
//            $allPermission = $this->rolePermissionRepository->allPermissions();
//            Cache::put(ROLE_PERMISSION_CACHE_KEY, jsonResponse($allPermission), now()->addDays(PERMISSION_CACHE_TIME));   // cache theo ngày
//        }

        $role = $this->roleRepository->find($roleId);
        return $this->successResponse(["info" => $user, "role" => $role], 'Authenticated use info');
    }

    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');
        $response = passportResponse(null, null, 'refresh_token', $refreshToken);
        if ($response->failed()) {
            return $this->errorResponse('Refreshed token Failed.', Response::HTTP_UNAUTHORIZED, $response->json());
        }
        return $this->successResponse($response->json(), 'Refreshed token.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {

        $user = auth('api')->user();
        $currentPassword = $request->input('current_password');
        $newPassword = $request->input('new_password');
        // Check if the current password matches
        if (!Hash::check($currentPassword, $user->password)) {
            return $this->errorResponse('Mật khẩu hiện tại không chính xác!', Response::HTTP_UNAUTHORIZED, 'Error change password');
        }

        // Update password
        try {
            $this->userRepository->update($user->id, ['password' => Hash::make($newPassword)]);
        } catch (\Exception $exception) {
            return $this->errorResponse('Thay đổi mật khẩu thất bại!', Response::HTTP_BAD_REQUEST, $exception->getMessage());
        }

        return $this->successResponse('', 'Thay đổi mật khẩu thành công!');
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        try {
            $user = $this->userRepository->update($user->id, $request->validated());
            return $this->successResponse($user, 'Thay đổi thông tin tài khoản thành công!');
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }

    }

}
