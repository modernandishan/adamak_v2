<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['meta_title' => 'consultant_fee_percentage'],
            [
                'meta_value' => '30',
                'description' => 'درصد کارمزد مشاوران',
                'type' => 'number',
            ]
        );

        // تنظیمات کارمزد بازاریابان
        Setting::updateOrCreate(
            ['meta_title' => 'marketer_fee_percentage'],
            [
                'meta_value' => '20',
                'description' => 'درصد کارمزد بازاریابان',
                'type' => 'number',
            ]
        );

        // تنظیمات سیستم
        Setting::updateOrCreate(
            ['meta_title' => 'site_name'],
            [
                'meta_value' => 'سیستم آزمون ادمک',
                'description' => 'نام سایت',
                'type' => 'text',
            ]
        );

        Setting::updateOrCreate(
            ['meta_title' => 'site_description'],
            [
                'meta_value' => 'سیستم آزمون و مشاوره آنلاین',
                'description' => 'توضیحات سایت',
                'type' => 'text',
            ]
        );

        Setting::updateOrCreate(
            ['meta_title' => 'maintenance_mode'],
            [
                'meta_value' => '0',
                'description' => 'حالت تعمیر و نگهداری',
                'type' => 'boolean',
            ]
        );

        Setting::updateOrCreate(
            ['meta_title' => 'admin_email'],
            [
                'meta_value' => 'admin@example.com',
                'description' => 'ایمیل مدیر سیستم',
                'type' => 'text',
                'is_private' => true,
            ]
        );
    }
}
