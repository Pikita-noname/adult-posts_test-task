<?php

namespace App\Service;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;

class BlogService
{
    private const PAGE_SIZE = 9;

    private CategoryRepository $categoryRepository;
    private PostRepository $postRepository;

    public function __construct(CategoryRepository $categoryRepository, PostRepository $postRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
    }

    public function index(): array
    {
        $categories = [];
        foreach ($this->categoryRepository->withPosts() as $category) {
            $category['posts'] = $this->postRepository->forCategory((int)$category['id']);
            $categories[] = $category;
        }

        return ['categories' => $categories];
    }

    public function category(string $slug, string $sort, int $page): ?array
    {
        $category = $this->categoryRepository->findBySlug($slug);
        if ($category === null || $page < 1) {
            return null;
        }

        $categoryId = (int)$category['id'];
        $total = $this->postRepository->countForCategory($categoryId);
        $pages = max(1, (int)ceil($total / self::PAGE_SIZE));
        if ($page > $pages) {
            return null;
        }

        $sort = $sort === 'views' ? 'views' : 'date';
        $offset = ($page - 1) * self::PAGE_SIZE;
        $posts = $this->postRepository->forCategory($categoryId, $sort, self::PAGE_SIZE, $offset);

        return [
            'category' => $category,
            'posts' => $posts,
            'sort' => $sort,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'pageNumbers' => range(max(1, $page - 2), min($pages, $page + 2)),
        ];
    }

    public function detail(string $slug, bool $countView = true): ?array
    {
        $post = $this->postRepository->findBySlug($slug);
        if ($post === null) {
            return null;
        }

        $postId = (int)$post['id'];
        if ($countView) {
            $this->postRepository->incrementViews($postId);
            $post['views']++;
        }

        return [
            'post' => $post,
            'categories' => $this->categoryRepository->forPost($postId),
            'paragraphs' => preg_split('/\R\s*\R/u', $post['body']) ?: [$post['body']],
            'related' => $this->postRepository->related($postId),
        ];
    }
}
