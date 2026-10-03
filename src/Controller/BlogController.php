<?php

namespace App\Controller;

use App\Http\NotFound;
use App\Http\Request;
use App\Service\BlogService;
use App\View\View;

class BlogController
{
    private BlogService $blogService;
    private View $view;
    private Request $request;

    public function __construct(BlogService $blogService, View $view, Request $request)
    {
        $this->blogService = $blogService;
        $this->view = $view;
        $this->request = $request;
    }

    public function index(): string
    {
        $data = $this->blogService->index();
        $data['title'] = 'Истории и идеи на каждый день';

        return $this->view->render('home.tpl', $data);
    }

    public function category(string $slug): string
    {
        $sort = ($this->request->query['sort'] ?? '') === 'views' ? 'views' : 'date';
        $data = $this->blogService->category($slug, $sort, $this->page());
        if ($data === null) {
            throw new NotFound('Category or page not found');
        }

        $data['title'] = $data['category']['name'];

        return $this->view->render('category.tpl', $data);
    }

    public function detail(string $slug): string
    {
        $data = $this->blogService->detail($slug, $this->request->method === 'GET');
        if ($data === null) {
            throw new NotFound('Post not found');
        }

        $data['title'] = $data['post']['title'];

        return $this->view->render('post.tpl', $data);
    }

    private function page(): int
    {
        $page = $this->request->query['page'] ?? '1';
        if (!is_string($page)) {
            throw new NotFound('Invalid page number');
        }

        $page = filter_var($page, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($page === false) {
            throw new NotFound('Invalid page number');
        }

        return $page;
    }
}
