<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء مستخدم إداري
        User::updateOrCreate([
            'email' => 'admin@unv.edu',
        ], [
            'name' => 'المدير العام',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '0500000000',
        ]);

        // إنشاء معلمين
        User::updateOrCreate([
            'email' => 'ahmed.instructor@unv.edu',
        ], [
            'name' => 'د. أحمد محمد',
            'password' => Hash::make('instructor123'),
            'role' => 'instructor',
            'phone' => '0501111111',
        ]);

        User::updateOrCreate([
            'email' => 'fatima.instructor@unv.edu',
        ], [
            'name' => 'د. فاطمة علي',
            'password' => Hash::make('instructor123'),
            'role' => 'instructor',
            'phone' => '0502222222',
        ]);

        // إنشاء طلاب
        User::updateOrCreate([
            'email' => 'mohamed.student@unv.edu',
        ], [
            'name' => 'محمد أحمد',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'phone' => '0503333333',
            'department' => 'علوم الحاسب',
            'specialization' => 'برمجة',
            'level' => 2,
            'section' => 'ب',
        ]);

        User::updateOrCreate([
            'email' => 'sara.student@unv.edu',
        ], [
            'name' => 'سارة خالد',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'phone' => '0504444444',
            'department' => 'علوم الحاسب',
            'specialization' => 'عام',
            'level' => 1,
            'section' => 'أ',
        ]);

        User::updateOrCreate([
            'email' => 'ali.student@unv.edu',
        ], [
            'name' => 'علي حسن',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'phone' => '0505555555',
            'department' => 'الرياضيات',
            'specialization' => 'عام',
            'level' => 1,
            'section' => 'أ',
        ]);
    }
}
