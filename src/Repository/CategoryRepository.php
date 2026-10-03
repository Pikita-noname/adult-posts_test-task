<?php

namespace App\Repository;

use PDO;

class CategoryRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function withPosts(): array
    {
        return $this->db->query(
            'SELECT c.* FROM categories c
             WHERE EXISTS (
                 SELECT 1 FROM post_category pc
                 JOIN posts p ON p.id = pc.post_id
                 WHERE pc.category_id = c.id AND p.published_at <= CURRENT_TIMESTAMP
             )
             ORDER BY c.name, c.id'
        )->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM categories WHERE slug = ?');
        $statement->execute([$slug]);

        return $statement->fetch() ?: null;
    }

    public function forPost(int $postId): array
    {
        $statement = $this->db->prepare(
            'SELECT c.* FROM categories c
             JOIN post_category pc ON pc.category_id = c.id
             WHERE pc.post_id = ? ORDER BY c.name, c.id'
        );
        $statement->execute([$postId]);

        return $statement->fetchAll();
    }
}
