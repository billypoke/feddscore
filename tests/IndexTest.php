<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use FeddScore\Http\Controllers\IndexController;

class IndexTest extends TestCase
{
    public function testIndexRepeater()
    {
        $competitions = factory(FeddScore\Competition::class, 'active', 3)
                            ->create()
                            ->each(function($competition) {
                                $competition->teams()->save(factory(FeddScore\Team::class)->make());
                            });

        $date = \DateTime::createFromFormat('Y-m-d', '2016-11-22');

        $content = IndexController::getIndex($date)->render();
        $expectedPhrase = 'Live Scores for Fall 2016';

        $this->assertContains($expectedPhrase, $content);

        foreach ($competitions as $competition) {
            $this->assertContains(htmlspecialchars($competition->name, ENT_QUOTES), $content);
            foreach ($competition->teams() as $team) {
                $this->assertContains(htmlspecialchars($team->name, ENT_QUOTES), $content);
            }
        }
    }

    public function testIndexFinal()
    {
        $competitions = factory(FeddScore\Competition::class, 'final', 3)
                            ->create()
                            ->each(function($competition) {
                                $competition->teams()->save(factory(FeddScore\Team::class)->make());
                            });

        $date = \DateTime::createFromFormat('Y-m-d', '2016-11-23');

        $content = IndexController::getIndex($date)->render();
        $expected = 'Final Scores for Fall 2016';

        $this->assertContains($expected, $content);

        foreach ($competitions as $competition) {
            $this->assertContains(htmlspecialchars($competition->name, ENT_QUOTES), $content);
            foreach ($competition->teams() as $team) {
                $this->assertContains(htmlspecialchars($team->name, ENT_QUOTES), $content);
            }
        }
    }

    public function testIndexHallOfFame()
    {
        $competitions = factory(FeddScore\Competition::class, 'final', 3)
            ->create()
            ->each(function($competition) {
                $competition->teams()->save(factory(FeddScore\Team::class)->make());
            });

        $date = \DateTime::createFromFormat('Y-m-d', '2017-01-01');

        $content = IndexController::getIndex($date)->render();
        $expected = 'Final Scores for Fall 2016';

        $this->assertContains($expected, $content);

        foreach ($competitions as $competition) {
            $this->assertContains(htmlspecialchars($competition->name, ENT_QUOTES), $content);
            foreach ($competition->teams() as $team) {
                $this->assertContains(htmlspecialchars($team->name, ENT_QUOTES), $content);
            }
        }
    }

    public function testAdvertisement()
    {
        $competitions = factory(FeddScore\Competition::class, 'waiting', 3)
                            ->create()
                            ->each(function($competition) {
                                $competition->teams()->save(factory(FeddScore\Team::class)->make());
                            });

        $date = \DateTime::createFromFormat('Y-m-d', '2016-01-01');

        $content = IndexController::getIndex($date)->render();
        $expected = array(
            'This year\'s Freshman Engineering Design Day will be held on',
            'Tuesday, November 22, 2016'
        );

        foreach ($expected as $phrase) {
            $this->assertContains($phrase, $content);
        }
    }
}
