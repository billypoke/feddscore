<?php

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

    Route::group(['prefix' => 'admin/'], function() {
        Route::get('/', ['as' => 'admin', 'uses' => 'AdminController@getAdmin']);
        Route::post('/', ['as' => 'admin', 'uses' => 'AdminController@addComp']);
    });

    Route::group(['prefix' => 'competition/'], function() {
        Route::get('{id?}', ['as' => 'competition', 'uses' => 'AdminController@showCompetitionTeams']);
        Route::post('{id?}', ['as' => 'competition', 'uses' => 'AdminController@editCompetitionTeams']);
    });
});
