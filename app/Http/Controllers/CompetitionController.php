<?php

namespace FeddScore\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use FeddScore\Http\Requests;
use FeddScore\Http\Controllers\Controller;

use FeddScore\Competition;

class CompetitionController extends Controller
{
    private static $year;

    private static $FOURTH_THURSDAYS = array(
        // if the first day of November is a <blank>, Thanksgiving is on...
        'Sun' => 26,    'Mon' => 25,    'Tue' => 24,    'Wed' => 23,
        'Thu' => 22,    'Fri' => 28,    'Sat' => 29
    );

    private static function getFeddDate()
    {
        // Have we been passed a year?
        $year = isset(self::$year) ? self::$year : date('Y');

        // If we just generated the current year, store it, otherwise overwrite the year with itself
        self::$year = $year;

        // mktime(hour,minute,second,month,day,year,[is_dst]);
        $november1Weekday = date('D', mktime(0, 0, 0, 11, 1, $year));
        $day = self::$FOURTH_THURSDAYS[$november1Weekday] - 2;
        return \DateTime::createFromFormat('Y-m-d', "$year-11-$day");
    }

    private static function getMode()
    {
        $feddDate = self::getFeddDate();
        $today = new \DateTime();

        $competitionCount = Competition::where('year', $today->format('Y'))->count();
        if ($competitionCount > 0) {
            if ($feddDate == $today) {
                $mode = 'repeater';
            } elseif ($today > $feddDate) {
                $mode = 'final';
            } elseif ($today < $feddDate) {
                $mode = 'advert';
            }
        } else {
            $mode = 'halloffame';
        }

        return $mode;
    }

    public static function getIndex()
    {
        $request = Request::capture();
        self::$year = $request->has('year') ? $request->input('year') : null;
        $mode = $request->has('mode') ? $request->input('mode') : self::getMode();

        switch ($mode) {
            case "repeater":
                return self::repeater(self::$year);
                break;

            case "final":
                return self::finalScores(self::$year);
                break;

            case "halloffame":
                return self::hallOfFame(self::$year);
                break;

            case "advert":
                return view('scoreboard/advertisement', ['year' => self::$year, 'date' => self::getFeddDate()->format('Y-m-d')]);

            default:
                echo "what the heck?";
                break;
        }
    }

    public static function repeater ($year) {
        $competitions = self::getCompetitions($year, 'active', FALSE);

        return view('scoreboard/repeater', [
            'year' => $year,
            'autoreload' => env('APP_DEBUG') ? empty($_GET['noreloading']) : FALSE,
            'competitions' => $competitions
        ]);

    }

    public static function finalScores ($year) {
        $competitions = self::getCompetitions($year, 'final');

        return view('scoreboard/final-scores', [
            'year' => $year,
            'collapse' => TRUE,
            'competitions' => $competitions
        ]);
    }

    public static function hallOfFame ($year) {
        $year = $year - 1;
        $competitions = self::getCompetitions($year, 'final');

        return view('scoreboard/final-scores', [
            'year' => $year,
            'collapse' => TRUE,
            'competitions' => $competitions
        ]);
    }

    public static function selectCompetitions ($rest, $bindings)
    {
        $stmt = DB::select(
            "SELECT competitions.id as competition_id , " .
            "competitions.name, competitions.ampm, " .
            "competitions.year, competitions.status, " .
            "teams.id as team_id, teams.name as team_name , " .
            "teams.score, teams.place, teams.disqualified " .
            "FROM competitions " .
            "LEFT JOIN teams " .
            "ON(teams.competition_id = competitions.id) " .
            $rest,
            $bindings
        );

        $comps = array();
        foreach ($stmt as $row) {

            $compID = $row->competition_id;
            if (!array_key_exists($compID, $comps)) {
                $comps[$compID] = array('id' => $compID,
                    'name' => $row->name,
                    'ampm' => $row->ampm,
                    'year' => $row->year,
                    'status' => $row->status,
                    'teams' => array());
            }
            if ($row->team_id !== NULL) {
                $comps[$compID]['teams'][] = array('id' => $row->team_id,
                    'name' => $row->team_name,
                    'score' => $row->score,
                    'place' => $row->place,
                    'disqualified' => $row->disqualified);
            }
        }

        return $comps;
    }

    public static function getCompetitions ($year, $status, $byPlace = TRUE) {
        return self::selectCompetitions(
            "WHERE competitions.year = :year AND competitions.status = :status " .
            "ORDER BY competitions.ampm ASC, competitions.name ASC, " .
            ($byPlace ? "teams.place DESC, " : "") . "teams.score DESC",
            ['year'=> $year, 'status' => $status]
        );
    }

    public static function getCompetition ($id) {
        return DB::table('competitions')->where('id', $id);
    }
}
