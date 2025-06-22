<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('statistics')->insert([
            ['name' => 'total_students', 'value' => 0],
            ['name' => 'total_teachers', 'value' => 0],
            ['name' => 'total_departments', 'value' => 0],
            ['name' => 'total_majors', 'value' => 0],
            ['name' => 'total_classes', 'value' => 0],
            ['name' => 'total_courses', 'value' => 0],
            ['name' => 'total_course_classes', 'value' => 0],
            ['name' => 'total_classrooms', 'value' => 0],
            ['name' => 'total_users', 'value' => 0],
        ]);
    }
}
