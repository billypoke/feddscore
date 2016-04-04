<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use \FeddScore\Competition;

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

        $randomScore = random_int(0,100);

        $input = [
            "update[{$team->id}][score]" => $randomScore
        ];

        $this->visit("competition/{$competition->id}")
            ->submitForm('Save', $input)
            ->seeElement("input[name=\"update[{$team->id}][score]\"]", ['value'=>$randomScore]);
    }

    /** @test */
    public function it_can_disqualify_a_team()
    {
        $competition = $this->createAFakeCompetitionWithTeams('active')->first();
        $teams = $competition->teams()->get();

        $team = $teams[0];
        $otherTeam = $teams[1];

        $this->visit("competition/{$competition->id}")
            ->see($team->name)
            ->see($otherTeam->name)
            ->check("update[{$team->id}][dq]")
            ->press('Save')
            ->seeIsChecked("update[{$team->id}][dq]")
            ->dontSeeIsChecked("update[{$otherTeam->id}][dq]");
    }

    /** @test */
    public function it_can_assign_a_place()
    {
        $competition = $this->createAFakeCompetitionWithTeams('active')->first();
        $teams = $competition->teams()->get();

        $team = $teams[0];
        $otherTeam = $teams[1];

        $this->visit("competition/{$competition->id}")
            ->select('first', "update[{$team->id}][place]")
            ->press('Save')
            ->seeElement("select[name=\"update[{$team->id}][place]\"] option[value=\"first\"]", ['selected' => true])
            ->seeElement("select[name=\"update[{$otherTeam->id}][place]\"] option[value=\"first\"]", ['selected' => false]);
    }

    /** @test */
    public function it_can_delete_a_team()
    {
        $competition = $this->createAFakeCompetitionWithTeams('active')->first();
        $teams = $competition->teams()->get();

        $team = $teams[0];
        $otherTeam = $teams[1];

        $this->visit("competition/{$competition->id}")
            ->press('Del')
            ->dontSee($team->name)
            ->see($otherTeam->name);
    }

    private function createAFakeCompetition($status)
    {
        return factory(FeddScore\Competition::class, $status)
            ->create();
    }

    private function createAFakeCompetitionWithTeams($status)
    {
        return factory(FeddScore\Competition::class, $status, 2)
            ->create()
            ->each(function($competition) {
                $competition->teams()->saveMany(factory(FeddScore\Team::class, 2)->make());
            });
    }
}
