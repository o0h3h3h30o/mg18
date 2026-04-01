<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ChapterModel;

class ChapterController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new ChapterModel();
    }

    public function index($mangaId)
    {
        $manga = $this->db->table('manga')->where('id', $mangaId)->get()->getRow();
        if (!$manga) return redirect()->to('/admin/manga');

        $chapters = $this->db->table('chapter')
            ->where('manga_id', $mangaId)
            ->orderBy('CAST(`number` AS DECIMAL(10,2)) DESC', '', false)
            ->get()->getResult();

        $data = [
            'title'    => 'Chapters - ' . $manga->name,
            'manga'    => $manga,
            'chapters' => $chapters,
        ];
        return view('admin/chapter/index', $data);
    }

    public function create($mangaId)
    {
        $manga = $this->db->table('manga')->where('id', $mangaId)->get()->getRow();
        if (!$manga) return redirect()->to('/admin/manga');

        // Suggest next chapter number
        $lastNumber = $this->db->table('chapter')
            ->where('manga_id', $mangaId)
            ->orderBy('CAST(`number` AS DECIMAL(10,2)) DESC', '', false)
            ->limit(1)->get()->getRow();
        $nextNumber = $lastNumber ? (float)$lastNumber->number + 1 : 1;

        $data = [
            'title' => 'Create Chapter - ' . $manga->name,
            'item'  => null,
            'manga' => $manga,
            'pages' => [],
            'nextNumber' => $nextNumber,
        ];
        return view('admin/chapter/form', $data);
    }

    public function store($mangaId)
    {
        $manga = $this->db->table('manga')->where('id', $mangaId)->get()->getRow();
        if (!$manga) return redirect()->to('/admin/manga');

        if (!$this->validate([
            'name'   => 'required|max_length[500]',
            'number' => 'required|max_length[20]',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $number = trim($this->request->getPost('number'));
        $name = trim($this->request->getPost('name'));
        $slug = trim($this->request->getPost('slug')) ?: url_title($name ?: 'chapter-' . $number, '-', true);

        // Check duplicate number
        $existNumber = $this->db->table('chapter')->where('manga_id', $mangaId)->where('number', $number)->get()->getRow();
        if ($existNumber) {
            return redirect()->back()->withInput()->with('error', 'Chapter number "' . $number . '" đã tồn tại.');
        }

        // Check duplicate slug
        $existSlug = $this->db->table('chapter')->where('manga_id', $mangaId)->where('slug', $slug)->get()->getRow();
        if ($existSlug) {
            return redirect()->back()->withInput()->with('error', 'Slug "' . $slug . '" đã tồn tại.');
        }

        $id = $this->model->insert([
            'manga_id'    => $mangaId,
            'name'        => $name,
            'number'      => $number,
            'slug'        => $slug,
            'is_show'     => (int) $this->request->getPost('is_show'),
            'need_login'  => (int) $this->request->getPost('need_login'),
            'source_url'  => trim($this->request->getPost('source_url') ?? ''),
            'is_crawling' => (int) $this->request->getPost('is_crawling'),
            'view'        => 0,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        // Update manga latest chapter info
        $this->db->table('manga')->where('id', $mangaId)->update([
            'chapter_1'   => $name ?: 'Chapter ' . $number,
            'chap_1_slug' => $slug,
            'time_chap_1' => date('Y-m-d H:i:s'),
            'update_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/chapters/edit/' . $id)->with('success', 'Chapter created. Now add pages/images below.');
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/manga');

        $manga = $this->db->table('manga')->where('id', $item->manga_id)->get()->getRow();

        $pages = $this->db->table('page')
            ->where('chapter_id', $id)
            ->orderBy('slug', 'ASC')
            ->get()->getResult();

        $data = [
            'title' => 'Edit Chapter',
            'item'  => $item,
            'manga' => $manga,
            'pages' => $pages,
        ];
        return view('admin/chapter/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate([
            'name'        => 'required|max_length[500]',
            'number'      => 'required|max_length[20]',
            'slug'        => 'required|max_length[500]',
            'is_show'     => 'permit_empty|in_list[0,1]',
            'need_login'  => 'permit_empty|in_list[0,1]',
            'source_url'  => 'permit_empty|max_length[256]',
            'is_crawling' => 'permit_empty|in_list[0,1,2]',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $oldItem = $this->model->find($id);
        $newSlug = trim($this->request->getPost('slug'));

        // Rename chapter folder on disk if slug changed
        if ($oldItem && $newSlug !== $oldItem->slug) {
            $manga = $this->db->table('manga')->where('id', $oldItem->manga_id)->get()->getRow();
            if ($manga) {
                $basePath = config('Manga')->savePath . $manga->slug . '/chapters/';
                $oldDir = $basePath . $oldItem->slug;
                $newDir = $basePath . $newSlug;
                if (is_dir($oldDir) && !is_dir($newDir)) {
                    rename($oldDir, $newDir);
                }
            }
        }

        $this->model->update($id, [
            'name'        => trim($this->request->getPost('name')),
            'number'      => trim($this->request->getPost('number')),
            'slug'        => $newSlug,
            'is_show'     => (int) $this->request->getPost('is_show'),
            'need_login'  => (int) $this->request->getPost('need_login'),
            'source_url'  => trim($this->request->getPost('source_url') ?? ''),
            'is_crawling' => (int) $this->request->getPost('is_crawling'),
        ]);

        $item = $this->model->find($id);
        return redirect()->to('/admin/chapters/' . $item->manga_id)->with('success', 'Chapter updated.');
    }

    public function recrawl($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/manga')->with('error', 'Chapter not found.');

        if (empty($item->source_url)) {
            return redirect()->to('/admin/chapters/edit/' . $id)->with('error', 'No source_url set. Please enter a source URL first.');
        }

        $manga = $this->db->table('manga')->where('id', $item->manga_id)->get()->getRow();

        try {
            // Delete existing pages from DB
            $this->db->table('page')->where('chapter_id', $id)->delete();

            // Delete chapter folder on disk
            if ($manga) {
                $chapterDir = config('Manga')->savePath . $manga->slug . '/chapters/' . $item->slug;
                if (is_dir($chapterDir)) {
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($chapterDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($files as $f) {
                        $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath());
                    }
                    rmdir($chapterDir);
                }
            }

            // Reset chapter to be picked up by crawler
            $this->model->update($id, [
                'is_show'     => 0,
                'is_crawling' => 0,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Re-crawl chapter failed: ' . $e->getMessage());
            return redirect()->to('/admin/chapters/edit/' . $id)->with('error', 'Re-crawl failed: ' . $e->getMessage());
        }

        return redirect()->to('/admin/chapters/edit/' . $id)->with('success', 'Chapter queued for re-crawl. Pages deleted, is_show=0, is_crawling=0. Crawler will pick it up.');
    }

    public function delete($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/manga')->with('error', 'Chapter not found.');

        $manga = $this->db->table('manga')->where('id', $item->manga_id)->get()->getRow();
        $mangaId = $item->manga_id;

        try {
            // Delete related comments
            $this->db->table('comments')->where('post_type', 'chapter')->where('post_id', $id)->delete();

            // Delete pages from DB
            $this->db->table('page')->where('chapter_id', $id)->delete();

            // Delete chapter from DB
            $this->model->delete($id);

            // Delete chapter folder on disk
            if ($manga) {
                $chapterDir = config('Manga')->savePath . $manga->slug . '/chapters/' . $item->slug;
                if (is_dir($chapterDir)) {
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($chapterDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($files as $f) {
                        $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath());
                    }
                    rmdir($chapterDir);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Delete chapter failed: ' . $e->getMessage());
            return redirect()->to('/admin/chapters/' . $mangaId)->with('error', 'Delete failed: ' . $e->getMessage());
        }

        return redirect()->to('/admin/chapters/' . $mangaId)->with('success', 'Chapter deleted.');
    }

    /**
     * Bulk delete chapters (with folder + pages + comments)
     */
    public function bulkDelete($mangaId)
    {
        $ids = $this->request->getPost('chapter_ids');
        if (empty($ids) || !is_array($ids)) {
            return redirect()->to('/admin/chapters/' . $mangaId)->with('error', 'No chapters selected.');
        }

        $manga = $this->db->table('manga')->where('id', $mangaId)->get()->getRow();
        $deleted = 0;

        foreach ($ids as $id) {
            $id = (int) $id;
            $item = $this->model->find($id);
            if (!$item || (int)$item->manga_id !== (int)$mangaId) continue;

            try {
                $this->db->table('comments')->where('post_type', 'chapter')->where('post_id', $id)->delete();
                $this->db->table('page')->where('chapter_id', $id)->delete();
                $this->model->delete($id);

                if ($manga) {
                    $chapterDir = config('Manga')->savePath . $manga->slug . '/chapters/' . $item->slug;
                    if (is_dir($chapterDir)) {
                        $files = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($chapterDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                            \RecursiveIteratorIterator::CHILD_FIRST
                        );
                        foreach ($files as $f) {
                            $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath());
                        }
                        rmdir($chapterDir);
                    }
                }
                $deleted++;
            } catch (\Exception $e) {
                log_message('error', 'Bulk delete chapter #' . $id . ' failed: ' . $e->getMessage());
            }
        }

        return redirect()->to('/admin/chapters/' . $mangaId)->with('success', $deleted . ' chapter(s) deleted.');
    }

    /**
     * Fetch chapter list from manga18fx URL (AJAX)
     */
    public function fetchFromSource($mangaId)
    {
        $manga = $this->db->table('manga')->where('id', $mangaId)->get()->getRow();
        if (!$manga) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Manga not found']);
        }

        $sourceUrl = trim($manga->from_manga18fx ?? '');
        // Get first URL if comma-separated
        if (str_contains($sourceUrl, ',')) {
            $parts = array_filter(array_map('trim', explode(',', $sourceUrl)));
            $sourceUrl = $parts[0] ?? '';
        }

        if (!$sourceUrl) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No manga18fx URL set. Edit manga and set "From Manga18fx" field.']);
        }

        // Ensure full URL
        if (!str_starts_with($sourceUrl, 'http')) {
            $sourceUrl = 'https://manga18fx.com/manga/' . $sourceUrl;
        }

        $html = $this->fetchUrlWithProxy($sourceUrl, 'https://manga18fx.com');
        if (!$html) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to fetch: ' . $sourceUrl]);
        }

        $dom = \voku\helper\HtmlDomParser::str_get_html($html);
        $chapters = [];
        $links = $dom->find('.a-h a');

        // Get existing chapter numbers for this manga
        $existingNumbers = $this->db->table('chapter')
            ->select('number')
            ->where('manga_id', $mangaId)
            ->get()->getResult();
        $existSet = [];
        foreach ($existingNumbers as $e) {
            $existSet[floatval($e->number)] = true;
        }

        foreach ($links as $a) {
            $href = trim($a->href ?? '');
            if (!$href) continue;
            if (!str_starts_with($href, 'http')) {
                $href = 'https://manga18fx.com' . $href;
            }
            $number = $this->extractChapterNumber($href);
            if ($number <= 0) continue;

            $chapters[] = [
                'number'   => $number,
                'url'      => $href,
                'name'     => 'Chapter ' . $number,
                'exists'   => isset($existSet[$number]),
            ];
        }

        // Sort by number desc
        usort($chapters, fn($a, $b) => $b['number'] <=> $a['number']);

        return $this->response->setJSON([
            'status'   => 'ok',
            'source'   => $sourceUrl,
            'chapters' => $chapters,
            'total'    => count($chapters),
            'new'      => count(array_filter($chapters, fn($c) => !$c['exists'])),
        ]);
    }

    /**
     * Import selected chapters from manga18fx (AJAX)
     */
    public function importChapters($mangaId)
    {
        $manga = $this->db->table('manga')->where('id', $mangaId)->get()->getRow();
        if (!$manga) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Manga not found']);
        }

        $items = $this->request->getJSON(true) ?? [];
        if (empty($items)) {
            $items = $this->request->getPost('chapters') ?? [];
        }
        if (empty($items)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No chapters selected']);
        }

        $inserted = 0;
        foreach ($items as $ch) {
            $number = floatval($ch['number'] ?? 0);
            $url = trim($ch['url'] ?? '');
            if ($number <= 0 || !$url) continue;

            // Skip if already exists
            $exists = $this->db->table('chapter')
                ->where('manga_id', $mangaId)
                ->where('number', $number)
                ->countAllResults();
            if ($exists > 0) continue;

            $slug = 'chapter-' . $number;
            // Ensure slug is unique
            $slugExists = $this->db->table('chapter')->where('manga_id', $mangaId)->where('slug', $slug)->countAllResults();
            if ($slugExists > 0) {
                $slug .= '-' . time();
            }

            $this->db->table('chapter')->insert([
                'slug'        => $slug,
                'name'        => 'Chapter ' . $number,
                'number'      => $number,
                'volume'      => 0,
                'manga_id'    => $mangaId,
                'user_id'     => (int) $this->user_info->id,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
                'view'        => 0,
                'is_show'     => 0,
                'is_crawling' => 0,
                'source_url'  => $url,
            ]);

            if ($this->db->insertID() > 0) {
                $inserted++;
            }
        }

        return $this->response->setJSON([
            'status'   => 'ok',
            'message'  => $inserted . ' chapter(s) imported. Crawler will pick them up.',
            'inserted' => $inserted,
        ]);
    }

    private function extractChapterNumber(string $url): float
    {
        if (preg_match('/chapter[_-]?([\d]+(?:\.\d+)?)/', $url, $m)) {
            return floatval($m[1]);
        }
        return 0;
    }

    private function fetchUrlWithProxy(string $url, string $referer = ''): string
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];

        $proxy = '';
        $ips = env('CURL_PROXY_IPS', '');
        if ($ips) {
            $ipList = array_filter(array_map('trim', explode(',', $ips)));
            if (!empty($ipList)) {
                $ip   = $ipList[array_rand($ipList)];
                $user = env('CURL_PROXY_USER', '');
                $pass = env('CURL_PROXY_PASS', '');
                $port = env('CURL_PROXY_PORT', '50100');
                $proxy = "http://{$user}:{$pass}@{$ip}:{$port}";
            }
        }

        $ch = curl_init($url);
        if ($proxy) curl_setopt($ch, CURLOPT_PROXY, $proxy);
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
        curl_close($ch);

        return ($httpCode >= 200 && $httpCode < 400) ? ($response ?: '') : '';
    }
}
