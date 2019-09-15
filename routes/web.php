<?php

use Slim\App;
use App\Controllers\TodoController;

return function (App $app) {
    $app->get('/todos', TodoController::class . ':index');

    $app->post('/todos', TodoController::class . ':store');

    $app->put('/todos', TodoController::class . ':updateAll');

    $app->put('/todos/{todo}', TodoController::class . ':update');

    $app->delete('/todos/completed', TodoController::class . ':deleteCompleted');

    $app->delete('/todos/{todo}', TodoController::class . ':delete');
};
