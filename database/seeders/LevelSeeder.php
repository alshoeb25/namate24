<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    public function run()
    {
        $levels = [
            // Skill Level
            ['name' => 'Beginner', 'group_name' => 'Skill Level', 'value' => 1, 'order' => 1],
            ['name' => 'Intermediate', 'group_name' => 'Skill Level', 'value' => 2, 'order' => 2],
            ['name' => 'Expert', 'group_name' => 'Skill Level', 'value' => 3, 'order' => 3],

            // Grades
            ['name' => 'Pre-KG, Nursery', 'group_name' => 'Grades', 'value' => 4, 'order' => 4],
            ['name' => 'Kindergarten (KG)', 'group_name' => 'Grades', 'value' => 5, 'order' => 5],
            ['name' => 'Grade 1', 'group_name' => 'Grades', 'value' => 6, 'order' => 6],
            ['name' => 'Grade 2', 'group_name' => 'Grades', 'value' => 7, 'order' => 7],
            ['name' => 'Grade 3', 'group_name' => 'Grades', 'value' => 8, 'order' => 8],
            ['name' => 'Grade 4', 'group_name' => 'Grades', 'value' => 9, 'order' => 9],
            ['name' => 'Grade 5', 'group_name' => 'Grades', 'value' => 10, 'order' => 10],
            ['name' => 'Grade 6', 'group_name' => 'Grades', 'value' => 11, 'order' => 11],
            ['name' => 'Grade 7', 'group_name' => 'Grades', 'value' => 12, 'order' => 12],
            ['name' => 'Grade 8', 'group_name' => 'Grades', 'value' => 13, 'order' => 13],
            ['name' => 'Grade 9', 'group_name' => 'Grades', 'value' => 14, 'order' => 14],
            ['name' => 'Grade 10', 'group_name' => 'Grades', 'value' => 15, 'order' => 15],
            ['name' => 'IGCSE', 'group_name' => 'Grades', 'value' => 16, 'order' => 16],
            ['name' => 'GCSE', 'group_name' => 'Grades', 'value' => 17, 'order' => 17],
            ['name' => 'O level', 'group_name' => 'Grades', 'value' => 18, 'order' => 18],
            ['name' => 'Grade 11', 'group_name' => 'Grades', 'value' => 19, 'order' => 19],
            ['name' => 'AS level', 'group_name' => 'Grades', 'value' => 20, 'order' => 20],
            ['name' => 'A2 level', 'group_name' => 'Grades', 'value' => 21, 'order' => 21],
            ['name' => 'A level', 'group_name' => 'Grades', 'value' => 22, 'order' => 22],
            ['name' => 'Grade 12', 'group_name' => 'Grades', 'value' => 23, 'order' => 23],
            ['name' => 'Diploma', 'group_name' => 'Grades', 'value' => 24, 'order' => 24],
            ['name' => 'Bachelors/Undergraduate', 'group_name' => 'Grades', 'value' => 25, 'order' => 25],
            ['name' => 'Masters/Postgraduate', 'group_name' => 'Grades', 'value' => 26, 'order' => 26],
            ['name' => 'MPhil', 'group_name' => 'Grades', 'value' => 27, 'order' => 27],
            ['name' => 'Doctorate/PhD', 'group_name' => 'Grades', 'value' => 28, 'order' => 28],

            // Others
            ['name' => 'Adult/Casual learning', 'group_name' => 'Others', 'value' => 29, 'order' => 29],
        ];

        DB::table('levels')->insert($levels);
    }
}
