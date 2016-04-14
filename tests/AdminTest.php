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
        $input = [
            'name' => 'Competition Name',
            'ampm' => 'am'
        ];
        $this->visit('admin')
            ->dontSee('Competition Name');
        $this->call('POST', 'admin/add', $input);
        $this->seePageIs('admin/add')
            ->see('Competition Name');
    }

    /** @test */
    public function it_can_edit_competitions()
    {
        $competition = $this->createAFakeCompetition('waiting');

        $input = [
            'name' => 'New Name',
            'ampm' => 'am'
        ];

        $this->visit('admin')
            ->click('Edit')
            ->seeElement('.edit')
            ->see('Editing Below')
            ->seeElement('#name', ['value'=>$competition->name])
            ->type('New Name', '#name')
            ->seeElement('#am', ['checked'=>true])
            ->seeElement('#pm', ['checked'=>false]);
        $this->call('POST', 'admin/rename', $input);
        $this->seePageIs('admin/rename')
            ->see('New Name');
    }

    /** @test */
    public function it_can_edit_competitions_and_save_as_new()
    {
        $competition = $this->createAFakeCompetition('waiting');

        $input = [
            'name' => 'New Name',
            'ampm' => 'am'
        ];

        $this->visit('admin')
            ->click('Edit')
            ->seeElement('.edit')
            ->see('Editing Below')
            ->seeElement('#name', ['value'=>$competition->name])
            ->type('New Name', '#name')
            ->seeElement('#am', ['checked'=>true])
            ->seeElement('#pm', ['checked'=>false]);
        $this->call('POST', 'admin/add', $input);
        $this->seePageIs('admin/add')
            ->see('New Name')
            ->see($competition->name);
    }

    /** @test */
    public function it_can_change_competition_status()
    {
        $competition = $this->createAFakeCompetition('waiting');

        $input = [
            "competitions" => array($competition->id),
            'action' => 'active'
        ];

        $this->visit('admin')
            ->seeInElement("#comp-{$competition->id} td:nth-child(4)", 'Waiting');
        $this->call('POST', 'admin/edit', $input);
        $this->seePageIs('admin/edit')
            ->seeInElement("#comp-{$competition->id} td:nth-child(4)", 'Active');
    }

    /** @test */
    public function it_can_delete_a_competition()
    {
        $competition = $this->createAFakeCompetition('waiting');

        $input = [
            "competitions" => array($competition->id),
            'action' => 'delete'
        ];

        $this->visit('admin')
            ->see($competition->name);
        $this->call('POST', 'admin/delete', $input);
        $this->seePageIs('admin/delete')
            ->dontSee($competition->name);
    }

    /** @test */
    public function it_can_create_teams()
    {
        $competition = $this->createAFakeCompetition('waiting');

        $teams = [
            'names' => "Team A\nTeam B"
        ];

        $this->visit("competition/{$competition->id}")
            ->type($teams, 'names');
        $this->call('POST', "competition/{$competition->id}/add", $teams);
        $this->seePageIs("competition/{$competition->id}/add")
            ->seeElement('.teaminput', ['value'=>'Team A'])
            ->seeElement('.teaminput', ['value'=>'Team B']);
    }

    /** @test */
    public function it_can_add_scores()
    {
        $competition = $this->createAFakeCompetitionWithTeams('active')->first();
        $teams = $competition->teams()->get();

        $team = $teams[0];
        $otherTeam = $teams[1];

        $randomScore = random_int(0,100);

        $input = [
            "update" => [
                $team->id => [
                    'name' => $team->name,
                    'score' => $randomScore,
                    'place' => $team->place,
                    'dq' => $team->dq
                ]
            ]
        ];

        $this->visit("competition/{$competition->id}")
            ->see($team->name)
            ->see($otherTeam->name);
        $this->call('POST', "competition/{$competition->id}/edit", $input);
        $this->seePageIs("competition/{$competition->id}/edit")
            ->seeElement("input[name=\"update[{$team->id}][score]\"]", ['value'=>$randomScore])
            ->seeElement("input[name=\"update[{$otherTeam->id}][score]\"]", ['value'=>$otherTeam->score]);
    }

    /** @test */
    public function it_can_disqualify_a_team()
    {
        $competition = $this->createAFakeCompetitionWithTeams('active')->first();
        $teams = $competition->teams()->get();

        $team = $teams[0];
        $otherTeam = $teams[1];

        $input = [
            "update" => [
                $team->id => [
                    'name' => $team->name,
                    'score' => $team->score,
                    'place' => $team->place,
                    'dq' => true
                ]
            ]
        ];

        $this->visit("competition/{$competition->id}")
            ->see($team->name)
            ->see($otherTeam->name);
        $this->call('POST', "competition/{$competition->id}/edit", $input);
        $this->seePageIs("competition/{$competition->id}/edit")
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

        $input = [
            "update" => [
                $team->id => [
                    'name' => $team->name,
                    'score' => $team->score,
                    'place' => 'first',
                    'dq' => $team->dq
                ]
            ]
        ];

        $this->visit("competition/{$competition->id}")
            ->see($team->name)
            ->see($otherTeam->name);
        $this->call('POST', "competition/{$competition->id}/edit", $input);
        $this->seePageIs("competition/{$competition->id}/edit")
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

        $input = [
            'delete' => $team->id
        ];

        $this->visit("competition/{$competition->id}")
            ->see($team->name)
            ->see($otherTeam->name);
        $this->call('POST', "competition/{$competition->id}/delete", $input);
        $this->seePageIs("competition/{$competition->id}/delete")
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
