<?php

require('lib/tuffy/Init.php');

$year = date('Y');
$competitionID = (int)(maybe($_GET, 'id'));

if ($competitionID < 1) die("that is not a valid ID");

$competition = FEDD_Queries::getCompetition($competitionID);

if ($competition === NULL) {
    Tuffy_Template::display('admin/error.html', array('message' => "That competition does not exist."));
    throw new Tuffy_Exit();
}


$places = array('first', 'second', 'third', 'honorable');


if (REQUEST_METHOD === 'POST') {
    $db = Tuffy_Database::$db;
    $action = maybe($_POST, 'action');
    if (!empty($_POST['delete'])) {
        $id = (int)($_POST['delete']);
        if ($id > 0) {
            $db->exec("DELETE FROM `fedd_teams` WHERE `id` = ?", array($id));
        }
        Tuffy::flash('success', "The team has been deleted.");

    } else if ($action === 'save' && is_array($_POST['update'])) {
        // update ALL THE TEAMS!
        $stmt = $db->prepare("UPDATE `fedd_teams` SET `name` = :name, " .
                             "`score` = :score, `place` = :place, " .
                             "`disqualified` = :dq WHERE `id` = :id");
        foreach ($_POST['update'] as $textID => $submitted) {
            if (!is_array($submitted)) continue;
            // ID
            $id = (int)($textID);
            if ($id < 1) continue;
            $data = array('id' => $id);
            // Name
            if (empty($submitted['name'])) continue;
            $data['name'] = $submitted['name'];
            // Score
            $score = maybe($submitted, 'score', '');
            if ($score === '') {
                $data['score'] = NULL;
            } else if (is_numeric($score)) {
                $data['score'] = (int)($score);
            } else {
                continue;
            }
            // Place
            $data['place'] = in_array($submitted['place'], $places)
                           ? $submitted['place'] : NULL;
            // DQ
            $data['dq'] = !empty($submitted['dq']);
            $stmt->execute($data);
        }
        Tuffy::flash('success', "The team data has been saved.");
    
    } else if ($action === 'add' && !empty($_POST['names'])) {
        $stmt = $db->prepare("INSERT INTO `fedd_teams` (`competition_id`, " .
                             "`name`) VALUES (?, ?)");
        $names = explode("\n", trim($_POST['names']));

        foreach ($names as $name) {
            $name = trim($name);
            if (!empty($name)) {
                $stmt->execute(array($competitionID, $name));
            }
        }
        Tuffy::flash('success', "The new teams have been added.");
    }

    $competition = FEDD_Queries::getCompetition($competitionID);
}


Tuffy_Template::display('admin/competition.html', array(
    'competition' => $competition
));

