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

    $router->group(['prefix' => 'menus'], function () use ($router) {
        $router->get('/', ['as' => 'admin.menus.index', 'uses' => 'MenuController@index']);
        $router->post('/store', ['as' => 'admin.menus.store', 'uses' => 'MenuController@store']);
        $router->put('/update/{id}', ['as' => 'admin.menus.update', 'uses' => 'MenuController@update']);
        $router->delete('/destroy/{id}', ['as' => 'admin.menus.destroy', 'uses' => 'MenuController@destroy']);
    });

    $router->group(['prefix' => 'cafes'], function () use ($router) {
        $router->get('/', ['as' => 'admin.cafes.index', 'uses' => 'CafeController@index']);
        $router->post('/store', ['as' => 'admin.cafes.store', 'uses' => 'CafeController@store']);
        $router->put('/update/{id}', ['as' => 'admin.cafes.update', 'uses' => 'CafeController@update']);
        $router->delete('/destroy/{id}', ['as' => 'admin.cafes.destroy', 'uses' => 'CafeController@destroy']);
    });

    $router->group(['prefix' => 'locations'], function () use ($router) {
        $router->get('/', ['as' => 'admin.locations.index', 'uses' => 'LocationNameController@index']);
        $router->post('/store', ['as' => 'admin.locations.store', 'uses' => 'LocationNameController@store']);
        $router->put('/update/{id}', ['as' => 'admin.locations.update', 'uses' => 'LocationNameController@update']);
        $router->delete('/destroy/{id}', ['as' => 'admin.locations.destroy', 'uses' => 'LocationNameController@destroy']);
    });

    $router->group(['prefix' => 'clients'], function () use ($router) {
        $router->get('/', ['as' => 'admin.clients.index', 'uses' => 'ClientController@index']);
        $router->put('/update/{id}', ['as' => 'admin.clients.update', 'uses' => 'ClientController@update']);
        $router->delete('/destroy/{id}', ['as' => 'admin.clients.destroy', 'uses' => 'ClientController@destroy']);
    });

    $router->group(['prefix' => 'orders'], function () use ($router) {
        $router->get('/', ['as' => 'admin.orders.index', 'uses' => 'OrderController@index']);
        $router->put('/update/{id}', ['as' => 'admin.orders.update', 'uses' => 'OrderController@update']);
        $router->delete('/destroy/{id}', ['as' => 'admin.orders.destroy', 'uses' => 'OrderController@destroy']);
    });

    $router->group(['prefix' => 'feedback'], function () use ($router) {
        $router->get('/', ['as' => 'admin.feedback.index', 'uses' => 'FeedbackController@index']);
        $router->put('/update', ['as' => 'admin.feedback.update', 'uses' => 'FeedbackController@update']);
    });

});
