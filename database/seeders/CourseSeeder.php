<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'code' => 'CS101',
                'name' => 'مقدمة في علوم الحاسب',
                'description' => 'مقدمة أساسية في علوم الحاسب',
                'instructor_id' => 2, // Instructor User
                'department' => 'علوم الحاسب',
                'specialization' => 'عام',
                'level' => 1,
                'section' => 'أ',
                'status' => 'active',
                'credit_hours' => 3,
                'capacity' => 50,
            ],
            [
                'code' => 'CS201',
                'name' => 'برمجة متقدمة',
                'description' => 'مفاهيم متقدمة في البرمجة',
                'instructor_id' => 6, // د. أحمد محمد
                'department' => 'علوم الحاسب',
                'specialization' => 'برمجة',
                'level' => 2,
                'section' => 'ب',
                'status' => 'active',
                'credit_hours' => 4,
                'capacity' => 40,
            ],
            [
                'code' => 'MATH101',
                'name' => 'رياضيات 1',
                'description' => 'مبادئ الرياضيات الأساسية',
                'instructor_id' => 7, // د. فاطمة علي
                'department' => 'الرياضيات',
                'specialization' => 'عام',
                'level' => 1,
                'section' => 'أ',
                'status' => 'active',
                'credit_hours' => 3,
                'capacity' => 60,
            ],
        ];

        foreach ($courses as $course) {
            \App\Models\Course::create($course);
        }
    }
}
