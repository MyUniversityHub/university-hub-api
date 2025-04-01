<?php

namespace App\Providers;

use App\Models\Classes;
use App\Models\Teacher;
use App\Models\User;
use App\Observers\UserObserver;
use App\Repositories\Contracts\ClassesRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\MajorRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ClassesRepositoryImpl;
use App\Repositories\Eloquent\DepartmentRepositoryImpl;
use App\Repositories\Eloquent\MajorRepositoryImpl;
use App\Repositories\Eloquent\RoleRepositoryImpl;
use App\Repositories\Eloquent\StudentRepositoryImpl;
use App\Repositories\Eloquent\TeacherRepositoryImpl;
use App\Repositories\Eloquent\UserRepositoryImpl;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    private function registerRepositories(): array
    {
        return [
            UserRepositoryInterface::class => UserRepositoryImpl::class,
            RoleRepositoryInterface::class => RoleRepositoryImpl::class,
            StudentRepositoryInterface::class => StudentRepositoryImpl::class,
            DepartmentRepositoryInterface::class => DepartmentRepositoryImpl::class,
            MajorRepositoryInterface::class => MajorRepositoryImpl::class,
            ClassesRepositoryInterface::class => ClassesRepositoryImpl::class,
            TeacherRepositoryInterface::class => TeacherRepositoryImpl::class
        ];
    }
    public function register(): void
    {
        //
        $this->bindClasses($this->registerRepositories());

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addMinutes(config('app.minute_token_expire')));  // Thời gian hết hạn của Access Token
        Passport::refreshTokensExpireIn(now()->addMinutes(config('app.minute_refresh_token_expire'))); // Thời gian hết hạn của Refresh Token
        Passport::personalAccessTokensExpireIn(now()->addDays(config('app.day_person_token_expire'))); // Thời gian hết hạn của Personal Access Token. Được sử dụng cho mục đích riêng, như kết nối API với bên thứ ba hoặc công cụ phát triển.
        Passport::enablePasswordGrant(); // Cho phép đăng nhập để lấy Token

        User::observe(UserObserver::class);
        RateLimiter::for('api', function ($request) {
            // Giới hạn số request
            return Limit::perMinute(LOGIN_RATE_LIMITED)->by($request->input('user_name'));
        });
    }

    private function bindClasses(array $classes): void
    {
        foreach ($classes as $interface => $implement) {
            $this->app->bind($interface, $implement);
        }
    }
}
