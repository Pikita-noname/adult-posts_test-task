<?php

use App\Http\BlogController;
use App\Http\Router;

/** @var Router $router */
$router->get('/', [BlogController::class, 'home']);
$router->get('/category/{slug}', [BlogController::class, 'category']);
$router->get('/post/{slug}', [BlogController::class, 'post']);
