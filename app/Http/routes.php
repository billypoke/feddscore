<?php

use \FeddScore\Competition;

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

    Route::get('/', function () {
        return redirect()->route('dashboard.advert', ['year' => date("Y")]);
    });

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
            Route::match(['get', 'post'], '/', ['as' => 'admin', 'uses' => 'AdminController@getAdmin']);

            Route::post('/add', ['as' => 'competition.add', 'uses' => 'AdminController@addCompetition']);
            Route::post('/edit', ['as' => 'competition.edit', 'uses' => 'AdminController@editCompetition']);
            Route::post('/rename', ['as' => 'competition.rename', 'uses' => 'AdminController@renameCompetition']);
            Route::post('/delete', ['as' => 'competition.delete', 'uses' => 'AdminController@deleteCompetition']);
        });

        Route::group(['prefix' => 'competition/{id?}'], function($id) {
            if ($id == null){
                return view('admin/error', ['message' => 'Invalid Competition ID.']);
            }

            $competition = Competition::where('id', $id);
            if ($competition == null){
                return view('admin/error', ['message' => 'That competition does not exist.']);
            }

            Route::match(['get', 'post'], '/', ['as' => 'competition', 'uses' => 'AdminController@showCompetitionTeams']);

            Route::post('/edit', ['as' => 'teams.save', 'uses' => 'AdminController@saveCompetitionTeams', $id]);
            Route::post('/add', ['as' => 'teams.add', 'uses' => 'AdminController@addCompetitionTeams', $id]);
            Route::post('/delete', ['as' => 'teams.delete', 'uses' => 'AdminController@deleteCompetitionTeams', $id]);
        });
    });
});
