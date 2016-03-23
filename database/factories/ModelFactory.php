<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->defineAs(FeddScore\Competition::class, 'active', function (Faker\Generator $faker) {
    return [
        'name' => $faker->company,
        'year' => '2016',
        'ampm' => 'am',
        'status' => 'active'
    ];
});

$factory->defineAs(FeddScore\Competition::class, 'final', function (Faker\Generator $faker) {
    return [
        'name' => $faker->company,
        'year' => '2016',
        'ampm' => 'am',
        'status' => 'final'
    ];
});

$factory->defineAs(FeddScore\Competition::class, 'waiting', function (Faker\Generator $faker) {
    return [
        'name' => $faker->company,
        'year' => '2016',
        'ampm' => 'am',
        'status' => 'waiting'
    ];
});

$factory->define(FeddScore\Team::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->company,
        'score' => random_int(0,10),
        'place' => null,
        'disqualified' => 0
    ];
});