<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('setting')->insert([
            'id_setting' => 1,
            'company_name' => 'Toko Ku',
            'address' => 'Jl. Kibandang Samaran Ds. Slangit',
            'telephone' => '081234779987',
            'note_type' => 1, // kecil
            'discount' => 5,
            'path_logo' => '/img/logo.png',
            'member_card_path' => '/img/member.png',
        ]);
    }
}
