<?php

use Slim\App;
use App\Middleware\Authenticate;
use App\Controllers\TodoController;
use App\Controllers\AuthController;

return function (App $app) {
    $app->post('/register', AuthController::class . ':register');

    $app->post('/login', AuthController::class . ':login');

    $app->group('', function () use ($app) {
        $app->get('/todos', TodoController::class . ':index');

        $app->post('/todos', TodoController::class . ':store');

        $app->put('/todos', TodoController::class . ':updateAll');

        $app->put('/todos/{todo}', TodoController::class . ':update');

        $app->delete('/todos/completed', TodoController::class . ':deleteCompleted');

        $app->delete('/todos/{todo}', TodoController::class . ':delete');
    })->add(new Authenticate($app->getContainer()));
};
