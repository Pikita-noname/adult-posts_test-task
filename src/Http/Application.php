<?php

namespace App\Http;

use App\Controller\BlogController;
use App\Database\Connection;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Service\BlogService;
use App\View\View;
use LogicException;
use Throwable;

final class Application
{
    private string $root;

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    public function run(): void
    {
        $request = Request::capture();
        header('Content-Type: text/html; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        try {
            $config = require $this->root . '/bootstrap.php';
            $view = new View($this->root, [
                'name' => $_ENV['APP_NAME'] ?? 'Adult Posts',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);
            $html = $this->handle($request, $config, $view);
        } catch (Throwable $exception) {
            error_log((string) $exception);
            http_response_code(500);
            $html = '<!doctype html><html lang="ru"><meta charset="utf-8"><title>Ошибка сервера</title>'
                . '<h1>Не удалось загрузить страницу</h1><p>Попробуйте ещё раз немного позже.</p>'
                . '<a href="/">На главную</a></html>';
        }

        if ($request->method !== 'HEAD') {
            echo $html;
        }
    }

    private function handle(Request $request, array $config, View $view): string
    {
        if (!in_array($request->method, ['GET', 'HEAD'], true)) {
            http_response_code(405);
            header('Allow: GET, HEAD');

            return $view->render('errors/message.tpl', [
                'title' => 'Метод не поддерживается',
                'message' => 'Откройте страницу обычным переходом по ссылке.',
            ]);
        }

        $router = new Router();
        require $this->root . '/routes/web.php';

        try {
            $route = $router->match($request->path);
            [$controllerClass, $action] = $route['handler'];
            $db = Connection::create($config['db']);
            $blogService = new BlogService(
                new CategoryRepository($db),
                new PostRepository($db),
            );
            $controllers = [
                BlogController::class => new BlogController($blogService, $view, $request),
            ];
            $controller = $controllers[$controllerClass] ?? throw new LogicException('Unknown controller: ' . $controllerClass);

            return $controller->$action(...array_values($route['parameters']));
        } catch (NotFound $exception) {
            http_response_code(404);

            return $view->render('errors/404.tpl', ['title' => 'Страница не найдена']);
        }
    }
}
