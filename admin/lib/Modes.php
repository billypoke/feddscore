<?php

class FEDD_Modes {
    public static function repeater ($year) {
        $competitions = FEDD_Queries::getCompetitions($year, 'active', FALSE);

        Tuffy_Template::display('scoreboard/repeater.html', array(
            'year' => $year,
            'autoreload' => APP_DEBUG ? empty($_GET['noreloading']) : FALSE,
            'competitions' => $competitions
        ));
        
    }

    public static function finalScores ($year) {
        $competitions = FEDD_Queries::getCompetitions($year, 'final');

        Tuffy_Template::display('scoreboard/final-scores.html', array(
            'year' => $year,
            'collapse' => FALSE,
            'competitions' => $competitions
        ));
    }

    public static function hallOfFame ($year) {
        $year = $year - 1;
        $competitions = FEDD_Queries::getCompetitions($year, 'final');

        Tuffy_Template::display('scoreboard/final-scores.html', array(
            'year' => $year,
            'collapse' => TRUE,
            'competitions' => $competitions
        ));
    }
}

