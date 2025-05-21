<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SettingsSeeder::class,
        ]);

        $user = User::create([
            'first_name' => 'محمد جواد',
            'last_name' => 'قانع دستجردی',
            'mobile' => '09904861378',
            'mobile_verified_at' => now(),
            'password' => Hash::make('password'),
            'referral_code' => Str::random(8),
        ]);

        $user->profile->update([
            'gender' => 'male',
            'relationship' => 'پدر',
            'province' => 'اصفهان',
            'city' => 'اصفهان',
            'address' => 'آدرس تست',
            'postal_code' => '12345'
        ]);

        $user->assignRole('super_admin');

        $this->command->info('کاربر تست با موفقیت ایجاد شد!');
    }
}
