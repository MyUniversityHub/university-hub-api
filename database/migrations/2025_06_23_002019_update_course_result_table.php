<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_course_results', function (Blueprint $table) {
            $table->decimal('frequent_score_1', 3, 1)->nullable()->change();
            $table->decimal('frequent_score_2', 3, 1)->nullable()->change();
            $table->decimal('frequent_score_3', 3, 1)->nullable()->change();
            $table->decimal('final_score', 3, 1)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('scores', function (Blueprint $table) {
            // Giả sử trước đó là (2,1), bạn có thể khôi phục lại như sau:
            $table->decimal('frequent_score_1', 2, 1)->nullable()->change();
            $table->decimal('frequent_score_2', 2, 1)->nullable()->change();
            $table->decimal('frequent_score_3', 2, 1)->nullable()->change();
            $table->decimal('final_score', 2, 1)->nullable()->change();
        });
    }
};
