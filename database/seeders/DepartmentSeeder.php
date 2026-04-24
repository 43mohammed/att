<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Specialization;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'هندسة' => ['هندسة الحاسوب', 'هندسة الإلكترونيات', 'هندسة المدنية', 'هندسة الميكانيكا'],
            'علوم' => ['رياضيات', 'فيزياء', 'كيمياء', 'أحياء'],
            'آداب' => ['لغة عربية', 'لغة إنجليزية', 'تاريخ', 'جغرافيا'],
            'تجارة' => ['محاسبة', 'إدارة أعمال', 'اقتصاد', 'تسويق'],
            'طب' => ['طب بشري', 'طب أسنان', 'صيدلة', 'تمريض'],
        ];

        foreach ($departments as $deptName => $specializations) {
            $department = Department::create(['name' => $deptName]);

            foreach ($specializations as $specName) {
                Specialization::create([
                    'department_id' => $department->id,
                    'name' => $specName,
                ]);
            }
        }
    }
}