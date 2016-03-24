<?php

namespace FeddScore;

use DateTime;

class DesignDay
{
    /**
     * @var DateTime
     */
    private $current;

    public function __construct(DateTime $current)
    {
        $this->current = $current;
    }

    /**
     * Return the current year's design day date.
     * @return DateTime
     */
    public function current()
    {
        $year = $this->current->format('Y');
        return new DateTime("fourth thursday of november {$year} -2 days");
    }

    /**
     * @return bool true if this year's design day has occurred. Otherwise, false.
     */
    public function hasHappened()
    {
        return $this->current > $this->current();
    }

    /**
     * @return bool true if the compeition is happening now. Otherwise, false.
     */
    public function isHappening()
    {
        return $this->current == $this->current();
    }

    /**
     * Return the next date Design Day occurs on after current.
     * @return DateTime
     */
    public function next()
    {
        if ($this->hasHappened()) {
            return new DateTime(
                sprintf("fourth thursday of november %s -2 days", $this->current->format('Y') + 1)
            );
        }

        return $this->current();
    }

    /**
     * @return DateTime
     */
    public function today()
    {
        return $this->current;
    }
}