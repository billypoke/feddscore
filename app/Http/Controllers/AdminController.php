<?php

namespace FeddScore\Http\Controllers;

use FeddScore\Competition;
use FeddScore\Team;
use FeddScore\Http\Requests;
use Illuminate\Support\Facades\Input;

class AdminController extends Controller
{

    private $actions = array(
        'waiting' => "marked as waiting",
        'active' => "activated",
        'final' => "finalized"
    );

    private $places = array(
        'first',
        'second',
        'third',
        'honorable'
    );

    /**
     * @param array $messages
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAdmin($messages = NULL)
    {
        $year = date('Y');

        $comps = Competition::where('year', $year)
            ->orderBy('ampm', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $toEditID = (int)Input::get('edit');

        if ($toEditID > 0) {
            $toEdit = $this->getCompetition($toEditID);
        } else {
            $toEdit = NULL;
        }

        return view('admin/index', [
            'competitions' => $comps,
            'messages' => $messages,
            'edit' => $toEdit
        ]);
    }

    public function addCompetition()
    {
        $year = date('Y');

        $messages = array();

        $name = Input::get('name');
        $ampm = Input::get('ampm');

        if (!empty($name) && ($ampm === 'am' || $ampm === 'pm')) {
            // Add a new competition
            Competition::insert([
                'name' => $name,
                'year' => $year,
                'ampm' => $ampm,
                'status' => 'waiting'
            ]);

            $messages[] = ['type' => 'success', 'message' => $name . " &ndash; " . strtoupper($ampm) .
                " has been added as a competition."];
        } else {
            $messages[] = ['type' => 'success', 'message' => 'ERROR: Name and time are required'];
        }

        return $this->getAdmin($messages);
    }

    public function renameCompetition()
    {
        $original = (int)(Input::get('original'));
        $name = Input::get('name');
        $ampm = Input::get('ampm');
        $messages = array();

        if ($original > 0 && !empty($name) && ($ampm === 'am' || $ampm === 'pm')) {
            // Update a competition
            Competition::where('id', $original)
                ->update([
                    'name' => $name,
                    'ampm' => $ampm
                ]);

            $messages[] = ['type' => 'success', 'message' => $name . " &ndash; " . strtoupper($ampm) .
                " has been edited."];

        } else {
            $messages[] = ['type' => 'success', 'message' => 'ERROR: A new name and time are required'];
        }

        return $this->getAdmin($messages);
    }

    public function editCompetition($action)
    {
        $competitions = Input::get('competitions');
        $messages = array();

        if (!empty($action) && array_key_exists($action, $this->actions) && !empty($competitions) && is_array($competitions)) {
            // Something else
            $ids = array();
            foreach ($competitions as $competitionID) {
                $id = (int)$competitionID;
                if ($id < 1) {
                    $ids = NULL;
                    break;
                }
                $ids[] = $id;
            }

            if ($ids) {

                $comps = Competition::whereIn('id', $ids);

                switch ($action) {
                    case 'waiting':
                        $comps->update(array('status' => 'waiting'));
                        break;
                    case 'active':
                        $comps->update(array('status' => 'active'));
                        break;
                    case 'final':
                        $comps->update(array('status' => 'final'));
                        break;
                    case 'delete':
                        $comps->delete();
                }
            }
            $messages[] = ['type' => 'success', 'message' => "The selected competitions have been " .
                $this->actions[$action] . "."];
        } else {
            $messages[] = ['type' => 'success', 'message' => "ERROR: The selected competitions were NOT " .
                $this->actions[$action] . "."];
        }

        return $this->getAdmin($messages);
    }

    public function deleteCompetition()
    {
        $competitions = Input::get('competitions');
        $messages = array();

        if ((Input::get('action') == 'delete') && !empty($competitions) && is_array($competitions)) {
            // Something else
            $ids = array();
            foreach ($competitions as $competitionID) {
                $id = (int)$competitionID;
                if ($id < 1) {
                    $ids = NULL;
                    break;
                }
                $ids[] = $id;
            }

            if ($ids) {
                $comps = Competition::whereIn('id', $ids);
                $comps->delete();
            }
            $messages[] = ['type' => 'success', 'message' => "The selected competitions have been deleted."];
        } else {
            $messages[] = ['type' => 'success', 'message' => "ERROR: The selected competitions were NOT deleted."];
        }

        return $this->getAdmin($messages);
    }

    public function showCompetitionTeams($id, $messages = null)
    {
        if ($id == null)
            return view('admin/error', ['message' => 'Invalid Competition ID.']);

        $competition = $this->getCompetition($id);

        if ($competition == null)
            return view('admin/error', ['message' => 'That competition does not exist.']);

        return view('admin/competition', [
            'competition' => $competition,
            'messages' => $messages
        ]);
    }

    public function saveCompetitionTeams($competitionId)
    {
        if ($competitionId == null){
            return view('admin/error', ['message' => 'Invalid Competition ID.']);
        }

        $competition = $this->getCompetition($competitionId);
        if ($competition == null){
            return view('admin/error', ['message' => 'That competition does not exist.']);
        }

        $toUpdate = Input::get('update');

        if (is_array($toUpdate)) {
            // update ALL THE TEAMS!
            foreach ($toUpdate as $textID => $submitted) {
                if (!is_array($submitted)) continue;
                // ID
                $id = (int)($textID);
                if ($id < 1) continue;
                // Name
                if (empty($submitted['name'])) continue;
                $data['name'] = $submitted['name'];
                // Score
                $score = array_key_exists('score', $submitted) ? $submitted['score'] : '';
                if ($score === '') {
                    $data['score'] = NULL;
                } else if (is_numeric($score)) {
                    $data['score'] = (int)($score);
                } else {
                    continue;
                }
                // Place
                $data['place'] = in_array($submitted['place'], $this->places)
                    ? $submitted['place'] : NULL;
                // DQ
                $data['disqualified'] = !empty($submitted['dq']);

                Team::where('id', $id)
                    ->update($data);
            }
            $messages[] = ['type' => 'success', 'message' => "The team data has been saved."];

        } else {
            $messages[] = ['type' => 'success', 'message' => "ERROR: The teams were not updated."];
        }

        return $this->showCompetitionTeams($competitionId, $messages);
    }

    public function addCompetitionTeams($competitionId)
    {
        if ($competitionId == null){
            return view('admin/error', ['message' => 'Invalid Competition ID.']);
        }

        $competition = $this->getCompetition($competitionId);
        if ($competition == null){
            return view('admin/error', ['message' => 'That competition does not exist.']);
        }

        $toInsert = Input::get('names');

        $messages = array();

        if (!empty($toInsert)) {
            $names = explode("\n", trim($toInsert));

            foreach ($names as $name) {
                $name = trim($name);
                if (!empty($name)) {
                    Team::insert([
                        'competition_id' => $competitionId,
                        'name' => $name
                    ]);
                }
            }
            $messages[] = ['type' => 'success', 'message' => "The new teams have been added."];
        } else {
            $messages[] = ['type' => 'success', 'message' => "ERROR: No team names were provided."];
        }

        return $this->showCompetitionTeams($competitionId, $messages);
    }

    public function deleteCompetitionTeams($competitionId)
    {
        if ($competitionId == null){
            return view('admin/error', ['message' => 'Invalid Competition ID.']);
        }

        $competition = $this->getCompetition($competitionId);
        if ($competition == null){
            return view('admin/error', ['message' => 'That competition does not exist.']);
        }

        $delete = Input::get('delete');

        if (!empty($delete)) {
            $id = (int)($delete);
            if ($id > 0) {
                Team::where('id', $id)->delete();
            }
            $messages[] = ['type' => 'success', 'message' => "The team has been deleted."];

        } else {
            $messages[] = ['type' => 'success', 'message' => "ERROR: The team was not deleted."];
        }

        return $this->showCompetitionTeams($competitionId, $messages);
    }

    private function getCompetition($id)
    {
        return Competition::where('id', $id)->orderBy('name', 'asc')->first();
    }
}
