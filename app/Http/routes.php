<?php

use \Illuminate\Support\Facades\Input;

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {


    Route::group(['prefix' => 'dashboard/{year?}'], function() {
        Route::get('/', [
            'as' => 'dashboard',
            'uses' => 'DashboardController@getCurrent'
        ]);

        # /dashboard/2016/advert
        Route::get('advert', ['as' => 'dashboard.advert', 'uses' => 'DashboardController@getAdvert']);
        Route::get('repeater', ['as' => 'dashboard.repeater', 'uses' => 'DashboardController@getRepeater']);
        Route::get('final', ['as' => 'dashboard.final', 'uses' => 'DashboardController@getFinal']);
        Route::get('hall-of-fame', ['as' => 'dashboard.hof', 'uses' => 'DashboardController@getHallOfFame']);
    });

    Route::group(['middleware' => ['auth.shib']], function () {
        Route::group(['prefix' => 'admin/'], function() {
            Route::get('/', ['as' => 'admin', 'uses' => 'AdminController@getAdmin']);

            Route::post('/', ['as' => 'competition', function() {
                $action = Input::get('action');
                $adminController = new \FeddScore\Http\Controllers\AdminController();

                switch ($action) {
                    case 'add': return $adminController->addCompetition();
                    case 'edit': return $adminController->renameCompetition();
                    case 'waiting':
                    case 'active':
                    case 'final': return $adminController->editCompetition($action);
                    case 'delete': return $adminController->deleteCompetition();
                }

                return $adminController->getAdmin();
            }]);
            Route::post('/add', ['as' => 'competition.add', 'uses' => 'AdminController@addCompetition']);
            Route::post('/edit', ['as' => 'competition.edit', 'uses' => 'AdminController@editCompetition']);
            Route::post('/rename', ['as' => 'competition.rename', 'uses' => 'AdminController@renameCompetition']);
            Route::post('/delete', ['as' => 'competition.delete', 'uses' => 'AdminController@deleteCompetition']);
        });

        Route::group(['prefix' => 'competition/'], function() {
            Route::get('{id?}', ['as' => 'competition', 'uses' => 'AdminController@showCompetitionTeams']);
            Route::post('{id?}', ['as' => 'teams', function($competitionId) {
                $action = Input::get('action');
                $adminController = new \FeddScore\Http\Controllers\AdminController();

                switch ($action) {
                    case 'add': return $adminController->addCompetitionTeams($competitionId);
                    case 'save': return $adminController->saveCompetitionTeams($competitionId);
                }

                if (!empty(Input::get('delete'))) {
                    return $adminController->deleteCompetitionTeams($competitionId);
                }

                return $adminController->showCompetitionTeams($competitionId);
            }]);
            Route::post('{id?}/edit', ['as' => 'teams.add', 'uses' => 'AdminController@saveCompetitionTeams']);
            Route::post('{id?}/add', ['as' => 'teams.add', 'uses' => 'AdminController@addCompetitionTeams']);
            Route::post('{id?}/delete', ['as' => 'teams.delete', 'uses' => 'AdminController@deleteCompetitionTeams']);
        });
    });
});
