<?php

use FeddScore\DesignDay;

class DesignDayTest extends TestCase
{

    /** @test */
    public function it_calculates_this_years_design_day()
    {
        $designDay = new DesignDay(new DateTime('2016-09-22'));

        $this->assertEquals('2016-11-22', $designDay->current()->format('Y-m-d'));
    }

    /** @test */
    public function it_knows_if_design_day_has_happened_yet()
    {
        $designDay = new DesignDay(new DateTime('2016-09-22'));
        $this->assertFalse($designDay->hasHappened());
    }

    /** @test */
    public function it_calculates_the_next_design_day()
    {
        // Calculate this year's.
        $designDay = new DesignDay(new DateTime('2016-09-22'));
        $this->assertEquals('2016-11-22', $designDay->next()->format('Y-m-d'));

        // Calculate next year's because we're after the date.
        $designDay = new DesignDay(new DateTime('2016-12-22'));
        $this->assertEquals('2017-11-21', $designDay->next()->format('Y-m-d'));
    }
}