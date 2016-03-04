<?php

namespace FeddScore\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use FeddScore\Http\Requests;
use FeddScore\Http\Controllers\Controller;

use FeddScore\Competition;

class CompetitionController extends Controller
{
    /**
     * @var string the year to use for a request
     */
    private static $year;

    /**
     * @var array Given the first day of November, what day is Thanksgiving on
     */
    private static $FOURTH_THURSDAYS = array(
        // if the first day of November is a <blank>, Thanksgiving is on...
        'Sun' => 26,    'Mon' => 25,    'Tue' => 24,    'Wed' => 23,
        'Thu' => 22,    'Fri' => 28,    'Sat' => 29
    );

    /**
     * If the self::$year variable has been set (via a $_GET parameter in getIndex() probably),
     * calculate the day of FEDD for that year
     *
     * Otherwise, calculate the day of FEDD fot eh current year
     *
     * @return \DateTime The day that FEDD will be held on
     */
    private static function getFeddDate()
    {
        // Have we been passed a year?
        $year = self::$year;

        // mktime(hour,minute,second,month,day,year[, is_dst]);
        $november1Weekday = date('D', mktime(0, 0, 0, 11, 1, $year));
        $day = self::$FOURTH_THURSDAYS[$november1Weekday] - 2;
        return \DateTime::createFromFormat('Y-m-d', "$year-11-$day");
    }

    /**
     * The mode to run in will be determined based on the current date and the year specified either by a $_GET
     * parameter in getIndex(), or in getFeddDate() as the current year
     *
     * @return string the mode the app should run in
     */
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

    /**
     * If the $_GET parameters 'year' or 'mode' are specified, use those, otherwise, use the defaults.
     * Then load the corresponding view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View the view to show based on mode and year
     */
    public static function getIndex()
    {
        // Grab the $_GET params, if there are any
        $request = Request::capture();
        self::$year = $request->has('year') ? $request->input('year') : date('Y');
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
                return view('scoreboard/advertisement', ['date' => self::getFeddDate()->format('Y-m-d')]);

            default:
                echo "what the heck?";
                break;
        }
    }

    /**
     * @param $year string the year to show the repeater view for
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View the repeater view to be rendered by Twig
     */
    public static function repeater ($year) {
        $competitions = self::getCompetitions($year, 'active', FALSE);

        return view('scoreboard/repeater', [
            'year' => $year,
            'autoreload' => env('APP_DEBUG') ? empty($_GET['noreloading']) : FALSE,
            'competitions' => $competitions
        ]);

    }

    /**
     * @param $year string the year to show the final-scores view with non-placing scores shown
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View the repeater view to be rendered by Twig
     */
    public static function finalScores ($year) {
        $competitions = self::getCompetitions($year, 'final');

        return view('scoreboard/final-scores', [
            'year' => $year,
            'collapse' => FALSE,
            'competitions' => $competitions
        ]);
    }

    /**
     * @param $year string the year to show the final-scores view with non-placing scores hidden
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View the repeater view to be rendered by Twig
     */
    public static function hallOfFame ($year) {
        $year = strval(intval($year) - 1);
        $competitions = self::getCompetitions($year, 'final');

        return view('scoreboard/final-scores', [
            'year' => $year,
            'collapse' => TRUE,
            'competitions' => $competitions
        ]);
    }

    /**
     * Select all competitions for a specified additional chunk $rest and bindings $bindings
     *
     * TODO Only called by getCompetitions
     * TODO Can we merge the two methods?
     *
     * @param $rest     string  any additional constraints or sorting to be applied to the statement
     * @param $bindings array   bindings for any wildcards or user input
     * @return          array   an array of competitions that match the year and status specified
     */
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

    /**
     * Get all competitions for a given year, status, and $byPlace sorting boolean
     *
     * @param $year                 string the year to get competitions for
     * @param $status               string the status of the competitions to get (waiting, active, final)
     * @param bool|TRUE $byPlace
     * @return array
     */
    public static function getCompetitions ($year, $status, $byPlace = TRUE) {
        return self::selectCompetitions(
            "WHERE competitions.year = :year AND competitions.status = :status " .
            "ORDER BY competitions.ampm ASC, competitions.name ASC, " .
            ($byPlace ? "teams.place DESC, " : "") . "teams.score DESC",
            ['year'=> $year, 'status' => $status]
        );
    }

    /**
     * Get a competition from its id
     *
     * @param $id
     * @return mixed
     */
    public static function getCompetition ($id) {
        return DB::table('competitions')
            ->where('id', $id)
            ->orderBy('name', 'asc');
    }
}
