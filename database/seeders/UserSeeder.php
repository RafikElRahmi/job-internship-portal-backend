<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where(['email' => 'superadmin@gmail.com'])->first();
        if (!$user) {
            DB::table('users')->insert([
                'firstname' => 'Super',
                'lastname' => 'Admin',
                'email' => 'superadmin@gmail.com',
                'phone' => '20202020',
                'education' => '',
                'section' => '',
                'adress' => '',
                'fax' => '',
                'role' => 'admin',
                'photo' => '/public/profile/admin.png',
                'password' => Hash::make('12345678'),
                'created_at' => Carbon::now(),
            ]);
        }
    }
}
