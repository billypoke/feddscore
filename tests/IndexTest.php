<?php

use FeddScore\DesignDay;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use FeddScore\Http\Controllers\IndexController;

class IndexTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_shows_an_advertisement_before_design_day()
    {
        $this->todayIs('2016-04-23');
        $this->createSomeFakeCompetitions();
        
        $this->visit('dashboard/2016/advert')
            ->see('Freshman Engineering Design Day');
    }

    /** @test */
    public function it_shows_realtime_scores_on_design_day()
    {
        $this->todayIs('2016-11-22');
        $this->createSomeFakeCompetitions();

        $this->visit('dashboard')
            ->see('Live Scores');
    }

    /** @test */
    public function it_shows_final_scores_after_design_day()
    {
        $this->todayIs('2016-11-23');
        $this->createSomeFakeCompetitions();

        $this->visit('dashboard')
            ->see('Final Scores for Fall 2016');
    }

    /** @test */
    public function it_shows_hall_of_fame_when_there_are_no_competitions()
    {
        $this->todayIs('2017-01-01');

        $this->visit('dashboard')
            ->see('Final Scores for Fall 2016');
    }

    private function todayIs($dateString)
    {
        $this->app[DesignDay::class] = new DesignDay(new DateTime($dateString));
    }

    private function createSomeFakeCompetitions()
    {
        factory(FeddScore\Competition::class, 'waiting', 3)
            ->create()
            ->each(function($competition) {
                $competition->teams()->save(factory(FeddScore\Team::class)->make());
            });
    }
}
