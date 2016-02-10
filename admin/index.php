<?php

require('lib/tuffy/Init.php');

$year = date('Y');

$action_queries = array(
    'waiting'   => "UPDATE `fedd_competitions` SET `status` = 'waiting'",
    'active'    => "UPDATE `fedd_competitions` SET `status` = 'active'",
    'final'     => "UPDATE `fedd_competitions` SET `status` = 'final'",
    'delete'    => "DELETE FROM `fedd_competitions`"
);

$action_past_participles = array(
    'waiting'   => "marked as waiting",
    'active'    => "activated",
    'final'     => "finalized",
    'delete'    => "deleted"
);


if (REQUEST_METHOD === 'POST') {
    $action = maybe($_POST, 'action');
    $original = (int)(maybe($_POST, 'original'));
    $name = maybe($_POST, 'name');
    $ampm = maybe($_POST, 'ampm');
    $competitions = maybe($_POST, 'competitions');

    if ($action === 'add' && !empty($name) && ($ampm === 'am' || $ampm === 'pm')) {
        // Add a new competition
        Tuffy_Database::$db->exec(
            "INSERT INTO `fedd_competitions`(`name`, `year`, `ampm`, `status`) " .
                        "VALUES (?, ?, ?, 'waiting')",
            array($name, $year, $ampm)
        );
        Tuffy::flash('success', esc($name) . " &ndash; " . esc(strtoupper($ampm)) .
                     " has been added as a competition.");

    } else if ($action === 'edit' && $original > 0 && !empty($name) &&
               ($ampm === 'am' || $ampm === 'pm')) {
        // Update a competition
        Tuffy_Database::$db->exec(
            "UPDATE `fedd_competitions` SET `name` = ?, `ampm` = ? " .
                   "WHERE `id` = ?",
            array($name, $ampm, $original)
        );
        Tuffy::flash('success', esc($name) . " &ndash; " . esc(strtoupper($ampm)) .
                     " has been updated.");

    } else if (!empty($action) && array_key_exists($action, $action_queries) &&
               !empty($competitions) && is_array($competitions)) {
        // Something else
        $ids = array();
        foreach ($competitions as $competitionID) {
            $id = (int)$competitionID;
            if ($id < 1) {
                $ids = NULL;
                break;
            }
            $ids[] = Tuffy_Database::$db->quote($id);
        }

        if ($ids) {
            $query = $action_queries[$action] . " WHERE `id` IN (" .
                     implode(', ', $ids) . ")";
            Tuffy_Database::$db->exec($query);
        }
        Tuffy::flash('success', "The selected competitions have been " .
                     $action_past_participles[$action] . ".");
    }
}

$comps = Tuffy_Database::$db->query(
    "SELECT * FROM `fedd_competitions` WHERE `year` = ? " .
    "ORDER BY `ampm` ASC, `name` ASC", array($year)
);


$toEditID = (int)(maybe($_GET, 'edit'));

if ($toEditID > 0) {
    $toEdit = FEDD_Queries::getCompetition($toEditID);
} else {
    $toEdit = NULL;
}


Tuffy_Template::display('admin/index.html', array(
    'competitions' => $comps, 'edit' => $toEdit
));

