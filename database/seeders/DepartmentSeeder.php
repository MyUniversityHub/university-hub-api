<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $departments = [
            ['name' => 'Computer Science', 'active' => true],
            ['name' => 'Mathematics', 'active' => true],
            ['name' => 'Physics', 'active' => false],
            ['name' => 'Biology', 'active' => true],
            ['name' => 'Chemistry', 'active' => false],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->insert([
                'name' => $department['name'],
                'active' => $department['active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
