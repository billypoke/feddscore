<?php

use Illuminate\Database\Seeder;

class TeamsTableSeeder extends Seeder
{
    private $places = array(
        'honorable',
        'third',
        'second',
        'first',
        null
    );
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('teams')->insert([
            'competition_id' => 1,
            'name' => str_random(10),
            'score' => random_int(0, 10000),
            'place' => $this->places[random_int(0, count($this->places)-1)],
            'disqualified' => random_int(0,1),
        ]);
    }
}
