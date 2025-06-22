<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Các URI không cần kiểm tra CSRF
     *
     * @var array
     */
    protected $except = [
        // Thêm các route callback payment vào đây
        'payment/momo/callback',
    ];
}
