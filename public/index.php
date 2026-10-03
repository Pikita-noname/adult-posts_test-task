<?php

use App\Database\Connection;
use App\Http\BlogController;
use App\Http\NotFound;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\View\View;

$root = dirname(__DIR__);
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');

try {
    $config = require $root . '/bootstrap.php';
    $view = new View($root, [
        'name' => $_ENV['APP_NAME'] ?? 'Adult Posts',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    ]);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, ['GET', 'HEAD'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD');
        echo $view->render('errors/message.tpl', ['title' => 'Метод не поддерживается', 'message' => 'Откройте страницу обычным переходом по ссылке.']);
        exit;
    }

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (!is_string($path)) {
        throw new NotFound();
    }
    $route = [];
    if ($path !== '/' && !preg_match('~^/(category|post)/([a-z0-9]+(?:-[a-z0-9]+)*)/?$~D', $path, $route)) {
        throw new NotFound();
    }

    $db = Connection::create($config['db']);
    $controller = new BlogController(new CategoryRepository($db), new PostRepository($db), $view);
    if ($path === '/') {
        $html = $controller->home();
    } elseif ($route[1] === 'category') {
        $html = $controller->category($route[2], $_GET);
    } else {
        $html = $controller->post($route[2], $method === 'GET');
    }
    if ($method !== 'HEAD') {
        echo $html;
    }
} catch (NotFound $exception) {
    http_response_code(404);
    echo $view->render('errors/404.tpl', ['title' => 'Страница не найдена']);
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);
    // Keep the fallback independent of the database and template cache.
    echo '<!doctype html><html lang="ru"><meta charset="utf-8"><title>Ошибка сервера</title>';
    echo '<h1>Не удалось загрузить страницу</h1><p>Попробуйте ещё раз немного позже.</p><a href="/">На главную</a></html>';
}
