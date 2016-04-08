<?php

use FeddScore\DesignDay;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DashboardTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_shows_an_advertisement_before_design_day()
    {
        $this->todayIs('2016-04-23');
        $this->createSomeFakeCompetitions('waiting');
        
        $this->visit('dashboard/2016/advert')
            ->see('Freshman Engineering Design Day');
    }

    /** @test */
    public function it_shows_realtime_scores_on_design_day()
    {
        $this->todayIs('2016-11-22');
        $competitions = $this->createSomeFakeCompetitions('active');

        $this->visit('dashboard')
            ->see('Live Scores');

        $competitions->each(function ($competition, $key) {
            $competition->teams()->each(function ($team, $key) {
                $this->seeInElement('#team-' . $team->id . ' .score', $team->score);
            });
        });
    }

    /** @test */
    public function it_shows_final_scores_after_design_day()
    {
        $this->todayIs('2016-11-23');
        $competitions = $this->createSomeFakeCompetitions('final');

        $this->visit('dashboard')
            ->see('Final Scores for Fall 2016');

        $competitions->each(function ($competition, $key) {
            $competition->teams()->each(function ($team, $key) {
                $this->seeInElement('#team-' . $team->id . ' .score', $team->score);
            });
        });
    }

    /** @test */
    public function it_shows_hall_of_fame_when_there_are_no_competitions_for_current_year()
    {
        $this->todayIs('2017-01-01');
        $competitions = $this->createSomeFakeCompetitions('final'); // creates competitions for 2016

        $this->visit('dashboard')
            ->see('Final Scores for Fall 2016');

        $competitions->each(function ($competition, $key) {
            $competition->teams()->each(function ($team, $key) {
                $this->seeInElement('#team-' . $team->id . ' .score', $team->score);
            });
        });
    }

    /** @test */
    public function it_shows_collapsed_scores_when_in_hall_of_fame()
    {
        $this->todayIs('2017-01-01');
        $competitions = $this->createSomeFakeCompetitions('final'); // creates competitions for 2016

        $this->visit('dashboard')
            ->assertViewHas('collapse', true);
    }

    private function todayIs($dateString)
    {
        $this->app[DesignDay::class] = new DesignDay(new DateTime($dateString));
    }

    private function createSomeFakeCompetitions($status)
    {
        return factory(FeddScore\Competition::class, $status, 3)
            ->create()
            ->each(function($competition) {
                $competition->teams()->save(factory(FeddScore\Team::class)->make());
            });
    }
}
