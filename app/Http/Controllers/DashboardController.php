<?php

namespace FeddScore\Http\Controllers;

use FeddScore\Competition;
use FeddScore\DesignDay;
use FeddScore\Http\Requests;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * @return DesignDay
     */
    private function getFeddDate()
    {
        return app(DesignDay::class);
    }

    /**
     * Determines mode to run in and returns view to display, based on the current date
     *
     * Also allows setting of debug parameters:
     *      @var $debugDate \DateTime
     *      @var $debugYear string
     * to override the auto-generated values
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCurrent($year = null)
    {
        $designDay = $this->getFeddDate();

        $year = isset($year) && is_int($year) ? $year : $designDay->today()->format('Y');
        $competitionCount = Competition::where('year', $designDay->today()->format('Y'))->count();

        if ($competitionCount > 0) {
            if ($designDay->isHappening()) {
                return $this->getRepeater($year);
            } elseif ($designDay->hasHappened()) {
                return $this->getFinal($year);
            } else {
                return $this->getAdvert($year);
            }
        } else {
            return $this->getHallOfFame($year);
        }
    }

    /**
     * return the advertisement view
     *
     * @param $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAdvert($year)
    {
        return view('scoreboard/advertisement', ['date' => $this->getFeddDate()->next()->format('Y-m-d')]);
    }

    /**
     * return the live repeater view
     *
     * @param $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getRepeater($year)
    {
        $competitions = Competition::where('year', $year)
            ->where('status', 'active')->get();

        return view('scoreboard/repeater',[
            'year' => $year,
            'collapse' => false,
            'competitions' => $competitions
        ]);
    }

    /**
     * return the final score view
     *
     * @param $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getFinal($year)
    {
        $competitions = Competition::where('year', $year)
            ->where('status', 'final')
            ->orderBy('ampm', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        $years = DB::table('competitions')
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->get();

        return view('scoreboard/final-scores', [
            'year' => $year,
            'collapse' => false,
            'competitions' => $competitions,
            'years' => $years
        ]);
    }

    /**
     * return the hall of fame view
     *
     * @param $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getHallOfFame($year)
    {
        $competitions = Competition::where('year', $year)
            ->where('status', 'final')
            ->orderBy('ampm', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        $years = DB::table('competitions')
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->get();

        return view('scoreboard/final-scores', [
            'year' => $year,
            'collapse' => true,
            'competitions' => $competitions,
            'years' => $years
        ]);
    }

    public function showErrorPage()
    {
        return view('error');
    }
}
