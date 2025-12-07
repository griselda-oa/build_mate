<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Category model
 */
class Category extends Model
{
    protected string $table = 'categories';
    
    /**
     * Find by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }
}

