<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class GalleryRepository
{
    /** @return list<array<string,mixed>> albums in admin-set order, each with its photo count */
    public function published(): array
    {
        return Database::select(
            'SELECT g.id, g.title, g.slug, g.description, g.cover_image, g.likes_count, g.views_count, g.published_at,
                    (SELECT COUNT(*) FROM gallery_images i WHERE i.gallery_id = g.id) AS images_count,
                    (SELECT i.path FROM gallery_images i WHERE i.gallery_id = g.id ORDER BY i.sort_order, i.id LIMIT 1) AS first_image
             FROM galleries g
             WHERE g.status = ?
             ORDER BY g.sort_order, g.published_at DESC',
            ['published'],
        );
    }

    /** @return array<string,mixed>|null */
    public function findPublishedBySlug(string $slug): ?array
    {
        return Database::selectOne(
            'SELECT id, title, slug, description, cover_image, published_at
             FROM galleries WHERE slug = ? AND status = ? LIMIT 1',
            [$slug, 'published'],
        );
    }

    /** @return list<array<string,mixed>> */
    public function images(int $galleryId): array
    {
        return Database::select(
            'SELECT id, path, alt_text, caption FROM gallery_images WHERE gallery_id = ? ORDER BY sort_order, id',
            [$galleryId],
        );
    }
}
