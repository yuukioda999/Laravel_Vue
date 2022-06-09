<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Support\Str;
use App\Photo;

$factory->define(App\Photo::class, function (Faker $faker) {
    return [
        'user_id' => fn() => factory(App\User::class)->create()->id,
        'file_path' => Str::random(12) . '.jpg',
        'created_at' => $faker->dateTime(),
        'updated_at' => $faker->dateTime(),
    ];
});
