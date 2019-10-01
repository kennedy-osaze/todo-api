<?php

namespace App\Controllers;

use App\Models\User;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as V;

class AuthController extends Controller
{
    public function register(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();

        $validator = $this->registerValidator($request_data);

        if ($validator->fails()) {
            return $response->withJson(['errors' => $validator->getErrors()], 422);
        }

        $user = User::create([
            'name' => ucwords($request_data['name']),
            'email' => $request_data['email'],
            'password' => password_hash($request_data['password'], PASSWORD_BCRYPT)
        ]);

        return $response->withJson(['data' => $user->toArray()], 201);
    }

    private function registerValidator(array $data)
    {
        $data += ['password' => ''];

        return $this->validator->validate($data, [
            'name' => V::stringType()->notEmpty()->length(3, 255, true),
            'email' => V::notEmpty()->email()->length(null, 255, true)->not(
                V::databaseExists($this->db, 'users', 'email', function ($query) {
                    $query->whereNull('deleted_at');
                })
            ),
            'password' => V::stringType()->notEmpty()->length(8, null, true),
            'password_confirmation' => V::notEmpty()->identical($data['password'])
        ], [
            'name.notEmpty' => 'The name is required',
            'name.length' => 'The name must be at least 3 characters and at most 255 characters long',
            'email.notEmpty' => 'The email address is required',
            'email.length' => 'The email address must be at most 255 characters long',
            'email.databaseExists' => 'The email address already exist',
            'email.email' => 'The email address is not valid',
            'password.notEmpty' => 'The password is required',
            'password.length' => 'The password must be at least 8 characters long',
            'password_confirmation.notEmpty' => 'The password is required',
            'password_confirmation.identical' => 'The password does not match'
        ]);
    }

    public function login(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();

        $validator = $this->loginValidator($request_data);
        
        if ($validator->fails()) {
            return $response->withJson(['errors' => $validator->getErrors()], 422);
        }

        $credentials = array_filter($request_data, function ($key) {
            return in_array($key, ['email', 'password']);
        }, ARRAY_FILTER_USE_KEY);

        if (! ($token = $this->auth->attempt($credentials))) {
            return $response->withJson(['error' => 'Invalid login credentials'], 401);
        }

        $user = $this->auth->user();

        return $response->withJson(['data' => compact('token', 'user')], 200);
    }

    private function loginValidator(array $data)
    {
        return $this->validator->validate($data, [
            'email' => V::notEmpty()->email(),
            'password' => V::stringType()->notEmpty()
        ], [
            'email.notEmpty' => 'The email address is required',
            'email.email' => 'The email address is not valid',
            'password.notEmpty' => 'The password is required'
        ]);
    }
}
