<?php

use FeddScore\DesignDay;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DashboardTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_shows_an_advertisement_before_design_day()
    {
        $this->todayIs($this->getBaseDate()." -1 week");
        $this->createSomeFakeCompetitions('waiting');
        
        $this->visit('dashboard/'.date("Y").'/advert')
            ->see('Freshman Engineering Design Day');
    }

    /** @test */
    public function it_shows_realtime_scores_on_design_day()
    {
        $this->todayIs($this->getBaseDate());
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
        $this->todayIs($this->getBaseDate()." +1 week");
        $competitions = $this->createSomeFakeCompetitions('final');

        $this->visit('dashboard')
            ->see('Final Scores for:')
            ->seeInElement('#yearSelect', 'Fall '.date("Y"));

        $competitions->each(function ($competition, $key) {
            $competition->teams()->each(function ($team, $key) {
                $this->seeInElement('#team-' . $team->id . ' .score', $team->score);
            });
        });
    }

    /** @test */
    public function it_shows_hall_of_fame_when_there_are_no_competitions_for_current_year()
    {
        $this->todayIs($this->getBaseDate()." +3 months");
        $competitions = $this->createSomeFakeCompetitions('final'); // creates competitions for current year

        $this->visit('dashboard')
            ->see('Hall of Fame for:')
            ->seeInElement('#yearSelect', 'Fall '.date("Y"));

        $competitions->each(function ($competition, $key) {
            $competition->teams()->each(function ($team, $key) {
                $this->seeInElement('#team-' . $team->id . ' .score', $team->score);
            });
        });
    }

    /** @test */
    public function it_shows_collapsed_scores_when_in_hall_of_fame()
    {
        $this->todayIs($this->getBaseDate()." +3 months");
        $competitions = $this->createSomeFakeCompetitions('final'); // creates competitions for 2016

        $this->visit('dashboard')
            ->assertViewHas('collapse', true);
    }

    private function todayIs($dateString)
    {
        $this->app[DesignDay::class] = new DesignDay(new DateTime($dateString));
    }

    /**
     * @return string Design day of the current year, used for constructing other dates
     */
    private function getBaseDate()
    {
        return "fourth thursday of november ".date("Y")." -2 days";
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
