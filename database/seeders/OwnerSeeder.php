<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('owners')->insert([
            [
                'name' => 'test1',
                'email' => 'ttt1@gmail.com',
                'password' => Hash::make('iiiiiiii'),
                'created_at' => '2022/09/14 11:11:11'
            ],
            [
                'name' => 'test2',
                'email' => 'ttt2@gmail.com',
                'password' => Hash::make('iiiiiiii'),
                'created_at' => '2022/09/14 11:11:11'
            ],
            [
                'name' => 'test3',
                'email' => 'ttt3@gmail.com',
                'password' => Hash::make('iiiiiiii'),
                'created_at' => '2022/09/14 11:11:11'
            ],
        ]);
    }
}
