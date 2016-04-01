<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminTest extends TestCase
{
    use DatabaseMigrations;
    use WithoutMiddleware;

    /** @test */
    public function it_can_create_competitions()
    {
        $this->visit('admin')
            ->type('Competition Name', '#name')
            ->select('am', 'ampm')
            ->press('Add')
            ->see('Competition Name');
    }

    /** @test */
    public function it_can_edit_competitions()
    {
        $competition = $this->createAFakeCompetition('waiting');

        $this->visit('admin')
            ->click('Edit')
            ->seeElement('.edit')
            ->see('Editing Below')
            ->seeElement('#name', ['value'=>$competition->name])
            ->type('New Name', '#name')
            ->seeElement('#am', ['checked'=>true])
            ->seeElement('#pm', ['checked'=>false])
            ->press('Save')
            ->see('New Name');
    }

    /** @test */
    public function it_can_edit_competitions_and_save_as_new()
    {
        $competition = $this->createAFakeCompetition('waiting');

        $this->visit('admin')
            ->click('Edit')
            ->seeElement('.edit')
            ->see('Editing Below')
            ->seeElement('#name', ['value'=>$competition->name])
            ->type('New Name', '#name')
            ->seeElement('#am', ['checked'=>true])
            ->seeElement('#pm', ['checked'=>false])
            ->press('Save as New')
            ->see('New Name')
            ->see($competition->name);
    }

    /** @test */
    public function it_can_change_competition_status()
    {
        $competition = $this->createAFakeCompetition('waiting');

        $input = [
            "competitions[0]" => $competition->id
        ];

        $this->visit('admin')
            ->submitForm('Mark Active', $input)
            ->seeInElement("#comp-{$competition->id} td:nth-child(4)", 'Active');
    }

    /** @test */
    public function it_can_delete_a_competition()
    {
        $competition = $this->createAFakeCompetition('waiting');

        $input = [
            "competitions[0]" => $competition->id
        ];

        $this->visit('admin')
            ->submitForm('Delete', $input)
            ->dontSee($competition->name);
    }

    /** @test */
    public function it_can_create_teams()
    {
        $competition = $this->createAFakeCompetition('waiting');

        $teams = "Team A\r\nTeam B";

        $this->visit("competition/{$competition->id}")
            ->type($teams, 'names')
            ->press('Add Teams')
            ->seeElement('.teaminput', ['value'=>'Team A'])
            ->seeElement('.teaminput', ['value'=>'Team B']);
    }

    /** @test */
    public function it_can_add_scores()
    {
        $competition = $this->createAFakeCompetitionWithTeams('active')->first();
        $team = $competition->teams()->first();

        $randomScore = range(0,100);

        $input = [
            "update[{$team->id}][score]"=>$randomScore
        ];

        $this->visit("competition/{$competition->id}")
            ->submitForm('Save', $input)
            ->seeElement("update[{$team->id}][score]", ['value'=>$randomScore]);
    }

    /** @test */
    public function it_can_disqualify_a_team()
    {

    }

    /** @test */
    public function it_can_assign_a_place()
    {

    }

    public function it_can_delete_a_team()
    {

    }

    private function createAFakeCompetition($status)
    {
        return factory(FeddScore\Competition::class, $status, 1)
            ->create();
    }

    private function createAFakeCompetitionWithTeams($status)
    {
        return factory(FeddScore\Competition::class, $status, 2)
            ->create()
            ->each(function($competition) {
                $competition->teams()->save(factory(FeddScore\Team::class)->make());
            });
    }
}
