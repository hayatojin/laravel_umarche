<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->insert([
            'name' => 'test',
            'email' => 'ttt@gmail.com',
            'password' => Hash::make('iiiiiiii'),
            'created_at' => '2022/09/14 11:11:11'
        ]);
    }
}
