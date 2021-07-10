<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', ['as' => 'home', 'uses' => 'IndexController@index']);
$router->post('login', ['as' => 'login', 'uses' => 'LoginController@login']);
$router->get('logout', ['as' => 'logout', 'uses' => 'LoginController@logout']);

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('dashboard', ['as' => 'admin.dashboard', 'uses' => 'IndexController@dashboard']);
    $router->group(['prefix' => 'bot'], function () use ($router) {
        $router->get('/', ['as' => 'admin.bot.index', 'uses' => 'BotController@index']);
        $router->post('set-webhook', ['as' => 'admin.bot.setWebhook', 'uses' => 'BotController@setWebhook']);
    });
});
