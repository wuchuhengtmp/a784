<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('members', MembersController::class);
    $router->resource('levels', LevelsController::class);
    $router->resource('videos', videosController::class);
    $router->resource('articles', ArticlesController::class);
});
