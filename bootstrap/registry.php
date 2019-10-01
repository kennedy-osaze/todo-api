<?php

use Slim\App;
use App\Models\User;
use App\Models\Todo;

return function (App $app) {
    $container = $app->getContainer();

    $container['route-bindings'] = [
        'user' => User::class,
        'todo' => Todo::class,

        // 'user' => function ($value) {
        //     return User::where('id', $value)->first();
        // },
    ];
};
