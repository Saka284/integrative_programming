<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('api/login', 'AuthController@login');   

$router->group(['prefix' => 'api', 'middleware' => 'auth'], function () use ($router) {

    //Semua user login
    $router->get('me', 'AuthController@me');
    $router->post('logout', 'AuthController@logout');

    //Hanya Admin
    $router->group(['middleware' => 'role:admin'], function () use ($router) {
        $router->get('users', 'UserController@index');
        $router->post('users', 'UserController@store');
        $router->get('users/{id}', 'UserController@show');
        $router->put('users/{id}', 'UserController@update');
        $router->delete('users/{id}', 'UserController@destroy');
    });

    //Semua user login bisa lihat post/komentar
    $router->get('posts', 'PostController@index');
    $router->get('posts/{id}', 'PostController@show');
    $router->get('comments', 'CommentController@index');
    $router->get('comments/{id}', 'CommentController@show');

    //Penulis, editor, admin (bisa nulis & update)
    $router->group(['middleware' => 'role:penulis,editor,admin'], function () use ($router) {
        $router->post('posts', 'PostController@store');
        $router->put('posts/{id}', 'PostController@update');
        $router->patch('posts/{id}', 'PostController@updatePartial');

        $router->post('comments', 'CommentController@store');
        $router->put('comments/{id}', 'CommentController@update');
        $router->patch('comments/{id}', 'CommentController@updatePartial');
    });

    //Editor & admin bisa hapus
    $router->group(['middleware' => 'role:editor,admin'], function () use ($router) {
        $router->delete('posts/{id}', 'PostController@destroy');
        $router->delete('comments/{id}', 'CommentController@destroy');
    });
});
