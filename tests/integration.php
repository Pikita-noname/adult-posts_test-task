<?php

use App\Database\Connection;
use App\Http\BlogController;
use App\Http\NotFound;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\View\View;

$config = require dirname(__DIR__) . '/bootstrap.php';
$testDatabase = $_ENV['TEST_DB_NAME'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_]+_test$/D', $testDatabase)) {
    fwrite(STDERR, "Set TEST_DB_NAME to a separate existing database ending in _test. Fixtures are rolled back.\n");
    exit(1);
}
$config['db']['name'] = $testDatabase;
$db = Connection::create($config['db']);
$db->exec(file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
    echo "PASS: $message\n";
};
$notFound = static function (callable $action) use ($check): void {
    try {
        $action();
    } catch (NotFound $exception) {
        $check(true, 'Invalid resource or page returns NotFound');
        return;
    }
    throw new RuntimeException('Expected NotFound');
};

$db->beginTransaction();
try {
    $prefix = 'test-' . bin2hex(random_bytes(6));
    $categoryInsert = $db->prepare('INSERT INTO categories (slug, name, description) VALUES (?, ?, ?)');
    $categoryIds = [];
    foreach (['main', 'shared', 'empty', 'scheduled'] as $name) {
        $categoryInsert->execute([$prefix . '-' . $name, $name, '<script>alert("category")</script>']);
        $categoryIds[$name] = (int) $db->lastInsertId();
    }
    $insert = $db->prepare('INSERT INTO posts (slug, title, image, description, body, views, published_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $link = $db->prepare('INSERT INTO post_category (post_id, category_id) VALUES (?, ?)');
    $postIds = [];
    for ($i = 0; $i < 12; $i++) {
        $insert->execute([
            $prefix . '-' . $i, '<script>alert("title")</script>', '/assets/images/culture.svg',
            '<b>Description</b>', "<script>alert('body')</script>\n\nSecond paragraph",
            100 - $i, sprintf('2020-01-%02d 12:00:00', $i + 1),
        ]);
        $postIds[$i] = (int) $db->lastInsertId();
        $link->execute([$postIds[$i], $categoryIds['main']]);
        if ($i < 3) {
            $link->execute([$postIds[$i], $categoryIds['shared']]);
        }
    }
    $insert->execute([$prefix . '-future', 'Future', '/assets/images/travel.svg', '', '', 99999, '2999-01-01 00:00:00']);
    $futureId = (int) $db->lastInsertId();
    $link->execute([$futureId, $categoryIds['main']]);
    $link->execute([$futureId, $categoryIds['scheduled']]);

    $categories = new CategoryRepository($db);
    $posts = new PostRepository($db);
    $visibleCategories = array_column($categories->withPosts(), 'id');
    $check(in_array($categoryIds['main'], $visibleCategories), 'Home includes populated categories');
    $check(!in_array($categoryIds['empty'], $visibleCategories), 'Home excludes empty categories');
    $check(!in_array($categoryIds['scheduled'], $visibleCategories), 'Home excludes categories with future posts only');
    $check($posts->countForCategory($categoryIds['main']) === 12, 'Category count excludes future posts');
    $check(array_column($posts->forCategory($categoryIds['main']), 'id') === [$postIds[11], $postIds[10], $postIds[9]], 'Home returns exactly three latest articles');
    $check(array_column($posts->forCategory($categoryIds['main'], 'views'), 'id') === [$postIds[0], $postIds[1], $postIds[2]], 'Popularity sort uses view counters');
    $check(array_column($posts->forCategory($categoryIds['main'], 'date', 9, 9), 'id') === [$postIds[2], $postIds[1], $postIds[0]], 'Last page contains remaining articles');
    $check(count($categories->forPost($postIds[0])) === 2, 'An article can belong to multiple categories');
    $check(array_column($posts->related($postIds[0]), 'id') === [$postIds[2], $postIds[1], $postIds[11]], 'Three related posts rank by shared categories, exclude current/future posts and duplicates');
    $check($posts->findBySlug($prefix . '-future') === null, 'Future article is unavailable directly');
    $check($posts->findBySlug("' OR 1=1 --") === null, 'Slug lookup treats SQL injection as data');
    $check(array_column($posts->forCategory($categoryIds['main'], 'views; DROP TABLE posts'), 'id') === [$postIds[11], $postIds[10], $postIds[9]], 'Unknown sorting cannot inject SQL');

    $view = new View(dirname(__DIR__), ['name' => 'Test blog', 'debug' => true]);
    $controller = new BlogController($categories, $posts, $view);
    $category = $controller->category($prefix . '-main', ['sort' => 'views', 'page' => '2']);
    $check(substr_count($category, '<article class="post-card">') === 3, 'Category template renders the last page');
    $check(str_contains($category, '?sort=views&amp;page=1'), 'Pagination preserves sorting');
    $check(!str_contains($category, '<script>') && str_contains($category, '&lt;script&gt;'), 'Category text and titles are escaped');
    $check(str_contains($controller->category($prefix . '-empty', []), 'пока нет статей'), 'Empty category renders an empty state');
    foreach (['0', '-1', '3', '1.5', '999999999999999999999999', ['2']] as $page) {
        $notFound(fn () => $controller->category($prefix . '-main', ['page' => $page]));
    }
    $notFound(fn () => $controller->category($prefix . '-missing', []));
    $notFound(fn () => $controller->post($prefix . '-missing'));
    $notFound(fn () => $controller->post($prefix . '-future'));
    $controller->post($prefix . '-0', false);
    $check((int) $posts->findBySlug($prefix . '-0')['views'] === 100, 'HEAD rendering does not increment views');
    $article = $controller->post($prefix . '-0');
    $check((int) $posts->findBySlug($prefix . '-0')['views'] === 101, 'Opening article increments views once');
    $check(str_contains($article, 'Просмотры: 101'), 'Article displays updated view counter');
    $check(!str_contains($article, '<script>') && str_contains($article, '&lt;script&gt;'), 'Article body is escaped');
    $check(substr_count($article, '<article class="post-card">') === 3, 'Article renders three related cards');
    $check(str_contains($article, $prefix . '-shared') && str_contains($article, $prefix . '-main'), 'Article links all its categories');
    echo "\n$checks checks passed.\n";
} finally {
    $db->rollBack();
}
