<?php

require('admin/lib/tuffy/Init.php');

// Mode Select
$mode = APP_DEBUG ? maybe($_GET, 'forceMode') : NULL;
$year = APP_DEBUG ? maybe($_GET, 'forceYear', date('Y')) : date('Y');


if (!$mode) {
    $countThisYear = FEDD_Queries::countForYear($year);

    if ($countThisYear > 0) {
        // Dispatch based on date.
        $feddDate = FEDD_Helpers::computeDate($year);
        $feddDayNumber = (int)($feddDate->format('z'));
        $thisDayNumber = (int)(APP_DEBUG ? maybe($_GET, 'forceDay', date('z'))
                                         : date('z'));

        if ($feddDayNumber === $thisDayNumber) {
            $mode = 'repeater';
        } else if ($thisDayNumber > $feddDayNumber) {
            $mode = 'final';
        } else if ($thisDayNumber < $feddDayNumber) {
            $mode = 'advert';
        }
    } else {
        $mode = 'halloffame';
    }
}

switch ($mode) {
    case "repeater":
        FEDD_Modes::repeater($year);
        break;

    case "final":
        FEDD_Modes::finalScores($year);
        break;

    case "halloffame":
        FEDD_Modes::hallOfFame($year);
        break;

    case "advert":
        Tuffy_Template::display('scoreboard/advertisement.html', array(
            'year' => $year
        ));
        break;

    default:
        echo "what the heck?";
        break;
}

