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
        'final' => "finalized",
        'delete' => "deleted"
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
            'messages' =>$messages,
            'edit' => $toEdit
        ]);
    }

    /**
     * All input (get/post) is accessed with Input::get()
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addComp()
    {
        $year = date('Y');

        $messages = array();

        $action = Input::get('action');
        $original = (int)(Input::get('original'));
        $name = Input::get('name');
        $ampm = Input::get('ampm');
        $competitions = Input::get('competitions');

        if ($action === 'add' && !empty($name) && ($ampm === 'am' || $ampm === 'pm')) {
            // Add a new competition
            Competition::insert([
                'name' => $name,
                'year' => $year,
                'ampm' => $ampm,
                'status' => 'waiting'
            ]);

            $messages[] = ['type' => 'success', 'message' => $this->$name . " &ndash; " . $this->strtoupper($ampm) .
                " has been added as a competition."];

        } else if ($action === 'edit' && $original > 0 && !empty($name) && ($ampm === 'am' || $ampm === 'pm')) {
            // Update a competition
            Competition::where('id', $original)
                ->update([
                    'name' => $name,
                    'ampm' => $ampm
                ]);

            $messages[] = ['type' => 'success', 'message' => $this->$name . " &ndash; " . $this->strtoupper($ampm) .
                " has been edited."];

        } else if (!empty($action) && array_key_exists($action, $this->actions) && !empty($competitions) && is_array($competitions)) {
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
                        $comps->update(array('status'=> 'active'));
                        break;
                    case 'final':
                        $comps->update(array('status'=> 'final'));
                        break;
                    case 'delete':
                        $comps->delete();
                }
            }
            $messages[] = ['type' => 'success', 'message' => "The selected competitions have been " .
                $this->actions[$action] . "."];
        }

        return $this->getAdmin($messages);
    }

    public function showCompetitionTeams($id, $messages = null)
    {
        if ($id == null)
            return view('admin/error', ['message' => 'Invalid Competition ID.']);

        $competition = $this->getCompetition($id);

        if($competition == null)
            return view('admin/error', ['message' => 'That competition does not exist.']);

        return view('admin/competition', [
            'competition' => $competition,
            'messages' => $messages
        ]);
    }

    public function editCompetitionTeams($competitionId)
    {
        if ($competitionId == null)
            return view('admin/error', ['message' => 'Invalid Competition ID.']);

        $competition = $this->getCompetition($competitionId);
        if($competition == null)
            return view('admin/error', ['message' => 'That competition does not exist.']);

        $action = Input::get('action');
        $delete = Input::get('delete');
        $toUpdate = Input::get('update');
        $toInsert = Input::get('names');

        $messages = array();

        if (!empty($delete)) {
            $id = (int)($delete);
            if ($id > 0) {
                Team::where('id', $id)->delete();
            }
            $messages[] = ['type' => 'success', 'message' => "The team has been deleted."];

        } else if ($action === 'save' && is_array($toUpdate)) {
            // update ALL THE TEAMS!
            foreach ($_POST['update'] as $textID => $submitted) {
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

        } else if ($action === 'add' && !empty($toInsert)) {
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
        }

        return $this->showCompetitionTeams($competitionId, $messages);
    }

    private function getCompetition($id)
    {
        return Competition::where('id', $id)->orderBy('name', 'asc')->first();
    }
}
