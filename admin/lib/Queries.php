<?php

class FEDD_Queries {
    public static function countForYear ($year) {
        $stmt = Tuffy_Database::$db->query(
            "SELECT COUNT(*) AS `count` FROM `fedd_competitions` " .
            "WHERE `year` = ?", array($year)
        );
        return (int)$stmt->fetchColumn();
    }

    public static function selectCompetitions ($rest, $bindings) {
        $query = "SELECT competitions.id as competition_id, " .
                        "competitions.name, competitions.ampm, " .
                        "competitions.year, competitions.status, " .
                        "teams.id as team_id, teams.name as team_name, " .
                        "teams.score, teams.place, teams.disqualified " .
                     "FROM `fedd_competitions` AS competitions " .
                          "LEFT JOIN `fedd_teams` AS teams " .
                          "ON(teams.competition_id = competitions.id) " . $rest;
        $stmt = Tuffy_Database::$db->query($query, $bindings);

        $comps = array();
        foreach ($stmt as $row) {
            $compID = $row['competition_id'];
            if (!array_key_exists($compID, $comps)) {
                $comps[$compID] = array('id' => $compID,
                                        'name' => $row['name'],
                                        'ampm' => $row['ampm'],
                                        'year' => $row['year'],
                                        'status' => $row['status'],
                                        'teams' => array());
            }
            if ($row['team_id'] !== NULL) {
                $comps[$compID]['teams'][] = array('id' => $row['team_id'],
                                                   'name' => $row['team_name'],
                                                   'score' => $row['score'],
                                                   'place' => $row['place'],
                                                   'disqualified' => $row['disqualified']);
            }
        }
        return $comps;
    }

    public static function getCompetitions ($year, $status, $byPlace = TRUE) {
        return self::selectCompetitions(
            "WHERE competitions.year = ? AND competitions.status = ? " .
            "ORDER BY competitions.ampm ASC, competitions.name ASC, " .
            ($byPlace ? "teams.place DESC, " : "") . "teams.score DESC",
            array($year, $status)
        );
    }

    public static function getCompetition ($id) {
        $comp = Tuffy_Database::$db->query(
            "SELECT id, name, ampm, year, status FROM `fedd_competitions` " .
            "WHERE `id` = ? ", array($id)
        )->fetch();
        if ($comp) {
            $comp['teams'] = Tuffy_Database::$db->query(
                "SELECT id, name, score, place, disqualified " .
                "FROM `fedd_teams` WHERE `competition_id` = ? " .
                "ORDER BY name ASC", array($id)
            )->fetchAll();
        }
        return $comp;
    }
}

