<?php

namespace App\Controllers;

use App\Models\Todo;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as V;

class TodoController extends Controller
{
    public function index()
    {
        $todos = Todo::all();

        return $this->response->withJson($todos);
    }

    public function store(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();

        $validator = $this->todoCreateUpdateValidator($request_data);

        if ($validator->fails()) {
            return $response->withJson(['error' => $validator->getErrors()], 422);
        }

        $todo = Todo::create($request_data);

        return $response->withJson(['data' => $todo], 201);
    }

    public function update(Request $request, Response $response, Todo $todo)
    {
        $request_data = $request->getParsedBody();

        $validator = $this->todoCreateUpdateValidator($request_data);

        if ($validator->fails()) {
            return $response->withJson(['error' => $validator->getErrors()], 422);
        }

        $todo->update($request_data);

        return $response->withJson(['data' => $todo], 200);
    }

    public function updateAll(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();

        $validator = $this->validator->validate($request_data, [
            'completed' => V::notOptional()->boolType(),
        ], [
            'completed.notOptional' => 'The completed field is required',
            'completed.boolType' => 'The completed field must be a boolean',
        ]);

        if ($validator->fails()) {
            return $response->withJson(['error' => $validator->getErrors()], 422);
        }

        Todo::query()->update(['completed' => $request_data['completed']]);

        return $response->withJson(['data' => 'Update was successful'], 200);
    }

    public function delete(Request $request, Response $response, Todo $todo)
    {
        $todo->delete();

        return $response->withJson([], 204);
    }

    public function deleteCompleted(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();

        $validator = $this->validator->validate($request_data, [
            'todos' => V::arrayType()->notEmpty()->each(
                V::intVal()->databaseExists($this->db, 'todos', 'id', function ($query) {
                    $query->where('completed', true);
                })
            )
        ], [
            'todos.notEmpty' => 'The todos ids to delete is required',
            'todos.arrayType' => 'Todos must be an array',
            'todos.intVal' => 'Each of the todo id should be an integer',
            'todos.databaseExists' => 'One of the todo ids is invalid'
        ]);

        if ($validator->fails()) {
            return $response->withJson(['error' => $validator->getErrors()], 422);
        }

        Todo::destroy($request_data['todos']);

        return $response->withJson(null, 204);
    }

    private function todoCreateUpdateValidator(array $data)
    {
        return $this->validator->validate($data, [
            'title' => V::notEmpty()->stringType(),
            'completed' => V::notOptional()->boolType()
        ], [
            'title.notEmpty' => 'The title is required',
            'completed.notOptional' => 'The completed field is required',
            'title.stringType' => 'The title must be a string',
            'completed.boolVal' => 'The completed field must be a boolean',
        ]);
    }
}
