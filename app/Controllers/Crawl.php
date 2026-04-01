<?php

namespace App\Controllers;

use voku\helper\HtmlDomParser;

/**
 * Crawl Controller - Refactored
 * Requires: composer require voku/simple_html_dom
 *
 * is_crawling: 0 = Done, 1 = Crawling, 2 = Need crawl
 */
class Crawl extends \CodeIgniter\Controller
{
    protected $db;
    protected string $savePath;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->savePath = config('Manga')->savePath;
    }

    // =========================================================================
    // PUBLIC ENDPOINTS
    // =========================================================================

    /**
     * Crawl latest manga from manga18fx (pages 1-2)
     * Creates new manga + inserts chapters with source_url
     */
    public function index()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');

        for ($paged = 1; $paged <= 2; $paged++) {
            echo "-------- TRANG {$paged} --------\n";

            $mangaList = $this->getLatestManga("https://manga18fx.com/page/{$paged}");
            echo "Found " . count($mangaList) . " manga\n";

            foreach ($mangaList as $idx => $item) {
                $num = $idx + 1;

                // Chỉ xử lý manga có adult-badges (18+)
                if (!($item['is_18'] ?? 0)) {
                    echo "[{$num}] SKIP (not 18+): {$item['title']}\n";
                    continue;
                }

                $sourceUrl = 'https://manga18fx.com' . $item['source'];
                $existingManga = $this->findMangaByLink($sourceUrl);

                echo "\n[{$num}] {$item['title']} [18+] | Last: {$item['last_chapter']}\n";

                if (!$existingManga) {
                    echo "  => NEW manga\n";
                    $this->crawlNewManga($item, $sourceUrl, $paged);
                } else {
                    echo "  => EXISTS: #{$existingManga->id} {$existingManga->name} (ch1={$existingManga->chapter_1})\n";
                    $this->crawlNewChaptersForManga($existingManga, $item, $paged);
                }
            }
        }
        echo "\nDone.\n";
    }

    /**
     * Crawl chapters that need crawling (is_crawling = 2)
     * Downloads images from source_url and saves to local storage
     */
    public function crawlChapter()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');

        $items = $this->db->query(
            "SELECT * FROM chapter WHERE source_url != '' AND source_url IS NOT NULL AND is_show = 0 AND is_crawling = 0 ORDER BY id DESC LIMIT 20"
        )->getResult();

        if (empty($items)) {
            echo "No chapters to crawl.\n";
            return;
        }

        foreach ($items as $item) {
            $manga = $this->db->table('manga')->where('id', $item->manga_id)->get()->getRow();
            if (!$manga) {
                echo "Manga not found for chapter {$item->id}\n";
                continue;
            }

            // Double-check still needs crawl
            $check = $this->db->table('chapter')
                ->where('id', $item->id)
                ->where('is_crawling', 0)
                ->where('is_show', 0)
                ->countAllResults();
            if ($check < 1) {
                echo "Chapter {$item->id} already being crawled, skip.\n";
                continue;
            }

            echo "Crawling: {$manga->name} - {$item->name} ({$item->source_url})\n";

            if (str_contains($item->source_url, 'manga18')) {
                $this->crawlChapterFromManga18fx($item, $manga);
            } elseif (str_contains($item->source_url, 'mangadistrict')) {
                $this->crawlChapterFromMangaDistrict($item, $manga);
            } else {
                echo "Unknown source: {$item->source_url}\n";
            }

            sleep(5); // Rate limit between chapters
        }
        echo "\nDone.\n";
    }

    /**
     * Crawl chapters that have external pages (is_crawling = 2)
     * Downloads external images to local storage
     */
    public function crawlChapter2()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');

        $items = $this->db->table('chapter')
            ->where('is_show', 0)
            ->where('is_crawling', 2)
            ->orderBy('id', 'DESC')
            ->limit(20)
            ->get()->getResult();

        if (empty($items)) {
            echo "No chapters to crawl.\n";
            return;
        }

        foreach ($items as $item) {
            $manga = $this->db->table('manga')->where('id', $item->manga_id)->get()->getRow();
            if (!$manga) continue;

            // Double-check
            $check = $this->db->table('chapter')
                ->where('id', $item->id)
                ->where('is_crawling', 2)
                ->where('is_show', 0)
                ->countAllResults();
            if ($check < 1) {
                echo "Chapter {$item->id} already being crawled.\n";
                continue;
            }

            $pages = $this->db->table('page')
                ->where('chapter_id', $item->id)
                ->where('external', 1)
                ->get()->getResult();

            // No external pages in DB yet — parse source_url to create them
            if (empty($pages) && !empty($item->source_url)) {
                echo "  No pages in DB, parsing source_url: {$item->source_url}\n";
                $pages = $this->parseAndInsertExternalPages($item);
            }

            if (empty($pages)) {
                echo "Chapter {$item->id} has no external pages.\n";
                // Mark done so it doesn't keep retrying
                $this->db->table('chapter')->where('id', $item->id)->update(['is_crawling' => 0]);
                continue;
            }

            echo "Crawling external pages: {$manga->name} - {$item->name} ({$item->id})\n";

            // Set crawling
            $this->db->table('chapter')->where('id', $item->id)->update(['is_crawling' => 1]);

            // Prepare directory
            $chapterDir = $this->savePath . $manga->slug . '/chapters/' . $item->slug . '/';
            @mkdir($chapterDir, 0755, true);

            foreach ($pages as $page) {
                $imageUrl = $page->image;
                $ext = $this->getImageExtension($imageUrl);
                $pageName = str_pad($page->slug, 3, '0', STR_PAD_LEFT) . '.' . $ext;

                // Determine referer based on source
                $referer = 'https://manga18fx.com/';
                if (str_contains($imageUrl, 'newtoki') || str_contains($imageUrl, 'manatoki')) {
                    $referer = 'https://newtoki468.com/';
                }

                $rawdata = $this->fetchImageData($imageUrl, $referer);
                if ($rawdata) {
                    $finalName = $this->saveAndOptimizeImage($rawdata, $chapterDir, $pageName);
                    $this->db->table('page')->where('id', $page->id)->update([
                        'image'    => $finalName,
                        'external' => 0,
                    ]);
                    echo "  OK: Page {$finalName}\n";
                } else {
                    echo "  FAIL: Page {$pageName} ({$imageUrl})\n";
                }
            }

            // Done
            $this->db->table('chapter')->where('id', $item->id)->update([
                'is_crawling' => 0,
                'is_show'     => 1,
            ]);
            $this->updateMangaLatestChapters($item->manga_id);

            echo "  Completed chapter {$item->id}\n";
        }
        echo "\nDone.\n";
    }

    /**
     * Reset views: day (always) + month (if 1st of month)
     */
    public function resetView()
    {
        $this->db->query('UPDATE manga SET view_day = 0');
        echo "Reset view_day done.\n";

        if ((int)date('j') === 1) {
            $this->db->query('UPDATE manga SET view_month = 0');
            echo "Reset view_month done (1st of month).\n";
        }
    }

    /**
     * Crawl single manga from mangadistrict by URL
     * Usage: /crawl/mangadistrict?url=https://mangadistrict.com/series/cuck-thology/
     */
    public function mangadistrict()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');

        $sourceUrl = trim($this->request->getGet('url') ?? '');
        if (!$sourceUrl || !str_contains($sourceUrl, 'mangadistrict.com')) {
            echo "Usage: /crawl/mangadistrict?url=https://mangadistrict.com/series/xxx/\n";
            return;
        }

        echo "=== MangaDistrict Crawl ===\n";
        echo "URL: {$sourceUrl}\n\n";

        // Fetch page
        $html = $this->fetchUrl($sourceUrl, 'https://mangadistrict.com');
        if (!$html) {
            echo "FAILED: Could not fetch page.\n";
            return;
        }

        $dom = HtmlDomParser::str_get_html($html);
        $data = $this->parseMangaDistrictPage($dom);
        $chapters = $this->parseMangaDistrictChapters($dom, $sourceUrl);

        if (!$data['name']) {
            echo "FAILED: Could not parse manga name.\n";
            return;
        }

        echo "Title: {$data['name']}\n";
        echo "Alt: {$data['otherNames']}\n";
        echo "Author: {$data['author']} | Artist: {$data['artist']}\n";
        echo "Categories: " . implode(', ', $data['categories']) . "\n";
        echo "Chapters found: " . count($chapters) . "\n";
        echo "Cover: {$data['image']}\n\n";

        // Check if manga already exists (by source URL or name)
        $existingManga = $this->findMangaByLink($sourceUrl);
        if (!$existingManga) {
            $existingManga = $this->findMangaByName($data['name']);
        }

        if ($existingManga) {
            $mangaId = $existingManga->id;
            $slug = $existingManga->slug;

            // Append source URL if not already there
            $links = $existingManga->from_manga18fx ?? '';
            if (!str_contains($links, $sourceUrl)) {
                $links = rtrim($links, ',') . ',' . $sourceUrl . ',';
            }

            $this->db->table('manga')->where('id', $mangaId)->update([
                'from_manga18fx' => $links,
                '_authors'       => $data['author'],
                '_artists'       => $data['artist'],
                'summary'        => $data['summary'],
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            echo "=> Updated existing manga #{$mangaId} - {$existingManga->name}\n\n";
        } else {
            // Create new manga
            $slug = $this->slugify($data['name']);
            $existSlug = $this->db->table('manga')->where('slug', $slug)->countAllResults();
            if ($existSlug > 0) {
                $slug .= '-' . time();
            }

            $this->db->table('manga')->insert([
                'name'           => $data['name'],
                'otherNames'     => $data['otherNames'],
                'from_manga18fx' => $sourceUrl . ',',
                'is_public'      => 1,
                'cover'          => 1,
                'user_id'        => 1,
                'status_id'      => 1,
                'slug'           => $slug,
                '_authors'       => $data['author'],
                '_artists'       => $data['artist'],
                'summary'        => $data['summary'],
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            $mangaId = $this->db->insertID();

            // Download cover with mangadistrict referer
            if ($data['image']) {
                $this->downloadCoverFrom($slug, $data['image'], 'https://mangadistrict.com/');
            }

            // Insert categories
            $this->insertCategories($mangaId, $data['categories']);

            // Insert authors & artists
            if ($data['author']) $this->insertAuthorsArtists($mangaId, $data['author'], 1);
            if ($data['artist']) $this->insertAuthorsArtists($mangaId, $data['artist'], 2);

            echo "=> Created manga #{$mangaId} - {$data['name']} (slug: {$slug})\n\n";
        }

        // Insert chapters (is_crawling=0, is_show=0 → sẽ được crawlChapter() xử lý)
        $inserted = 0;
        $skipped = 0;
        foreach ($chapters as $chUrl) {
            $number = $this->extractChapterNumberFromMangaDistrict($chUrl);
            if ($number <= 0) {
                echo "  ? Skip (no number): {$chUrl}\n";
                continue;
            }

            // Check duplicate by number
            $exists = $this->db->table('chapter')
                ->where('number', $number)
                ->where('manga_id', $mangaId)
                ->countAllResults();
            if ($exists > 0) {
                $skipped++;
                continue;
            }

            $chSlug = 'chapter-' . str_replace('.', '-', $number);

            $this->db->table('chapter')->insert([
                'slug'        => $chSlug,
                'name'        => 'Chapter ' . $number,
                'number'      => $number,
                'volume'      => 0,
                'manga_id'    => $mangaId,
                'user_id'     => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
                'view'        => 0,
                'is_show'     => 0,
                'is_crawling' => 0,
                'source_url'  => $chUrl,
            ]);
            echo "  + Chapter {$number} ({$chUrl})\n";
            $inserted++;
        }

        // Update manga latest chapter info
        if ($inserted > 0) {
            $this->updateMangaLatestChapters($mangaId);
        }

        echo "\n=== Done ===\n";
        echo "Inserted: {$inserted} | Skipped (exists): {$skipped}\n";
        echo "Next: Run /crawl/crawlChapter to download images.\n";
    }

    /**
     * Parse mangadistrict manga detail page (by label, not index)
     */
    private function parseMangaDistrictPage($dom): array
    {
        $data = [
            'name' => '', 'otherNames' => '', 'author' => '',
            'artist' => '', 'summary' => '', 'image' => '', 'categories' => [],
        ];

        $titleEl = $dom->find('.post-title h1', 0);
        if ($titleEl) $data['name'] = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($titleEl->innertext))));

        // Parse by label instead of index
        $items = $dom->find('.post-content_item');
        foreach ($items as $item) {
            $heading = $item->find('.summary-heading h5', 0);
            if (!$heading) continue;
            $label = strtolower(trim($heading->text()));
            $content = $item->find('.summary-content', 0);
            if (!$content) continue;
            $val = trim(preg_replace('/\s+/', ' ', $content->text()));

            if (str_contains($label, 'alternative')) {
                $data['otherNames'] = $val;
            }
        }

        $author = $dom->find('.author-content', 0);
        if ($author) $data['author'] = trim(preg_replace('/\s+/', ' ', $author->text()));

        $artist = $dom->find('.artist-content', 0);
        if ($artist) $data['artist'] = trim(preg_replace('/\s+/', ' ', $artist->text()));

        $genres = $dom->find('.genres-content', 0);
        if ($genres) {
            $data['categories'] = array_filter(array_map('trim', explode(',', $genres->text())));
        }

        $summary = $dom->find('.summary__content', 0);
        if ($summary) $data['summary'] = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($summary->innerHtml()))));

        $img = $dom->find('.summary_image img', 0);
        if ($img) {
            $data['image'] = $img->getAttribute('data-src') ?: $img->getAttribute('src') ?: '';
        }

        return $data;
    }

    /**
     * Parse chapter list from mangadistrict
     * First tries HTML dom, if empty fetches via AJAX (Madara theme loads chapters dynamically)
     */
    private function parseMangaDistrictChapters($dom, string $sourceUrl = ''): array
    {
        // Try direct HTML first
        $chapters = $this->extractChapterLinks($dom);

        // If no chapters found, try AJAX endpoint (Madara theme)
        if (empty($chapters) && $sourceUrl) {
            echo "No chapters in HTML, trying AJAX...\n";

            // Extract manga post ID from page
            $postId = '';
            // Try data-id on manga container
            $mangaEl = $dom->find('.manga-page', 0) ?: $dom->find('#manga-chapters-holder', 0);
            if ($mangaEl) {
                $postId = $mangaEl->getAttribute('data-id') ?? '';
            }
            // Try wp shortlink or input hidden
            if (!$postId) {
                $shortlink = $dom->find('link[rel="shortlink"]', 0);
                if ($shortlink) {
                    $href = $shortlink->getAttribute('href') ?? '';
                    if (preg_match('/\?p=(\d+)/', $href, $m)) {
                        $postId = $m[1];
                    }
                }
            }

            if ($postId) {
                $ajaxUrl = 'https://mangadistrict.com/wp-admin/admin-ajax.php';
                $ajaxHtml = $this->fetchPost($ajaxUrl, [
                    'action' => 'manga_get_chapters',
                    'manga'  => $postId,
                ], 'https://mangadistrict.com');

                if ($ajaxHtml) {
                    $ajaxDom = HtmlDomParser::str_get_html($ajaxHtml);
                    $chapters = $this->extractChapterLinks($ajaxDom);
                    echo "AJAX returned " . count($chapters) . " chapters.\n";
                }
            } else {
                echo "Could not find manga post ID for AJAX chapter fetch.\n";
            }
        }

        return $chapters;
    }

    /**
     * Extract chapter links from DOM (shared between HTML and AJAX response)
     * Deduplicates by chapter number — mangadistrict has multiple versions (v1, v2) per chapter
     * Only keeps the first URL encountered per chapter number
     */
    private function extractChapterLinks($dom): array
    {
        $chapters = [];
        $links = $dom->find('li.wp-manga-chapter a');

        $seenUrl = [];
        $seenNumber = [];
        foreach ($links as $a) {
            $href = trim($a->href ?? '');
            if (!$href || isset($seenUrl[$href])) continue;
            $text = trim($a->text());
            if (!$text) continue;
            $seenUrl[$href] = true;

            // Extract the real chapter number (ignore version suffix like _1)
            $number = $this->extractChapterNumberFromMangaDistrict($href);
            if ($number <= 0) continue;

            // Only keep first URL per chapter number
            $numKey = (string) $number;
            if (isset($seenNumber[$numKey])) continue;
            $seenNumber[$numKey] = true;

            $chapters[] = $href;
        }
        return $chapters;
    }

    /**
     * Extract real chapter number from mangadistrict URL
     * URL pattern: .../chapter-165_1/ (the _1 is version, not sub-chapter)
     * We extract just the number after "chapter-"
     */
    private function extractChapterNumberFromMangaDistrict(string $url): float
    {
        // Get last meaningful path segment containing "chapter"
        $parts = explode('/', trim($url, '/'));
        $chapterPart = '';
        foreach (array_reverse($parts) as $part) {
            if (str_contains($part, 'chapter')) {
                $chapterPart = $part;
                break;
            }
        }
        if (!$chapterPart) return 0;

        // chapter-165_1 → extract 165 (ignore _1 version suffix)
        // chapter-12-5  → extract 12.5 (real sub-chapter with hyphen)
        if (preg_match('/chapter[_-]?(\d+)(?:[_-](\d+))?/', $chapterPart, $m)) {
            // If the URL has version path like /v1-.../chapter-165_1/
            // the _1 is the version, real number is just 165
            $hasVersionPath = (bool) preg_match('/\/v\d+[_-]/', $url);

            if ($hasVersionPath && isset($m[2])) {
                // Version URL: _1 is version suffix, ignore it
                return (float) $m[1];
            } elseif (isset($m[2])) {
                // No version path: treat as sub-chapter (e.g., chapter-12-5 = 12.5)
                return (float) ($m[1] . '.' . $m[2]);
            }
            return (float) $m[1];
        }

        return 0;
    }

    /**
     * POST request via curl with proxy
     */
    private function fetchPost(string $url, array $data, string $referer = ''): string
    {
        $proxy = $this->getRandomProxy();
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];

        $ch = curl_init($url);
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agents[0]);
        curl_setopt($ch, CURLOPT_REFERER, $referer ?: $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            echo "POST Error on {$url} - code:{$httpCode} err:{$err}\n";
            return '';
        }

        return $response;
    }

    /**
     * Insert authors or artists into author + author_manga tables
     */
    private function insertAuthorsArtists(int $mangaId, string $names, int $type): void
    {
        $list = array_filter(array_map('trim', explode(',', $names)));
        foreach ($list as $name) {
            if (!$name) continue;
            $existing = $this->db->table('author')->where('name', $name)->get()->getRow();
            if ($existing) {
                $authorId = $existing->id;
            } else {
                $this->db->table('author')->insert(['name' => $name, 'slug' => $this->slugify($name)]);
                $authorId = $this->db->insertID();
            }
            $this->db->table('author_manga')->insert([
                'manga_id'  => $mangaId,
                'author_id' => $authorId,
                'type'      => $type,
            ]);
        }
    }

    // =========================================================================
    // CRAWL LOGIC - CHAPTER IMAGES
    // =========================================================================

    /**
     * Crawl a single chapter from manga18fx
     */
    private function crawlChapterFromManga18fx(object $chapter, object $manga): void
    {
        $html = $this->fetchUrl($chapter->source_url, 'https://manga18fx.com');
        if (!$html) {
            echo "  Failed to fetch page.\n";
            return;
        }

        $dom = HtmlDomParser::str_get_html($html);
        $readContent = $dom->find('.read-content');

        if (!isset($readContent[0])) {
            echo "  No .read-content found.\n";
            return;
        }

        // Set crawling status
        $this->db->table('chapter')->where('id', $chapter->id)->update(['is_crawling' => 1]);

        // Prepare chapter directory
        $chapterDir = $this->savePath . $manga->slug . '/chapters/' . $chapter->slug . '/';
        @mkdir($chapterDir, 0755, true);

        $imgDoms = $readContent[0]->find('img');
        $index = 1;
        $successCount = 0;

        foreach ($imgDoms as $imgDom) {
            $src = trim($imgDom->getAttribute('src') ?: $imgDom->src ?? '');
            if (!$src) continue;

            $ext = $this->getImageExtension($src);
            $pageName = str_pad($index, 2, '0', STR_PAD_LEFT) . '.' . $ext;

            echo "  Downloading page {$index}: " . substr($src, 0, 80) . "...\n";

            $imageData = $this->fetchImageData($src, 'https://manga18fx.com/');
            if (!$imageData) {
                echo "  FAILED: page {$index}\n";
                $index++;
                continue;
            }

            // Save and optimize image
            $finalName = $this->saveAndOptimizeImage($imageData, $chapterDir, $pageName);

            // Check if it's a dummy/logo image (height = 300)
            $info = @getimagesize($chapterDir . $finalName);
            if ($info && $info[1] == 300) {
                echo "  Skipped logo image (300px height)\n";
                @unlink($chapterDir . $finalName);
                $index++;
                continue;
            }

            // Insert page record
            $this->db->table('page')->insert([
                'slug'       => $index,
                'image'      => $finalName,
                'external'   => 0,
                'chapter_id' => $chapter->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $successCount++;
            echo "  OK: {$manga->name} - Chapter {$chapter->number} - Page: {$finalName}\n";
            $index++;
        }

        // Mark as done
        $this->db->table('chapter')->where('id', $chapter->id)->update([
            'is_crawling' => 0,
            'is_show'     => 1,
        ]);

        // Update manga latest chapter info
        $this->updateMangaLatestChapters($chapter->manga_id);

        echo "  Completed: {$successCount} pages downloaded.\n";
    }

    /**
     * Crawl chapter images from MangaDistrict
     * Selector: .reading-content img (data-src or src)
     */
    private function crawlChapterFromMangaDistrict(object $chapter, object $manga): void
    {
        $html = $this->fetchUrl($chapter->source_url, 'https://mangadistrict.com');
        if (!$html) {
            echo "  Failed to fetch mangadistrict page.\n";
            return;
        }

        $dom = HtmlDomParser::str_get_html($html);

        // MangaDistrict uses .reading-content or .entry-content for chapter images
        $container = $dom->find('.reading-content', 0) ?: $dom->find('.entry-content', 0);
        if (!$container) {
            echo "  No reading content found.\n";
            return;
        }

        $imgDoms = $container->find('img');
        if (empty($imgDoms) || count($imgDoms) === 0) {
            echo "  No images found in reading content.\n";
            return;
        }

        // Set crawling status
        $this->db->table('chapter')->where('id', $chapter->id)->update(['is_crawling' => 1]);

        // Prepare chapter directory
        $chapterDir = $this->savePath . $manga->slug . '/chapters/' . $chapter->slug . '/';
        @mkdir($chapterDir, 0755, true);

        $index = 1;
        $successCount = 0;

        foreach ($imgDoms as $imgDom) {
            $src = trim($imgDom->getAttribute('data-src') ?: $imgDom->getAttribute('src') ?: '');
            if (!$src || str_contains($src, 'logo') || str_contains($src, 'icon')) continue;

            // Skip tiny images (likely ads/logos)
            $width = (int) ($imgDom->getAttribute('width') ?: 0);
            if ($width > 0 && $width < 100) continue;

            $ext = $this->getImageExtension($src);
            $pageName = str_pad($index, 2, '0', STR_PAD_LEFT) . '.' . $ext;

            echo "  Downloading page {$index}: " . substr($src, 0, 80) . "...\n";

            $imageData = $this->fetchImageData($src, 'https://mangadistrict.com/');
            if (!$imageData) {
                echo "  FAILED: page {$index}\n";
                $index++;
                continue;
            }

            // Save and optimize image
            $finalName = $this->saveAndOptimizeImage($imageData, $chapterDir, $pageName);

            // Skip tiny/logo images by actual size
            $info = @getimagesize($chapterDir . $finalName);
            if ($info && $info[1] < 100) {
                echo "  Skipped tiny image ({$info[1]}px height)\n";
                @unlink($chapterDir . $finalName);
                $index++;
                continue;
            }

            // Insert page record
            $this->db->table('page')->insert([
                'slug'       => $index,
                'image'      => $finalName,
                'external'   => 0,
                'chapter_id' => $chapter->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $successCount++;
            echo "  OK: {$manga->name} - Chapter {$chapter->number} - Page: {$finalName}\n";
            $index++;
        }

        // Mark as done
        $this->db->table('chapter')->where('id', $chapter->id)->update([
            'is_crawling' => 0,
            'is_show'     => 1,
        ]);

        // Update manga latest chapter info
        $this->updateMangaLatestChapters($chapter->manga_id);

        echo "  Completed: {$successCount} pages downloaded.\n";
    }

    // =========================================================================
    // CRAWL LOGIC - NEW MANGA + CHAPTERS
    // =========================================================================

    /**
     * Crawl a new manga from manga18fx
     */
    private function crawlNewManga(array $item, string $sourceUrl, int $paged): void
    {
        if (!($item['is_18'] ?? 0)) {
            echo "Skip non-18+: {$item['title']}\n";
            return;
        }

        $html = $this->fetchUrl($sourceUrl, 'https://manga18fx.com');
        if (!$html) {
            echo "  FAIL: could not fetch manga page\n";
            return;
        }

        $dom = HtmlDomParser::str_get_html($html);
        $data = $this->parseMangaPage($dom);
        $chapters = $this->parseChapterList($dom);

        echo "  Parsed: {$data['name']} | Chapters: " . count($chapters) . "\n";

        // Check if manga with same name exists
        $existByName = $this->findMangaByName($data['name']);
        if ($existByName) {
            $this->db->table('manga')->where('id', $existByName->id)->update([
                'from_manga18fx' => $existByName->from_manga18fx . ',' . $sourceUrl . ',',
                '_authors'       => $data['author'],
                '_artists'       => $data['artist'],
                'summary'        => $data['summary'],
                'is_public'      => $paged == 1 ? 1 : $existByName->is_public,
            ]);
            echo "  EXISTS by name: #{$existByName->id} - updated link\n";
            return;
        }

        // Create new manga
        $slug = $this->slugify($data['name']);
        $existSlug = $this->db->table('manga')->where('slug', $slug)->countAllResults();
        if ($existSlug > 0) {
            $slug .= '-' . time();
        }

        $mangaId = $this->db->table('manga')->insert([
            'name'           => $data['name'],
            'otherNames'     => $data['otherNames'],
            'from_manga18fx' => $sourceUrl . ',',
            'is_public'      => $paged == 1 ? 1 : 0,
            'cover'          => 1,
            'user_id'        => 1,
            'status_id'      => 1,
            'slug'           => $slug,
            '_authors'       => $data['author'],
            '_artists'       => $data['artist'],
            'summary'        => $data['summary'],
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $mangaId = $this->db->insertID();

        // Download cover
        if ($data['image']) {
            $this->downloadCoverFrom($slug, $data['image']);
        }

        // Insert categories
        $this->insertCategories($mangaId, $data['categories']);

        // Insert chapters
        $inserted = 0;
        foreach ($chapters as $chUrl) {
            if ($this->insertChapterFromUrl($mangaId, $chUrl)) {
                $inserted++;
            }
        }

        echo "  CREATED: manga #{$mangaId} - {$data['name']} ({$slug}) | {$inserted} chapters\n";
    }

    /**
     * Crawl new chapters for existing manga
     */
    private function crawlNewChaptersForManga(object $manga, array $item, int $paged): void
    {
        if (!$manga->is_public) {
            echo "  SKIP: not public\n";
            return;
        }

        $lastChapter = $item['last_chapter'] ?? '';
        if (!$lastChapter) {
            echo "  SKIP: no last chapter info\n";
            return;
        }

        $chapNumber = $this->extractChapterNumber($lastChapter);
        if ($chapNumber <= 0) {
            echo "  SKIP: can't parse chapter number from '{$lastChapter}'\n";
            return;
        }

        if ($chapNumber <= floatval($manga->chapter_1)) {
            echo "  UP TO DATE (source:{$chapNumber} <= db:{$manga->chapter_1})\n";
            return;
        }

        echo "  NEW CHAPTERS: source has {$chapNumber}, db has {$manga->chapter_1}\n";

        $html = $this->fetchUrl('https://manga18fx.com' . $item['source'], 'https://manga18fx.com');
        if (!$html) {
            echo "  FAIL: could not fetch manga page\n";
            return;
        }

        $dom = HtmlDomParser::str_get_html($html);
        $chapters = $this->parseChapterList($dom);

        $inserted = 0;
        foreach ($chapters as $chUrl) {
            if ($this->insertChapterFromUrl($manga->id, $chUrl)) {
                $inserted++;
            }
        }
        if ($inserted > 0) {
            echo "  Added {$inserted} new chapters for: {$manga->name}\n";
        } else {
            echo "  No new chapters to insert (all exist)\n";
        }
    }

    // =========================================================================
    // PARSERS
    // =========================================================================

    /**
     * Parse manga detail page
     */
    private function parseMangaPage($dom): array
    {
        $data = [
            'name'        => '',
            'otherNames'  => '',
            'author'      => '',
            'artist'      => '',
            'summary'     => '',
            'image'       => '',
            'categories'  => [],
        ];

        $clean = fn($s) => trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($s))));

        $titleEl = $dom->find('.post-title h1', 0);
        if ($titleEl) $data['name'] = $clean($titleEl->innertext);

        $contentItems = $dom->find('.post-content_item .summary-content');
        if (isset($contentItems[1])) $data['otherNames'] = $clean($contentItems[1]->innertext);
        if (isset($contentItems[2])) $data['author'] = $clean($contentItems[2]->innertext);
        if (isset($contentItems[3])) $data['artist'] = $clean($contentItems[3]->innertext);
        if (isset($contentItems[4])) {
            $cats = $clean($contentItems[4]->innertext);
            $data['categories'] = array_filter(array_map('trim', explode(',', $cats)));
        }

        $summaryEl = $dom->find('.dsct', 0);
        if ($summaryEl) $data['summary'] = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($summaryEl->plaintext))));

        $imgEl = $dom->find('.summary_image img', 0);
        if ($imgEl) {
            $data['image'] = $imgEl->getAttribute('data-src') ?: $imgEl->getAttribute('src') ?: '';
        }

        return $data;
    }

    /**
     * Parse chapter list from manga page
     */
    private function parseChapterList($dom): array
    {
        $chapters = [];
        $links = $dom->find('.a-h a');
        foreach ($links as $a) {
            $href = trim($a->href ?? '');
            if (!$href) continue;
            if (!str_starts_with($href, 'http')) {
                $href = 'https://manga18fx.com' . $href;
            }
            $chapters[] = $href;
        }
        return $chapters;
    }

    /**
     * Get latest manga list from a page
     */
    private function getLatestManga(string $url): array
    {
        $html = $this->fetchUrl($url, 'https://manga18fx.com/');
        if (!$html) return [];

        $dom = HtmlDomParser::str_get_html($html);
        $items = $dom->find('.page-item');
        $list = [];

        foreach ($items as $item) {
            $manga = ['is_18' => 0, 'source' => '', 'title' => '', 'last_chapter' => ''];

            if (count($item->find('.adult-badges')) > 0) {
                $manga['is_18'] = 1;
            }

            $link = $item->find('.tt a', 0);
            if ($link) {
                $manga['source'] = trim($link->href);
                $manga['title'] = trim(preg_replace('/\s+/', ' ', html_entity_decode($link->plaintext)));
            }

            $chapterLink = $item->find('.list-chapter .chapter a', 0);
            if ($chapterLink) {
                $manga['last_chapter'] = trim($chapterLink->href);
            }

            if ($manga['source']) {
                $list[] = $manga;
            }
        }
        return $list;
    }

    // =========================================================================
    // HELPERS - DB
    // =========================================================================

    private function findMangaByLink(string $href): ?object
    {
        return $this->db->table('manga')->like('from_manga18fx', $href . ',')->get()->getRow();
    }

    private function findMangaByName(string $name): ?object
    {
        $otherName = str_replace("'", "\u{2019}", $name);
        return $this->db->table('manga')
            ->where('name', $name)
            ->orWhere('name', $otherName)
            ->get()->getRow();
    }

    /**
     * Insert chapter from URL, returns true if inserted
     */
    private function insertChapterFromUrl(int $mangaId, string $url): bool
    {
        $number = $this->extractChapterNumber($url);
        if ($number <= 0) return false;

        // Check if already exists
        $exists = $this->db->table('chapter')
            ->where('number', $number)
            ->where('manga_id', $mangaId)
            ->countAllResults();
        if ($exists > 0) return false;

        $this->db->table('chapter')->insert([
            'slug'        => $this->slugify('chapter-' . $number),
            'name'        => 'Chapter ' . $number,
            'number'      => $number,
            'volume'      => 0,
            'manga_id'    => $mangaId,
            'user_id'     => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
            'view'        => 0,
            'is_show'     => 0,
            'is_crawling' => 0,
            'source_url'  => $url,
        ]);

        $id = $this->db->insertID();
        if ($id > 0) {
            echo "  + Chapter {$number}\n";
            return true;
        }
        return false;
    }

    private function insertCategories(int $mangaId, array $categories): void
    {
        foreach ($categories as $catName) {
            $catName = trim($catName);
            if (!$catName) continue;
            $cat = $this->db->table('category')->like('name', $catName)->get()->getRow();
            if ($cat) {
                $this->db->table('category_manga')->insert([
                    'manga_id'    => $mangaId,
                    'category_id' => $cat->id,
                ]);
            }
        }
    }

    /**
     * Parse source_url of a chapter, insert pages as external=1, return pages
     */
    private function parseAndInsertExternalPages(object $chapter): array
    {
        $referer = 'https://manga18fx.com/';
        if (str_contains($chapter->source_url, 'mangadistrict')) {
            $referer = 'https://mangadistrict.com/';
        }

        $html = $this->fetchUrl($chapter->source_url, $referer);
        if (!$html) {
            echo "  Failed to fetch source_url.\n";
            return [];
        }

        $dom = HtmlDomParser::str_get_html($html);

        // manga18fx
        $readContent = $dom->find('.read-content');
        if (!isset($readContent[0])) {
            // mangadistrict
            $readContent = $dom->find('.reading-content');
        }
        if (!isset($readContent[0])) {
            echo "  No read content found.\n";
            return [];
        }

        $imgDoms = $readContent[0]->find('img');
        $index = 1;
        $pages = [];

        foreach ($imgDoms as $imgDom) {
            $src = trim($imgDom->getAttribute('data-src') ?: $imgDom->getAttribute('src') ?: '');
            if (!$src || str_contains($src, 'loading') || str_contains($src, 'logo')) continue;

            $this->db->table('page')->insert([
                'slug'       => $index,
                'image'      => $src,
                'external'   => 1,
                'chapter_id' => $chapter->id,
                'manga_id'   => $chapter->manga_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $page = new \stdClass();
            $page->id = $this->db->insertID();
            $page->slug = $index;
            $page->image = $src;
            $page->external = 1;
            $pages[] = $page;

            echo "  Inserted external page {$index}: " . substr($src, 0, 60) . "\n";
            $index++;
        }

        echo "  Total pages inserted: " . count($pages) . "\n";
        return $pages;
    }

    /**
     * Update manga latest chapter info (chapter_1, chapter_2, etc.)
     */
    private function updateMangaLatestChapters(int $mangaId): void
    {
        $chapters = $this->db->query(
            "SELECT number as chapter_number, name as chapter_name, slug as chapter_slug,
                    created_at as chapter_created_at, flag as chapter_flag
             FROM chapter
             WHERE manga_id = ? AND is_show = 1
             ORDER BY CAST(number AS DECIMAL(10,2)) DESC
             LIMIT 3",
            [$mangaId]
        )->getResult();

        if (empty($chapters)) return;

        $update = [
            'chapter_1'   => floatval($chapters[0]->chapter_number),
            'chap_1_slug' => $chapters[0]->chapter_slug,
            'flag_chap_1' => $chapters[0]->chapter_flag ?? '',
            'time_chap_1' => strtotime($chapters[0]->chapter_created_at),
            'update_at'   => time(),
        ];

        if (isset($chapters[1])) {
            $update['chapter_2']   = floatval($chapters[1]->chapter_number);
            $update['chap_2_slug'] = $chapters[1]->chapter_slug;
            $update['flag_chap_2'] = $chapters[1]->chapter_flag ?? '';
            $update['time_chap_2'] = strtotime($chapters[1]->chapter_created_at);
        }

        $this->db->table('manga')->where('id', $mangaId)->update($update);
    }

    // =========================================================================
    // HELPERS - HTTP
    // =========================================================================

    /**
     * Get a random proxy from env config
     */
    private function getRandomProxy(): string
    {
        $ips = env('CURL_PROXY_IPS', '');
        if (!$ips) return '';
        $ipList = array_filter(array_map('trim', explode(',', $ips)));
        if (empty($ipList)) return '';

        $ip   = $ipList[array_rand($ipList)];
        $user = env('CURL_PROXY_USER', '');
        $pass = env('CURL_PROXY_PASS', '');
        $port = env('CURL_PROXY_PORT', '50100');

        return "http://{$user}:{$pass}@{$ip}:{$port}";
    }

    /**
     * Fetch URL content via curl with proxy support
     */
    private function fetchUrl(string $url, string $referer = ''): string
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
        ];

        $proxy = $this->getRandomProxy();

        $ch = curl_init($url);
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agents[array_rand($agents)]);
        curl_setopt($ch, CURLOPT_REFERER, $referer ?: $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            echo "HTTP Error on {$url} - code:{$httpCode} err:{$err}\n";
            return '';
        }

        return $response;
    }

    /**
     * Fetch raw image data via curl with proxy
     */
    /**
     * Save image data to file, optimize if too large (>2MB: compress, >10MB: resize+compress)
     * Converts to WebP for smaller file size.
     * Returns final filename (may change extension to .webp)
     */
    private function saveAndOptimizeImage(string $imageData, string $savePath, string $filename, int $maxSizeMB = 10): string
    {
        $filePath = $savePath . $filename;
        file_put_contents($filePath, $imageData);
        $fileSize = strlen($imageData);
        $sizeMB = $fileSize / (1024 * 1024);

        // Under 2MB: keep original, no processing needed
        if ($sizeMB < 2) {
            return $filename;
        }

        echo "    Image {$filename}: " . round($sizeMB, 1) . "MB - optimizing...\n";

        // Try to process with GD
        $info = @getimagesize($filePath);
        if (!$info) {
            echo "    Cannot read image info, keeping original.\n";
            return $filename;
        }

        $mime = $info['mime'] ?? '';
        $srcImage = null;

        switch ($mime) {
            case 'image/jpeg':
                $srcImage = @imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($filePath);
                break;
            case 'image/webp':
                $srcImage = @imagecreatefromwebp($filePath);
                break;
            case 'image/gif':
                // GIF: just keep it, usually small
                return $filename;
        }

        if (!$srcImage) {
            echo "    Cannot create image resource, keeping original.\n";
            return $filename;
        }

        $origW = imagesx($srcImage);
        $origH = imagesy($srcImage);
        $newW = $origW;
        $newH = $origH;

        // Over 10MB or width > 2000px: resize down
        if ($sizeMB > $maxSizeMB || $origW > 2000) {
            $maxW = 1600;
            if ($origW > $maxW) {
                $ratio = $maxW / $origW;
                $newW = $maxW;
                $newH = (int) round($origH * $ratio);
                echo "    Resizing: {$origW}x{$origH} -> {$newW}x{$newH}\n";

                $resized = imagecreatetruecolor($newW, $newH);
                // Preserve transparency for PNG
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                imagedestroy($srcImage);
                $srcImage = $resized;
            }
        }

        // Save as WebP (much smaller than PNG/JPEG)
        $webpName = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        $webpPath = $savePath . $webpName;

        // Quality: 2-10MB → 85, >10MB → 75
        $quality = $sizeMB > $maxSizeMB ? 75 : 85;
        imagewebp($srcImage, $webpPath, $quality);
        imagedestroy($srcImage);

        $newSize = filesize($webpPath);
        $newSizeMB = round($newSize / (1024 * 1024), 2);
        echo "    Optimized: " . round($sizeMB, 1) . "MB -> {$newSizeMB}MB (WebP q{$quality})\n";

        // Remove original if WebP is smaller
        if ($newSize < $fileSize) {
            @unlink($filePath);
            return $webpName;
        }

        // WebP not smaller (rare), keep original
        @unlink($webpPath);
        echo "    WebP not smaller, keeping original.\n";
        return $filename;
    }

    private function fetchImageData(string $url, string $referer = ''): string
    {
        $proxy = $this->getRandomProxy();

        $ch = curl_init(trim($url));
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data ?: '';
    }

    // =========================================================================
    // HELPERS - FILES & IMAGES
    // =========================================================================

    /**
     * Download and save cover image with thumbnails
     */
    private function downloadCoverFrom(string $slug, string $imageUrl, string $referer = 'https://manga18fx.com/'): void
    {
        $coverDir = $this->savePath . $slug . '/cover/';
        @mkdir($coverDir, 0755, true);

        $imageData = $this->fetchImageData($imageUrl, $referer);
        if (!$imageData) return;

        $tmpFile = tempnam(sys_get_temp_dir(), 'cover_');
        file_put_contents($tmpFile, $imageData);

        try {
            $imgService = \Config\Services::image();
            $imgService->withFile($tmpFile)->resize(250, 350, true, 'height')->save($coverDir . 'cover_250x350.jpg', 90);
            $imgService->withFile($coverDir . 'cover_250x350.jpg')->resize(150, 210, true, 'height')->save($coverDir . 'cover_thumb.jpg', 85);
            $imgService->withFile($coverDir . 'cover_250x350.jpg')->resize(100, 140, true, 'height')->save($coverDir . 'cover_thumb_2.webp', 85);
        } catch (\Exception $e) {
            echo "  Cover resize error: {$e->getMessage()}\n";
        }

        @unlink($tmpFile);
    }


    // =========================================================================
    // HELPERS - STRING
    // =========================================================================

    /**
     * Extract chapter number from URL
     */
    private function extractChapterNumber(string $url): float
    {
        $parts = explode('/', trim($url, '/'));
        $chapter = end($parts);

        preg_match_all('!\d+!', $chapter, $matches);

        if (isset($matches[0]) && count($matches[0]) == 1) {
            return (float) $matches[0][0];
        } elseif (isset($matches[0]) && count($matches[0]) >= 2) {
            return (float) ($matches[0][0] . '.' . $matches[0][1]);
        }

        return 0;
    }

    /**
     * Get image extension from URL
     */
    private function getImageExtension(string $url): string
    {
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        if (!$ext || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            return 'jpg';
        }
        return $ext;
    }

    private function slugify(string $text, string $divider = '-'): string
    {
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, $divider);
        $text = preg_replace('~-+~', $divider, $text);
        $text = strtolower($text);
        return $text ?: 'n-a';
    }
}
