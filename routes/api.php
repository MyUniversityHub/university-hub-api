<?php

use App\Http\Controllers\ClassesController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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
    Route::group(['prefix' => 'admin', 'middleware' => [ROLE_MIDDLEWARE . ':' . ROLE_ADMIN]], function () {
        Route::put('/change-password', [AuthenticateController::class, 'changePassword']);
        Route::put('/update-profile', [AuthenticateController::class, 'updateProfile']);
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
            Route::get('/active', [MajorController::class, 'getMajorsActive']);
            Route::get('/department/{id}', [MajorController::class, 'getMajorsByDepartment']);
            Route::put('/{id}/update-status', [MajorController::class, 'updateActive']);
            Route::post('/', [MajorController::class, 'create']);
            Route::put('/{id}', [MajorController::class, 'update']);
            Route::delete('/bulk-delete', [MajorController::class, 'bulkDelete']);
        });
        Route::group(['prefix' => 'classes'], function () {
            Route::get('/', [ClassesController::class, 'index']);
            Route::get('/active', [ClassesController::class, 'getClassesActive']);
            Route::get('/major/{id}', [ClassesController::class, 'getClassesByMajor']);
            Route::put('/{id}/update-status', [ClassesController::class, 'updateActive']);
            Route::post('/', [ClassesController::class, 'create']);
            Route::put('/{id}', [ClassesController::class, 'update']);
            Route::delete('/bulk-delete', [ClassesController::class, 'bulkDelete']);
        });
        Route::group(['prefix' => 'users'], function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/student', [UserController::class, 'getStudents']);
            Route::get('/teacher', [UserController::class, 'getTeachers']);
            Route::get('/admin', [UserController::class, 'getAdmins']);
            Route::post('/', [UserController::class, 'create']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::put('/{id}/reset-password', [UserController::class, 'resetPassword']);
            Route::put('/{id}/update-status', [UserController::class, 'updateActive']);
            Route::delete('/bulk-delete', [UserController::class, 'bulkDelete']);

        });
        Route::group(['prefix' => 'roles'], function () {
            Route::get('/active', [RoleController::class, 'getRolesActive']);
        });
    });
    Route::group(['prefix' => 'student'], function () {

    });
    Route::group(['prefix' => 'teacher'], function () {

    });

    Route::group(['prefix' => 'staff'], function () {

    });
});



