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

    public static function getIndex(\DateTime $date)
    {
        $year = $date->format('Y');
        $feddDay = self::getFeddDate($year);

        $numCompetitions = Competition::where('year', $year)->count();

        if ($numCompetitions > 0) {
            if ($date < $feddDay) {
                return self::showAdvert($year);
            } elseif ($date == $feddDay) {
                return self::showRepeater($year);
            } elseif ($date > $feddDay) {
                return self::showFinal($year);
            }
        } else {
            return self::showHallOfFame($year);
        }

        return self::showErrorPage();
    }

    public static function showAdvert($year)
    {
        return view('scoreboard/advertisement', ['date' => self::getFeddDate($year)->format('Y-m-d')]);
    }

    public static function showRepeater($year)
    {
        $competitions = Competition::where('year', $year)
                            ->where('status', 'active');

        return view('scoreboard/repeater',[
            'year' => $year,
            'collapse' => false,
            'competitions' => $competitions
        ]);
    }

    public static function showFinal($year)
    {
        $competitions = Competition::where('year', $year)
                            ->where('status', 'final');

        return view('scoreboard/final-scores', [
            'year' => $year,
            'collapse' => false,
            'competitions' => $competitions
        ]);
    }

    public static function showHallOfFame($year)
    {
        $competitions = Competition::where('year', $year - 1)
                            ->where('status', 'final');

        return view('scoreboard/final-scores', [
            'year' => $year-1,
            'collapse' => false,
            'competitions' => $competitions
        ]);
    }

    public static function showErrorPage()
    {
        return view('error');
    }
}
