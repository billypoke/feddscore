<?php

namespace FeddScore\Http\Controllers;

use Illuminate\Http\Request;

use FeddScore\Http\Requests;
use FeddScore\Http\Controllers\Controller;

use FeddScore\Competition;

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

    public static function getIndex(\DateTime $date)
    {
        $year = $date->format('Y');
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

    public static function showAdvert($year)
    {
        return view('scoreboard/advertisement', ['date' => self::getFeddDate($year)->format('Y-m-d')]);
    }

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
