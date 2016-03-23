<?php

use Illuminate\Database\Seeder;

class CompetitionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('competitions')->insert([
            'year' => date('Y'),
            'ampm' => 'am',
            'name' => str_random(10),
            'status' => 'active'
        ]);
    }
}
