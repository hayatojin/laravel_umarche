<?php

namespace Database\Seeders;

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
        DB::table('users')->insert([
            [
                'name' => 'user1',
                'email' => 'user1@gmail.com',
                'password' => Hash::make('iiiiiiii'),
                'created_at' => '2022/09/20 11:11:11'
            ], 
        ]);
    }
}
