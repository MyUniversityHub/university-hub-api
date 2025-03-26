<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MajorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticateController;

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthenticateController::class, 'logout']);
    Route::get('/me', [AuthenticateController::class, 'me']);
    Route::post('/refresh', [AuthenticateController::class, 'refreshToken']);
});
Route::post('/login', [AuthenticateController::class, 'login'])->name('login');
Route::post('/register', [AuthenticateController::class, 'register']);

Route::group(['middleware' => ['auth:api', ROLE_PERMISSION_MIDDLEWARE]], function () {
    Route::group(['prefix' => 'admin'], function () {
        Route::group(['prefix' => 'departments'], function () {
            Route::get('/', [DepartmentController::class, 'index']);
            Route::get('/active', [DepartmentController::class, 'getDepartmentsActive']);
            Route::put('/{id}/update-status', [DepartmentController::class, 'updateActive']);
            Route::post('/', [DepartmentController::class, 'create']);
            Route::put('/{id}', [DepartmentController::class, 'update']);
            Route::delete('/bulk-delete', [DepartmentController::class, 'bulkDelete']);
        });
        Route::group(['prefix' => 'majors'], function () {
            Route::get('/', [MajorController::class, 'index']);
            Route::put('/{id}/update-status', [MajorController::class, 'updateActive']);
            Route::post('/', [MajorController::class, 'create']);
            Route::put('/{id}', [MajorController::class, 'update']);
            Route::delete('/bulk-delete', [MajorController::class, 'bulkDelete']);
        });
    });
    Route::group(['prefix' => 'student'], function () {

    });
    Route::group(['prefix' => 'teacher'], function () {

    });

    Route::group(['prefix' => 'staff'], function () {

    });
});



