<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->bigIncrements('department_id');
            $table->string('department_name');
            $table->string('department_code', 50)->nullable()->unique();
            $table->text('description');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('majors', function (Blueprint $table) {
            $table->bigIncrements('major_id');
            $table->string('major_name');
            $table->foreignId('department_id')->nullable()->constrained('departments', 'department_id')->onDelete('set null');
            $table->string('major_code', 50)->nullable()->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->bigIncrements('class_id');
            $table->string('class_name')->nullable()->unique();
            $table->foreignId('major_id')->nullable()->constrained('majors', 'major_id')->onDelete('set null');
            $table->string('course_year', 50);
            $table->string('advisor_name', 120);
            $table->integer('student_count')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('student_id');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('student_code')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->year('admission_year')->nullable();
            $table->string('gender')->nullable();
            $table->foreignId('class_id')->nullable()->constrained('classes', 'class_id')->onDelete('set null');
            $table->decimal('course_fee_debt', 12, 2)->default(0);
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->bigIncrements('teacher_id');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('teacher_code')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments', 'department_id')->onDelete('set null');
            $table->string('degree')->nullable();
            $table->string('specialization')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('course_id');
            $table->string('course_code', 50)->nullable()->unique();
            $table->string('course_name');
            $table->integer('credit_hours');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('classrooms', function (Blueprint $table) {
            $table->bigIncrements('classroom_id');
            $table->string('room_name')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_classes', function (Blueprint $table) {
            $table->bigIncrements('course_class_id');
            $table->string('course_class_code', 50)->nullable()->unique();
            $table->foreignId('course_id')->constrained('courses', 'course_id')->onDelete('cascade');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms', 'classroom_id')->onDelete('set null');
            $table->foreignId('teacher_id')->nullable()->constrained('teachers', 'teacher_id')->onDelete('set null');
            $table->foreignId('major_id')->nullable()->constrained('majors', 'major_id')->onDelete('set null');
            $table->json('weekdays')->nullable();
            $table->tinyInteger('semester')->nullable();
            $table->unsignedInteger('lesson_start')->default(0);
            $table->unsignedInteger('lesson_end')->default(0);
            $table->unsignedInteger('current_student_count')->default(0);
            $table->unsignedInteger('max_student_count')->nullable();
            $table->tinyInteger('status')->default(0); // 0: Chờ phân công giảng viên, 1: Sẵn sàng mở , 2: Đã mở, 3: Đã kết thúc
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

//        Schema::create('student_course_registrations', function (Blueprint $table) {
//            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
//            $table->foreignId('course_class_id')->constrained('course_classes', 'course_class_id')->onDelete('cascade');
//            $table->integer('status')->default(0);
//            $table->timestamps();
//            $table->softDeletes();
//
//            $table->primary(['student_id', 'course_class_id']);
//        });

        Schema::create('student_course_results', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->foreignId('course_class_id')->constrained('course_classes', 'course_class_id')->onDelete('cascade');

            $table->decimal('frequent_score_1', 2, 1)->nullable();
            $table->decimal('frequent_score_2', 2, 1)->nullable();
            $table->decimal('frequent_score_3', 2, 1)->nullable();
            $table->decimal('final_score', 2, 1)->nullable();
            $table->integer('absent_sessions')->default(0);
            $table->decimal('average_score', 5, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->boolean('status')->default(1); // 0: Trượt môn, 1: Đang học, 2: Đã học
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['student_id', 'course_class_id']);
        });

        Schema::create('registration_fee_details', function (Blueprint $table) {
            $table->foreignId('student_id');
            $table->foreignId('course_class_id');
            $table->string('fee_code');
            $table->string('fee_name');
            $table->integer('credit_count');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->integer('status')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['student_id', 'course_class_id', 'fee_code']);
            $table->foreign(['student_id', 'course_class_id'])
                ->references(['student_id', 'course_class_id'])
                ->on('student_course_results')
                ->onDelete('cascade');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('payment_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->dateTime('payment_date');
            $table->decimal('amount', 12, 2);
            $table->integer('payment_method')->default(1); // 1: cash, 2: bank transfer, 3: online payment
            $table->integer('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_prerequisites', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained('courses', 'course_id')->onDelete('cascade');
            $table->foreignId('prerequisite_course_id')->constrained('courses', 'course_id')->onDelete('cascade');
//            $table->integer('type')->default(1); // 1: tiên quyết, 2: song hành
            $table->timestamps();

            $table->primary(['course_id', 'prerequisite_course_id']);
        });

        Schema::create('notification', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message')->nullable();
//            $table->string('type')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::create('curriculum_programs', function (Blueprint $table) {
            $table->unsignedBigInteger('major_id');     // Khóa ngoại ngành học
            $table->unsignedBigInteger('course_id');    // Khóa ngoại môn học

            $table->integer('semester');                // Học kỳ áp dụng (1, 2, 3,...)

            $table->timestamps();

            // Khóa ngoại
            $table->foreign('major_id')->references('major_id')->on('majors')->onDelete('cascade');
            $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade');

            // Tránh trùng môn trong cùng học kỳ cùng ngành
            $table->unique(['major_id', 'course_id'], 'unique_curriculum_entry');
        });

        Schema::create('statistics', function (Blueprint $table) {
            $table->string('name', 50)->primary(); // Khóa chính là name
            $table->integer('value')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('registration_fee_details');
//        Schema::dropIfExists('student_course_registrations');
        Schema::dropIfExists('course_classes');
        Schema::dropIfExists('lesson_slots');
        Schema::dropIfExists('classrooms');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('majors');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('course_prerequisites');
        Schema::dropIfExists('payments');
    }
};
