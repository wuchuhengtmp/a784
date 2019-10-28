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
    $router->resource('answers', AnswersController::class);
    $router->resource('journal-logs', JournalAccountController::class);
    $router->resource('feedback', FeedbackController::class);
    $router->resource('system-message-details', SystemMessageDetailsController::class);
    $router->resource('top-search-orders', TopSearchOrdersController::class);
    $router->resource('configs', ConfigsController::class);
    $router->resource('statement', StatementController::class);
    $router->resource('withdraw', withDrawController::class); 
    $router->resource('user-messages', UserMessagesController::class);
});
