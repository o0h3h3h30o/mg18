<?php

namespace App\Controllers;

class Sitemap extends BaseController
{
    /**
     * Sitemap Index - lists all sub-sitemaps
     * /sitemap.xml
     */
    public function index()
    {
        $this->response->setHeader('Content-Type', 'application/xml; charset=UTF-8');

        // Count chapters to determine how many chapter sitemaps needed
        $totalChapters = $this->db->table('chapter')
            ->join('manga', 'manga.id = chapter.manga_id')
            ->where('manga.is_public', 1)
            ->where('chapter.is_show', 1)
            ->countAllResults();

        $chapterPages = (int) ceil($totalChapters / 10000);
        $lastMod = date('c');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Static pages sitemap
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>https://manga18.club/sitemap-pages.xml</loc>' . "\n";
        $xml .= '    <lastmod>' . $lastMod . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";

        // Manga sitemap
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>https://manga18.club/sitemap-manga.xml</loc>' . "\n";
        $xml .= '    <lastmod>' . $lastMod . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";

        // Category sitemap
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>https://manga18.club/sitemap-category.xml</loc>' . "\n";
        $xml .= '    <lastmod>' . $lastMod . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";

        // Chapter sitemaps (paginated, 40k URLs each - Google limit is 50k)
        for ($i = 0; $i < $chapterPages; $i++) {
            $xml .= '  <sitemap>' . "\n";
            $xml .= '    <loc>https://manga18.club/sitemap-chapter-' . $i . '.xml</loc>' . "\n";
            $xml .= '    <lastmod>' . $lastMod . '</lastmod>' . "\n";
            $xml .= '  </sitemap>' . "\n";
        }

        $xml .= '</sitemapindex>';

        return $this->response->setBody($xml);
    }

    /**
     * Static pages sitemap
     * /sitemap-pages.xml
     */
    public function pages()
    {
        $this->response->setHeader('Content-Type', 'application/xml; charset=UTF-8');

        $urls = [
            ['loc' => 'https://manga18.club/',             'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => 'https://manga18.club/list-manga',   'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => 'https://manga18.club/latest-release','priority' => '0.9', 'changefreq' => 'hourly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $u) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $u['loc'] . '</loc>' . "\n";
            $xml .= '    <changefreq>' . $u['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $u['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $this->response->setBody($xml);
    }

    /**
     * All manga sitemap
     * /sitemap-manga.xml
     */
    public function manga()
    {
        $this->response->setHeader('Content-Type', 'application/xml; charset=UTF-8');

        $mangas = $this->db->table('manga')
            ->select('slug, update_at')
            ->where('is_public', 1)
            ->orderBy('update_at', 'DESC')
            ->get()->getResult();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($mangas as $m) {
            // update_at can be Unix timestamp (int) or datetime string
            if ($m->update_at && is_numeric($m->update_at)) {
                $lastmod = date('c', (int) $m->update_at);
            } elseif ($m->update_at) {
                $lastmod = date('c', strtotime($m->update_at));
            } else {
                $lastmod = date('c');
            }
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>https://manga18.club/manhwa/' . htmlspecialchars($m->slug) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $this->response->setBody($xml);
    }

    /**
     * Category sitemap
     * /sitemap-category.xml
     */
    public function category()
    {
        $this->response->setHeader('Content-Type', 'application/xml; charset=UTF-8');

        $categories = $this->db->table('category')
            ->select('slug')
            ->orderBy('name', 'ASC')
            ->get()->getResult();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($categories as $c) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>https://manga18.club/manga-list/' . htmlspecialchars($c->slug) . '</loc>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.6</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $this->response->setBody($xml);
    }

    /**
     * Chapter sitemap (paginated)
     * /sitemap-chapter-0.xml, /sitemap-chapter-1.xml, ...
     */
    public function chapter($page = 0)
    {
        $this->response->setHeader('Content-Type', 'application/xml; charset=UTF-8');

        $page = (int) $page;
        $perPage = 10000;
        $offset = $page * $perPage;

        $chapters = $this->db->table('chapter c')
            ->select('c.slug as chapter_slug, c.created_at, m.slug as manga_slug')
            ->join('manga m', 'm.id = c.manga_id')
            ->where('m.is_public', 1)
            ->where('c.is_show', 1)
            ->orderBy('c.id', 'DESC')
            ->limit($perPage, $offset)
            ->get()->getResult();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($chapters as $ch) {
            $lastmod = $ch->created_at ? date('c', strtotime($ch->created_at)) : date('c');
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>https://manga18.club/manhwa/' . htmlspecialchars($ch->manga_slug) . '/' . htmlspecialchars($ch->chapter_slug) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.6</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $this->response->setBody($xml);
    }
}
