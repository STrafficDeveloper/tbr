<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

final class SeoController extends Controller
{
    /**
     * Only public, indexable pages. Drafts, member pages and search results
     * stay out; winners pages appear once their announcement date passes.
     */
    public function sitemap(Request $request): never
    {
        $urls = [];
        $add = static function (string $path, ?string $lastmod = null) use (&$urls): void {
            $urls[] = ['loc' => url($path), 'lastmod' => $lastmod === null ? null : date('Y-m-d', (int) strtotime($lastmod))];
        };

        foreach (['/', '/port-rider', '/pit-stop', '/pit-stop/daftar', '/aktiviti/event', '/aktiviti/galeri',
                  '/aktiviti/peraduan', '/aktiviti/pemenang', '/panas-atas-jalan'] as $path) {
            $add($path);
        }

        foreach (Database::select("SELECT DISTINCT state FROM port_riders WHERE status = 'published' ORDER BY state") as $row) {
            $add('/port-rider?negeri=' . rawurlencode((string) $row['state']));
        }

        $sources = [
            ["SELECT slug, updated_at FROM pitstop_events WHERE status IN ('published', 'closed')", '/pit-stop/'],
            ["SELECT slug, updated_at FROM galleries WHERE status = 'published'", '/aktiviti/galeri/'],
            ["SELECT slug, updated_at FROM contests WHERE status = 'published'", '/aktiviti/peraduan/'],
            ["SELECT c.slug, c.updated_at FROM contests c
              WHERE c.status = 'published' AND (c.announce_on IS NULL OR c.announce_on <= CURDATE())
                AND EXISTS (SELECT 1 FROM contest_winners w WHERE w.contest_id = c.id)", '/aktiviti/pemenang/'],
        ];

        foreach ($sources as [$sql, $prefix]) {
            foreach (Database::select($sql) as $row) {
                $add($prefix . rawurlencode((string) $row['slug']), (string) $row['updated_at']);
            }
        }

        foreach (Database::select("SELECT slug, updated_at FROM hof_profiles WHERE status = 'published'") as $row) {
            $base = '/hall-of-fame/' . rawurlencode((string) $row['slug']);
            foreach (['', '/konten', '/galeri'] as $tab) {
                $add($base . $tab, (string) $row['updated_at']);
            }
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $entry) {
            echo '  <url><loc>' . htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>'
                . ($entry['lastmod'] !== null ? '<lastmod>' . $entry['lastmod'] . '</lastmod>' : '')
                . "</url>\n";
        }

        echo "</urlset>\n";
        exit;
    }

    /** Staging and local copies ask crawlers to stay away entirely. */
    public function robots(Request $request): never
    {
        header('Content-Type: text/plain; charset=utf-8');

        if (Config::get('app.env') !== 'production') {
            echo "User-agent: *\nDisallow: /\n";
            exit;
        }

        echo "User-agent: *\n"
            . "Disallow: /admin\n"
            . "\n"
            . 'Sitemap: ' . url('/sitemap.xml') . "\n";
        exit;
    }
}
