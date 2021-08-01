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

    $router->group(['prefix' => 'product-categories'], function () use ($router) {
        $router->get('/', ['as' => 'admin.productCategories.index', 'uses' => 'ProductCategoryController@index']);
        $router->post('/store', ['as' => 'admin.productCategories.store', 'uses' => 'ProductCategoryController@store']);
        $router->put('/update/{id}', ['as' => 'admin.productCategories.update', 'uses' => 'ProductCategoryController@update']);
        $router->delete('/destroy/{id}', ['as' => 'admin.productCategories.destroy', 'uses' => 'ProductCategoryController@destroy']);
    });

    $router->group(['prefix' => 'products'], function () use ($router) {
        $router->get('/', ['as' => 'admin.products.index', 'uses' => 'ProductController@index']);
        $router->post('/store', ['as' => 'admin.products.store', 'uses' => 'ProductController@store']);
        $router->put('/update/{id}', ['as' => 'admin.products.update', 'uses' => 'ProductController@update']);
        $router->delete('/destroy/{id}', ['as' => 'admin.products.destroy', 'uses' => 'ProductController@destroy']);
    });

});
