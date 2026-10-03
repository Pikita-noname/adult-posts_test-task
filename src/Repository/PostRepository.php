<?php

namespace App\Repository;

use PDO;

class PostRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function countForCategory(int $categoryId): int
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) FROM posts p
             JOIN post_category pc ON pc.post_id = p.id
             WHERE pc.category_id = ? AND p.published_at <= CURRENT_TIMESTAMP'
        );
        $statement->execute([$categoryId]);

        return (int)$statement->fetchColumn();
    }

    public function forCategory(int $categoryId, string $sort = 'date', int $limit = 3, int $offset = 0): array
    {
        // Only fixed SQL fragments can become part of ORDER BY.
        $order = match ($sort) {
            'views' => 'p.views DESC, p.published_at DESC, p.id DESC',
            default => 'p.published_at DESC, p.id DESC',
        };
        $statement = $this->db->prepare(
            'SELECT p.id, p.slug, p.title, p.image, p.description, p.views, p.published_at
             FROM posts p JOIN post_category pc ON pc.post_id = p.id
             WHERE pc.category_id = :category AND p.published_at <= CURRENT_TIMESTAMP
             ORDER BY ' . $order . ' LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':category', $categoryId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM posts WHERE slug = ? AND published_at <= CURRENT_TIMESTAMP'
        );
        $statement->execute([$slug]);

        return $statement->fetch() ?: null;
    }

    public function incrementViews(int $postId): void
    {
        $statement = $this->db->prepare('UPDATE posts SET views = views + 1 WHERE id = ?');
        $statement->execute([$postId]);
    }

    public function related(int $postId): array
    {
        // Count shared categories to rank matches; GROUP BY prevents duplicates.
        $statement = $this->db->prepare(
            'SELECT p.id, p.slug, p.title, p.image, p.description, p.views, p.published_at,
                    COUNT(*) AS shared_categories
             FROM posts p
             JOIN post_category candidate ON candidate.post_id = p.id
             JOIN post_category source ON source.category_id = candidate.category_id
             WHERE source.post_id = :source AND p.id <> :current
               AND p.published_at <= CURRENT_TIMESTAMP
             GROUP BY p.id
             ORDER BY shared_categories DESC, p.published_at DESC, p.id DESC
             LIMIT 3'
        );
        $statement->execute(['source' => $postId, 'current' => $postId]);

        return $statement->fetchAll();
    }
}
