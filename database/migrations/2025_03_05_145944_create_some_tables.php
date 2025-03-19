<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
//        Schema::create('users', function (Blueprint $table) {
//            $table->id();
//            $table->string('name');
//            $table->string('email')->unique();
//            $table->string('password');
//            $table->string('role'); // Removed ENUM
//            $table->timestamps();
//        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên ngành
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade'); // Liên kết với bảng departments
            $table->boolean('active')->default(true); // Trạng thái kích hoạt
            $table->timestamps();
            $table->softDeletes(); // Hỗ trợ soft delete
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('major_id')->constrained('majors')->onDelete('cascade');
            $table->boolean('active')->default(true); // Trạng thái kích hoạt
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('student_code')->unique();
            $table->string('avatar')->nullable();
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable(); // Removed ENUM
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('lecturer_code')->unique();
            $table->string('avatar')->nullable();
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable(); // Removed ENUM
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->timestamps();
        });

//        Schema::create('subjects', function (Blueprint $table) {
//            $table->id();
//            $table->string('name');
//            $table->foreignId('department_id')->constrained('departments')->onDelete('set null');
//            $table->integer('credit'); // Số tín chỉ
//            $table->timestamps();
//        });

//        Schema::create('subject_registrations', function (Blueprint $table) {
//            $table->id();
//            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
//            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
//            $table->integer('semester'); // Học kỳ
//            $table->string('academic_year'); // Năm học
//            $table->timestamps();
//        });

//        Schema::create('schedules', function (Blueprint $table) {
//            $table->id();
//            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
//            $table->foreignId('lecturer_id')->nullable()->constrained('lecturers')->onDelete('set null');
//            $table->string('day_of_week'); // Ex: "Monday"
//            $table->json('lesson_periods'); // Ex: [1,2,3] - Tiết học
//            $table->string('room')->nullable();
//            $table->time('start_time');
//            $table->time('end_time');
//            $table->timestamps();
//        });

        // Điểm sinh viên
//        Schema::create('student_grades', function (Blueprint $table) {
//            $table->id();
//            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
//            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
//            $table->float('midterm_score')->default(0);
//            $table->float('final_score')->default(0);
//            $table->float('total_score')->default(0);
//            $table->timestamps();
//        });

      //  Thông báo
//        Schema::create('announcements', function (Blueprint $table) {
//            $table->id();
//            $table->string('title');
//            $table->text('content');
//            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
//            $table->timestamps();
//        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('student_grades');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('subject_registrations');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('lecturers');
        Schema::dropIfExists('students');
        Schema::dropIfExists('users');
    }
};
