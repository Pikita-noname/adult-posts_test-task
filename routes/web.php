<?php

use App\Controller\BlogController;
use App\Http\Router;

/** @var Router $router */
$router->get('/', [BlogController::class, 'index']);
$router->get('/category/{slug}', [BlogController::class, 'category']);
$router->get('/post/{slug}', [BlogController::class, 'detail']);
