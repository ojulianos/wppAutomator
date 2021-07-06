<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('messages')->insert([
            'reference_phone' => '555182346281',
            'description' => 'Primeiro Contato',
            'body' => 'Olá, seja bem vindo, qual sua dúvida?',
            'type' => 'primeiro-contato',
            'tags' => 'oi, olá, hi, hello'
        ]);
    }
}
