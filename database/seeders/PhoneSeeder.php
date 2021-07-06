<?php

namespace Database\Seeders;

use Faker\Guesser\Name;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('phones')->insert([
            'user_id' => 1,
            'phone_number' => '555182346281',
            'name' => 'Tim RS',
            'platform_api_url' => '',
        ]);
    }
}
