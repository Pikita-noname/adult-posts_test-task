<?php

namespace App\Http;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\View\View;

final class BlogController
{
    private const PAGE_SIZE = 9;

    public function __construct(
        private CategoryRepository $categories,
        private PostRepository $posts,
        private View $view,
        private Request $request,
    ) {
    }

    public function home(): string
    {
        $categories = $this->categories->withPosts();
        foreach ($categories as &$category) {
            $category['posts'] = $this->posts->forCategory((int) $category['id']);
        }
        unset($category);

        return $this->view->render('home.tpl', [
            'title' => 'Истории и идеи на каждый день',
            'categories' => $categories,
        ]);
    }

    public function category(string $slug): string
    {
        $category = $this->categories->findBySlug($slug);
        if ($category === null) {
            throw new NotFound();
        }

        $query = $this->request->query;
        $sort = ($query['sort'] ?? '') === 'views' ? 'views' : 'date';
        $rawPage = $query['page'] ?? '1';
        $page = is_string($rawPage) ? filter_var($rawPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        if ($page === false) {
            throw new NotFound();
        }

        $total = $this->posts->countForCategory((int) $category['id']);
        $pages = max(1, (int) ceil($total / self::PAGE_SIZE));
        if ($page > $pages) {
            throw new NotFound();
        }

        return $this->view->render('category.tpl', [
            'title' => $category['name'],
            'category' => $category,
            'posts' => $this->posts->forCategory((int) $category['id'], $sort, self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE),
            'sort' => $sort,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'pageNumbers' => range(max(1, $page - 2), min($pages, $page + 2)),
        ]);
    }

    public function post(string $slug): string
    {
        $post = $this->posts->findBySlug($slug);
        if ($post === null) {
            throw new NotFound();
        }

        $postId = (int) $post['id'];
        if ($this->request->method === 'GET') {
            $this->posts->incrementViews($postId);
            $post['views']++;
        }

        return $this->view->render('post.tpl', [
            'title' => $post['title'],
            'post' => $post,
            'categories' => $this->categories->forPost($postId),
            'paragraphs' => preg_split('/\R\s*\R/u', $post['body']) ?: [$post['body']],
            'related' => $this->posts->related($postId),
        ]);
    }
}
