<?php

use App\Http\Controllers\ClassesController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CourseClassController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
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
Route::put('/change-password', [AuthenticateController::class, 'changePassword']);

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
            Route::get('/student', [StudentController::class, 'getStudentWithUserInfo']);
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
        Route::prefix('courses')->group(function () {
            Route::get('/', [CourseController::class, 'index']); // List all courses
            Route::get('/active', [CourseController::class, 'getCourseActive']); // List all courses
            Route::post('/', [CourseController::class, 'create']); // Create a new course
            Route::put('/{id}', [CourseController::class, 'update']); // Update a course
            Route::put('/{id}/update-active', [CourseController::class, 'updateActive']); // Update a course
            Route::delete('/', [CourseController::class, 'bulkDelete']); // Bulk delete courses
        });
        Route::group(['prefix' => 'classrooms'], function () {
            Route::get('/', [ClassroomController::class, 'index']); // List all classrooms
            Route::get('/active', [ClassroomController::class, 'getClassroomActive']);
            Route::post('/', [ClassroomController::class, 'create']); // Create a new classroom
            Route::put('/{id}', [ClassroomController::class, 'update']); // Update a classroom
            Route::put('/{id}/update-active', [ClassroomController::class, 'updateActive']); // Update a course
            Route::delete('/bulk-delete', [ClassroomController::class, 'bulkDelete']); // Bulk delete classrooms
        });
        Route::group(['prefix' => 'course-classes'], function () {
            Route::get('/', [CourseClassController::class, 'index']); // List all course classes
            Route::post('/', [CourseClassController::class, 'create']); // Create a new course class
            Route::put('/{id}', [CourseClassController::class, 'update']); // Update a course class
            Route::delete('/bulk-delete', [CourseClassController::class, 'bulkDelete']); // Bulk delete course classes
        });
        Route::group(['prefix' => 'teachers'], function () {
            Route::get('/active', [TeacherController::class, 'getTeacherActive']);
        });
    });
    Route::group(['prefix' => 'student'], function () {
        Route::get('/', [StudentController::class, 'getStudentWithUserInfo']);
        Route::get('/{id}', [StudentController::class, 'getStudentWithUserInfoById']);
        Route::put('/{id}', [StudentController::class, 'update']);
        Route::post('/register-course', [StudentController::class, 'registerCourse']);
        Route::post('/upload-image', [StudentController::class, 'uploadImage']);

    });
    Route::group(['prefix' => 'teacher'], function () {
        Route::get('/active', [TeacherController::class, 'getTeacherActive']);
    });

    Route::group(['prefix' => 'staff'], function () {

    });
});



