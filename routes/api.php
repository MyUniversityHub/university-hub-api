<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticateController;

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthenticateController::class, 'logout']);
    Route::get('/me', [AuthenticateController::class, 'me']);
});
Route::post('/login', [AuthenticateController::class, 'login'])->name('login');
Route::post('/register', [AuthenticateController::class, 'register']);

Route::group(['middleware' => ['auth:api', ROLE_PERMISSION_MIDDLEWARE]], function () {
    Route::group(['prefix' => 'admin'], function () {

    });
    Route::group(['prefix' => 'student'], function () {

    });
    Route::group(['prefix' => 'teacher'], function () {

    });

    Route::group(['prefix' => 'staff'], function () {

    });
});



