<?php

use App\Http\Controllers\ClassesController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CloudinaryUploadController;
use App\Http\Controllers\CourseClassController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CurriculumProgramController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RegistrationFeeDetailController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentCourseResultController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticateController;

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthenticateController::class, 'logout']);
    Route::get('/me', [AuthenticateController::class, 'me']);
    Route::post('/refresh', [AuthenticateController::class, 'refreshToken']);
    Route::get('/notifications', [UserController::class, 'getNotifications']);
    Route::put('/notifications/read', [UserController::class, 'updateStatusNotification']);
});
Route::post('/upload-image', [CloudinaryUploadController::class, 'uploadImage']);
Route::post('/login', [AuthenticateController::class, 'login'])->name('login');
Route::post('/register', [AuthenticateController::class, 'register']);
Route::put('/change-password', [AuthenticateController::class, 'changePassword']);
Route::post('/payment/momo/callback', [StudentController::class, 'handleMomoCallback'])->name('payment.momo.callback');

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
            Route::get('/export-excel', [DepartmentController::class, 'exportExcel']);
            Route::post('/import-excel', [DepartmentController::class, 'importExcel']);
        });
        Route::group(['prefix' => 'majors'], function () {
            Route::get('/', [MajorController::class, 'index']);
            Route::get('/active', [MajorController::class, 'getMajorsActive']);
            Route::get('/department/{id}', [MajorController::class, 'getMajorsByDepartment']);
            Route::put('/{id}/update-status', [MajorController::class, 'updateActive']);
            Route::post('/', [MajorController::class, 'create']);
            Route::put('/{id}', [MajorController::class, 'update']);
            Route::delete('/bulk-delete', [MajorController::class, 'bulkDelete']);
            Route::get('/export-excel', [MajorController::class, 'exportExcel']);
            Route::post('/import-excel', [MajorController::class, 'importExcel']);
        });
        Route::group(['prefix' => 'classes'], function () {
            Route::get('/', [ClassesController::class, 'index']);
            Route::get('/active', [ClassesController::class, 'getClassesActive']);
            Route::get('/major/{id}', [ClassesController::class, 'getClassesByMajor']);
            Route::put('/{id}/update-status', [ClassesController::class, 'updateActive']);
            Route::post('/', [ClassesController::class, 'create']);
            Route::put('/{id}', [ClassesController::class, 'update']);
            Route::delete('/bulk-delete', [ClassesController::class, 'bulkDelete']);
            Route::get('/export-excel', [ClassesController::class, 'exportExcel']);
            Route::post('/import-excel', [ClassesController::class, 'importExcel']);
        });
        Route::group(['prefix' => 'users'], function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/student', [StudentController::class, 'getStudentWithUserInfo']);
            Route::get('/teacher', [TeacherController::class, 'getTeacherWithUserInfo']);
            Route::get('/admin', [UserController::class, 'getAdmins']);
            Route::post('/', [UserController::class, 'create']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::put('/{id}/reset-password', [UserController::class, 'resetPassword']);
            Route::put('/{id}/update-status', [UserController::class, 'updateActive']);
            Route::delete('/bulk-delete', [UserController::class, 'bulkDelete']);
            Route::get('/student/export-excel', [StudentController::class, 'exportExcel']);
            Route::post('/student/import-excel', [StudentController::class, 'importExcel']);
            Route::get('/teacher/export-excel', [TeacherController::class, 'exportExcel']);
            Route::post('/teacher/import-excel', [TeacherController::class, 'importExcel']);

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
            Route::get('/export-excel', [CourseController::class, 'exportExcel']);
            Route::post('/import-excel', [CourseController::class, 'importExcel']);
        });
        Route::group(['prefix' => 'classrooms'], function () {
            Route::get('/', [ClassroomController::class, 'index']); // List all classrooms
            Route::get('/active', [ClassroomController::class, 'getClassroomActive']);
            Route::post('/', [ClassroomController::class, 'create']); // Create a new classroom
            Route::put('/{id}', [ClassroomController::class, 'update']); // Update a classroom
            Route::put('/{id}/update-active', [ClassroomController::class, 'updateActive']); // Update a course
            Route::delete('/bulk-delete', [ClassroomController::class, 'bulkDelete']); // Bulk delete classrooms
            Route::get('/export-excel', [ClassroomController::class, 'exportExcel']);
            Route::post('/import-excel', [ClassroomController::class, 'importExcel']);
        });
        Route::group(['prefix' => 'course-classes'], function () {
            Route::get('/', [CourseClassController::class, 'index']); // List all course classes
            Route::post('/', [CourseClassController::class, 'create']); // Create a new course class
            Route::post('/bulk-create', [CourseClassController::class, 'bulkCreate']); // Create a new course class
            Route::put('/{id}', [CourseClassController::class, 'update']); // Update a course class
            Route::put('/open-course-class/{id}', [CourseClassController::class, 'openCourseClass']);
            Route::put('/close-course-class/{id}', [CourseClassController::class, 'closeCourseClass']);
            Route::put('/assign-teacher/{id}', [CourseClassController::class, 'assignTeacher']); // Update a course class
            Route::delete('/bulk-delete', [CourseClassController::class, 'bulkDelete']); // Bulk delete course classes
        });
        Route::group(['prefix' => 'teachers'], function () {
            Route::get('/active', [TeacherController::class, 'getTeacherActive']);
        });
        Route::prefix('curriculum-programs')->group(function () {
            Route::get('/', [CurriculumProgramController::class, 'index']);
            Route::get('/major/{id}', [CurriculumProgramController::class, 'getCurriculumProgramByMajor']);
            Route::post('/', [CurriculumProgramController::class, 'create']);
            Route::put('/{id}', [CurriculumProgramController::class, 'update']);
            Route::delete('/', [CurriculumProgramController::class, 'delete']);
            Route::delete('/bulk-delete', [CurriculumProgramController::class, 'bulkDelete']);
        });
        Route::prefix('statistics')->group(function () {
            Route::get('/', [StatisticController::class, 'index']);
        });
    });
    Route::group(['prefix' => 'student', 'middleware' => [ROLE_MIDDLEWARE . ':' . ROLE_STUDENT]], function () {
        Route::get('/info', [StudentController::class, 'getStudentWithUserInfoById']);
        Route::put('/update-info/{id}', [StudentController::class, 'update']);
        Route::post('/register-course', [StudentController::class, 'registerCourse']);
        Route::delete('/unregister-course/{id}', [StudentController::class, 'unregisterCourse']);
        Route::post('/upload-image', [StudentController::class, 'uploadImage']);
        Route::get('/class-schedule', [StudentCourseResultController::class, 'getClassSchedule']);
        Route::post('/generate-momo-qr', [StudentController::class, 'createMomoPayment']);
        Route::get('/average-score', [StudentController::class, 'getAverageScore']);
        // routes/web.php
        Route::get('/payment/momo/return', [StudentController::class, 'handleMomoReturn'])
            ->name('payment.momo.return');

// routes/api.php

        Route::group(['prefix' => 'course-classes'], function () {
            Route::get('/', [CourseClassController::class, 'getCourseClassesForStudent']); // List all course classes
            Route::get('/{id}', [CourseClassController::class, 'getClassesByCourseAndMajor']); // List all course classes
        });
        Route::group(['prefix' => 'courses'], function () {
            Route::get('/', [CourseController::class, 'getCoursesWithClassesByStudentMajor']); // List all course classes
        });
        Route::group(['prefix' => 'course-results'], function () {
            Route::get('/', [StudentCourseResultController::class, 'index']); // List all course classes
            Route::get('/{id}/status', [StudentCourseResultController::class, 'getCourseResultByStatus']);
        });
        Route::group(['prefix' => 'registration-fee-detail'], function () {
            Route::get('/', [RegistrationFeeDetailController::class, 'index']); // List all course classes
            Route::get('/{status}/status', [RegistrationFeeDetailController::class, 'getRegistrationFeeDetailByStatus']); // List all course classes
            Route::put('/payTuitionFee', [RegistrationFeeDetailController::class, 'payTuitionFee']);
        });
        Route::group(['prefix' => 'payments'], function () {
            Route::get('/', [PaymentController::class, 'index']);
        });
        Route::prefix('curriculum-programs')->group(function () {
            Route::get('/major/{id}', [CurriculumProgramController::class, 'getCurriculumProgramByMajor']);
        });
    });
    Route::group(['prefix' => 'teacher'], function () {
        Route::get('/info', [TeacherController::class, 'getTeacherWithUserInfoById']);
        Route::get('/active', [TeacherController::class, 'getTeacherActive']);
        Route::get('/teaching-schedule', [CourseClassController::class, 'getTeachingSchedule']);
        Route::group(['prefix' => 'course-classes'], function () {
            Route::get('/', [CourseClassController::class, 'getCourseClassesForTeacher']); // List all course classes// List all course classes
            Route::get('/{id}/students', [CourseClassController::class, 'getStudentCourseResultByCourseClassId']); // List all course classes
            Route::post('/{id}/update-scores', [StudentCourseResultController::class, 'updateScoresForStudents']);
        });
        Route::group(['prefix' => 'majors'], function () {
            Route::get('/active', [MajorController::class, 'getMajorsActive']);
        });
        Route::prefix('courses')->group(function () {
            Route::get('/active', [CourseController::class, 'getCourseActive']); // List all courses
        });
        Route::group(['prefix' => 'classrooms'], function () {
            Route::get('/active', [ClassroomController::class, 'getClassroomActive']);
        });
    });

    Route::group(['prefix' => 'staff'], function () {

    });
});
