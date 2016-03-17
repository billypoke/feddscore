<?php

namespace FeddScore\Http\Controllers;

use FeddScore\Competition;
use FeddScore\Http\Requests;
use Illuminate\Support\Facades\Input;

class IndexController extends Controller
{
    /**
     * @var array Given the first day of november, the day thanksgiving is on.
     */
    private static $thanksgiving = array(
        'Sun' => 26,
        'Mon' => 25,
        'Tue' => 24,
        'Wed' => 23,
        'Thu' => 22,
        'Fri' => 28,
        'Sat' => 29
    );

    /**
     * Calculates the day of FEDD for a specified year
     *
     * @param $year         int     The year to calculate the day of FEDD for
     * @return \DateTime            The date of FEDD for the year input
     */
    private static function getFeddDate($year)
    {
        $november1Weekday = date('D', mktime(0, 0, 0, 11, 1, $year));
        $feddDay = self::$thanksgiving[$november1Weekday] - 2;
        return \DateTime::createFromFormat('Y-m-d', "$year-11-$feddDay");
    }

    /**
     * The mode to run in will be determined based on the current date and the year specified either by a $_GET
     * parameter in getIndex(), or in getFeddDate() as the current year
     *
     * @param \DateTime $date
     * @param \DateTime $feddDay
     * @return string the mode the app should run in
     */
    private static function getMode($date, $feddDay)
    {
        $competitionCount = Competition::where('year', $date->format('Y'))->count();

        if ($competitionCount > 0) {
            if ($feddDay == $date) {
                $mode = 'repeater';
            } elseif ($date > $feddDay) {
                $mode = 'final';
            } elseif ($date < $feddDay) {
                $mode = 'advert';
            }
        } else {
            $mode = 'halloffame';
        }

        return $mode;
    }

    /**
     * Determines mode to run in and returns view to display, based on the current date
     *
     * Also allows setting of debug parameters:
     *      @var $debugDate \DateTime
     *      @var $debugYear string
     * to override the auto-generated values
     *
     * @param \DateTime $date   The current date
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function getIndex(\DateTime $date)
    {
        $debugDate = Input::get('date');
        $debugYear = Input::get('year');

        $date = isset($debugDate) ? \DateTime::createFromFormat('Y-m-d', $debugDate) : $date;
        $year = isset($debugYear) ? $debugYear : $date->format('Y');

        $feddDay = self::getFeddDate($year);

        $mode = self::getMode($date, $feddDay);

        switch ($mode) {
            case "repeater":
                return self::showRepeater($year);

            case "final":
                return self::showFinal($year);

            case "halloffame":
                return self::showHallOfFame($year);

            case "advert":
                return self::showAdvert($year);

            default:
                return self::showErrorPage();
        }
    }

    /**
     * return the advertisement view
     *
     * @param $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function showAdvert($year)
    {
        return view('scoreboard/advertisement', ['date' => self::getFeddDate($year)->format('Y-m-d')]);
    }

    /**
     * return the live repeater view
     *
     * @param $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function showRepeater($year)
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
    public static function showFinal($year)
    {

        $competitions = Competition::where('year', $year)
            ->where('status', 'final')
            ->orderBy('ampm', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('scoreboard/final-scores', [
            'year' => $year,
            'collapse' => false,
            'competitions' => $competitions
        ]);
    }

    /**
     * return the hall of fame view
     *
     * @param $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function showHallOfFame($year)
    {
        $competitions = Competition::where('year', $year-1)
            ->where('status', 'final')
            ->orderBy('ampm', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('scoreboard/final-scores', [
            'year' => $year-1,
            'collapse' => true,
            'competitions' => $competitions
        ]);
    }

    public static function showErrorPage()
    {
        return view('error');
    }
}
