<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MangaModel;

class MangaController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new MangaModel();
    }

    public function index()
    {
        $search = $this->request->getGet('q') ?? '';
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $builder = $this->db->table('manga m')
            ->select('m.*, s.label as status_label, ct.label as type_label')
            ->join('status s', 's.id = m.status_id', 'left')
            ->join('comictype ct', 'ct.id = m.type_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('m.name', $search)
                ->orLike('m.otherNames', $search)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $manga = $builder->orderBy('m.id', 'DESC')->limit($perPage, $offset)->get()->getResult();

        $data = [
            'title'   => 'Manga',
            'manga'   => $manga,
            'search'  => $search,
            'page'    => $page,
            'perPage' => $perPage,
            'total'   => $total,
        ];
        return view('admin/manga/index', $data);
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/manga');

        $categories = $this->db->table('category')->orderBy('name')->get()->getResult();
        $statuses = $this->db->table('status')->get()->getResult();
        $types = $this->db->table('comictype')->get()->getResult();
        $selectedCats = $this->db->table('category_manga')->where('manga_id', $id)->get()->getResult();
        $selectedCatIds = array_column($selectedCats, 'category_id');

        // Authors & Artists
        $selectedAuthors = $this->db->query(
            'SELECT a.id, a.name FROM author a JOIN author_manga am ON a.id = am.author_id WHERE am.manga_id = ? AND am.type = 1', [$id]
        )->getResult();
        $selectedArtists = $this->db->query(
            'SELECT a.id, a.name FROM author a JOIN author_manga am ON a.id = am.author_id WHERE am.manga_id = ? AND am.type = 2', [$id]
        )->getResult();

        // Tags
        $selectedTags = $this->db->query(
            'SELECT t.id, t.name FROM tag t JOIN manga_tag mt ON t.id = mt.tag_id WHERE mt.manga_id = ?', [$id]
        )->getResult();

        $data = [
            'title'           => 'Edit Manga',
            'item'            => $item,
            'categories'      => $categories,
            'statuses'        => $statuses,
            'types'           => $types,
            'selectedCatIds'  => $selectedCatIds,
            'selectedAuthors' => $selectedAuthors,
            'selectedArtists' => $selectedArtists,
            'selectedTags'    => $selectedTags,
        ];
        return view('admin/manga/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate([
            'name'      => 'required|min_length[1]|max_length[500]',
            'status_id' => 'permit_empty|integer',
            'type_id'   => 'permit_empty|integer',
            'is_public' => 'permit_empty|in_list[0,1]',
            'hot'       => 'permit_empty|in_list[0,1]',
            'is_new'    => 'permit_empty|in_list[0,1]',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $slug = trim($this->request->getPost('slug'));
        if (!$slug) {
            $slug = url_title($this->request->getPost('name'), '-', true);
        }

        $updateData = [
            'name'            => trim($this->request->getPost('name')),
            'slug'            => $slug,
            'otherNames'      => $this->request->getPost('otherNames'),
            'summary'         => $this->request->getPost('summary'),
            'from_manga18fx'  => $this->request->getPost('from_manga18fx'),
            'status_id'       => $this->request->getPost('status_id') ?: null,
            'type_id'         => $this->request->getPost('type_id') ?: null,
            'is_public'       => (int) $this->request->getPost('is_public'),
            'hot'             => (int) $this->request->getPost('hot'),
            'is_new'          => (int) $this->request->getPost('is_new'),
            'caution'         => (int) $this->request->getPost('caution'),
        ];
        $this->model->update($id, $updateData);

        // Update categories
        $this->db->table('category_manga')->where('manga_id', $id)->delete();
        $catIds = $this->request->getPost('category_ids') ?? [];
        foreach ($catIds as $catId) {
            $this->db->table('category_manga')->insert([
                'manga_id'    => $id,
                'category_id' => (int) $catId,
            ]);
        }

        // Update authors (type=1)
        $this->db->table('author_manga')->where('manga_id', $id)->where('type', 1)->delete();
        $authorNames = $this->request->getPost('authors') ?? [];
        $authorNamesStr = [];
        foreach ($authorNames as $name) {
            $name = trim($name);
            if (!$name) continue;
            $author = $this->db->table('author')->where('name', $name)->get()->getRow();
            if (!$author) {
                $this->db->table('author')->insert(['name' => $name, 'slug' => url_title($name, '-', true)]);
                $authorId = $this->db->insertID();
            } else {
                $authorId = $author->id;
            }
            $this->db->table('author_manga')->insert(['manga_id' => $id, 'author_id' => $authorId, 'type' => 1]);
            $authorNamesStr[] = $name;
        }
        $this->model->update($id, ['_authors' => implode(', ', $authorNamesStr)]);

        // Update artists (type=2)
        $this->db->table('author_manga')->where('manga_id', $id)->where('type', 2)->delete();
        $artistNames = $this->request->getPost('artists') ?? [];
        $artistNamesStr = [];
        foreach ($artistNames as $name) {
            $name = trim($name);
            if (!$name) continue;
            $artist = $this->db->table('author')->where('name', $name)->get()->getRow();
            if (!$artist) {
                $this->db->table('author')->insert(['name' => $name, 'slug' => url_title($name, '-', true)]);
                $artistId = $this->db->insertID();
            } else {
                $artistId = $artist->id;
            }
            $this->db->table('author_manga')->insert(['manga_id' => $id, 'author_id' => $artistId, 'type' => 2]);
            $artistNamesStr[] = $name;
        }
        $this->model->update($id, ['_artists' => implode(', ', $artistNamesStr)]);

        // Update tags
        $this->db->table('manga_tag')->where('manga_id', $id)->delete();
        $tagNames = $this->request->getPost('tags') ?? [];
        foreach ($tagNames as $name) {
            $name = trim($name);
            if (!$name) continue;
            $tag = $this->db->table('tag')->where('name', $name)->get()->getRow();
            if (!$tag) {
                $this->db->table('tag')->insert(['name' => $name, 'slug' => url_title($name, '-', true)]);
                $tagId = $this->db->insertID();
            } else {
                $tagId = $tag->id;
            }
            $this->db->table('manga_tag')->insert(['manga_id' => $id, 'tag_id' => $tagId]);
        }

        return redirect()->to('/admin/manga/edit/' . $id)->with('success', 'Manga updated.');
    }

    /**
     * AJAX upload cover: file upload or fetch from URL
     */
    public function uploadCover($id)
    {
        $manga = $this->model->find($id);
        if (!$manga) {
            return $this->response->setJSON(['status' => 0, 'msg' => 'Manga not found']);
        }

        $savePath = config('Manga')->savePath . $manga->slug . '/cover/';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }

        $tmpFile = null;
        $imageUrl = $this->request->getPost('image_url');
        $file = $this->request->getFile('cover_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Upload file
            $mime = $file->getMimeType();
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                return $this->response->setJSON(['status' => 0, 'msg' => 'Invalid image type']);
            }
            $tmpFile = $file->getTempName();
        } elseif ($imageUrl) {
            // Fetch from URL
            $imageUrl = filter_var($imageUrl, FILTER_VALIDATE_URL);
            if (!$imageUrl) {
                return $this->response->setJSON(['status' => 0, 'msg' => 'Invalid URL']);
            }
            $ch = curl_init($imageUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_HTTPHEADER     => [
                    'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer: ' . parse_url($imageUrl, PHP_URL_SCHEME) . '://' . parse_url($imageUrl, PHP_URL_HOST) . '/',
                ],
            ]);
            $imgData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);
            if (!$imgData || $httpCode !== 200) {
                return $this->response->setJSON(['status' => 0, 'msg' => 'Cannot fetch image: ' . ($curlErr ?: 'HTTP ' . $httpCode)]);
            }
            // Validate image data
            $tmpFile = tempnam(sys_get_temp_dir(), 'cover_');
            file_put_contents($tmpFile, $imgData);
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmpFile);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                @unlink($tmpFile);
                return $this->response->setJSON(['status' => 0, 'msg' => 'Invalid image type from URL (got: ' . $mime . ')']);
            }
        } else {
            return $this->response->setJSON(['status' => 0, 'msg' => 'No file or URL provided']);
        }

        // Save cover_250x350.jpg
        $imgService = \Config\Services::image();
        $imgService->withFile($tmpFile)
            ->resize(250, 350, true, 'height')
            ->save($savePath . 'cover_250x350.jpg', 90);

        // Generate thumbnails
        $imgService->withFile($savePath . 'cover_250x350.jpg')
            ->resize(150, 210, true, 'height')
            ->save($savePath . 'cover_thumb.jpg', 85);

        $imgService->withFile($savePath . 'cover_250x350.jpg')
            ->resize(100, 140, true, 'height')
            ->save($savePath . 'cover_thumb_2.webp', 85);

        // Cleanup temp file from URL fetch
        if ($imageUrl && $tmpFile) {
            @unlink($tmpFile);
        }

        return $this->response->setJSON([
            'status' => 1,
            'msg' => 'Cover updated',
            'cover' => '/manga/' . $manga->slug . '/cover/cover_250x350.jpg?t=' . time(),
        ]);
    }

    /**
     * Fetch manga info from manga18fx.com
     */
    public function fetchManga18fx()
    {
        $url = trim($this->request->getPost('url') ?? '');
        if (!$url) {
            return $this->response->setJSON(['status' => 0, 'msg' => 'URL is required']);
        }

        // Normalize URL: accept slug or full URL
        if (!str_starts_with($url, 'http')) {
            $slug = trim($url, '/');
            $url = 'https://manga18fx.com/manga/' . $slug;
        }

        // Fetch HTML via curl with random user agent
        $listAgent = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.117 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];
        $agent = $listAgent[array_rand($listAgent)];
        $referer = 'https://manga18fx.com/';
        $proxy = env('CURL_PROXY', '');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if (!$html || $httpCode !== 200) {
            return $this->response->setJSON(['status' => 0, 'msg' => 'Cannot fetch page: ' . ($curlErr ?: 'HTTP ' . $httpCode)]);
        }

        // Parse HTML
        $data = $this->parseManga18fxHtml($html);
        $data['source_url'] = $url;

        return $this->response->setJSON(['status' => 1, 'data' => $data]);
    }

    /**
     * Parse manga18fx HTML to extract manga info
     */
    private function parseManga18fxHtml(string $html): array
    {
        $data = [
            'name'        => '',
            'slug'        => '',
            'otherNames'  => '',
            'summary'     => '',
            'authors'     => [],
            'artists'     => [],
            'genres'      => [],
            'type'        => '',
            'status'      => '',
            'cover'       => '',
        ];

        // Suppress HTML errors
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($doc);

        // Name: <h1> inside .post-title
        $nameNodes = $xpath->query('//div[contains(@class,"post-title")]//h1');
        if ($nameNodes->length > 0) {
            $data['name'] = trim($nameNodes->item(0)->textContent);
        }

        // Slug from breadcrumb link
        $breadcrumbLinks = $xpath->query('//ol[contains(@class,"breadcrumb")]//a[contains(@class,"active")]/@href');
        if ($breadcrumbLinks->length > 0) {
            $href = $breadcrumbLinks->item(0)->textContent;
            $parts = explode('/', trim($href, '/'));
            $data['slug'] = end($parts);
        } elseif ($data['name']) {
            $data['slug'] = url_title($data['name'], '-', true);
        }

        // Cover image
        $coverNodes = $xpath->query('//div[contains(@class,"summary_image")]//img/@src');
        if ($coverNodes->length === 0) {
            $coverNodes = $xpath->query('//div[contains(@class,"summary_image")]//img/@data-src');
        }
        if ($coverNodes->length > 0) {
            $data['cover'] = trim($coverNodes->item(0)->textContent);
        }

        // Parse post-content_item blocks
        $items = $xpath->query('//div[contains(@class,"post-content_item")]');
        foreach ($items as $item) {
            $heading = $xpath->query('.//div[contains(@class,"summary-heading")]//h5', $item);
            $content = $xpath->query('.//div[contains(@class,"summary-content")]', $item);
            if ($heading->length === 0 || $content->length === 0) continue;

            $label = strtolower(trim($heading->item(0)->textContent));
            $contentNode = $content->item(0);

            if (str_contains($label, 'alternative')) {
                $data['otherNames'] = trim($contentNode->textContent);
            } elseif (str_contains($label, 'author')) {
                $links = $xpath->query('.//a', $contentNode);
                for ($i = 0; $i < $links->length; $i++) {
                    $name = trim($links->item($i)->textContent);
                    if ($name) $data['authors'][] = $name;
                }
            } elseif (str_contains($label, 'artist')) {
                $links = $xpath->query('.//a', $contentNode);
                for ($i = 0; $i < $links->length; $i++) {
                    $name = trim($links->item($i)->textContent);
                    if ($name) $data['artists'][] = $name;
                }
            } elseif (str_contains($label, 'genre')) {
                $links = $xpath->query('.//a', $contentNode);
                for ($i = 0; $i < $links->length; $i++) {
                    $name = trim($links->item($i)->textContent);
                    if ($name) $data['genres'][] = $name;
                }
            } elseif (str_contains($label, 'type')) {
                $data['type'] = trim($contentNode->textContent);
            } elseif (str_contains($label, 'status')) {
                $data['status'] = trim($contentNode->textContent);
            }
        }

        // Summary: try multiple selectors
        $summarySelectors = [
            '//div[contains(@class,"description-summary")]//div[contains(@class,"summary__content")]',
            '//div[contains(@class,"description-summary")]',
            '//div[contains(@class,"dsct")]',
            '//div[contains(@class,"manga-summary")]',
        ];
        foreach ($summarySelectors as $selector) {
            $summaryNodes = $xpath->query($selector);
            if ($summaryNodes->length > 0) {
                // Get inner HTML
                $innerHtml = '';
                $children = $summaryNodes->item(0)->childNodes;
                foreach ($children as $child) {
                    $innerHtml .= $doc->saveHTML($child);
                }
                $innerHtml = trim($innerHtml);
                if ($innerHtml) {
                    $data['summary'] = $innerHtml;
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Parse pasted HTML from manga18fx (no fetch needed)
     */
    public function parseManga18fxFromHtml()
    {
        $html = $this->request->getPost('html') ?? '';
        if (strlen($html) < 100) {
            return $this->response->setJSON(['status' => 0, 'msg' => 'HTML too short or empty']);
        }
        $data = $this->parseManga18fxHtml($html);
        return $this->response->setJSON(['status' => 1, 'data' => $data]);
    }

    public function searchAuthors()
    {
        $q = $this->request->getGet('q') ?? '';
        $results = $this->db->table('author')->like('name', $q)->limit(10)->get()->getResult();
        return $this->response->setJSON(array_map(fn($r) => ['id' => $r->id, 'name' => $r->name], $results));
    }

    public function searchTags()
    {
        $q = $this->request->getGet('q') ?? '';
        $results = $this->db->table('tag')->like('name', $q)->limit(10)->get()->getResult();
        return $this->response->setJSON(array_map(fn($r) => ['id' => $r->id, 'name' => $r->name], $results));
    }

    public function create()
    {
        $categories = $this->db->table('category')->orderBy('name')->get()->getResult();
        $statuses = $this->db->table('status')->get()->getResult();
        $types = $this->db->table('comictype')->get()->getResult();

        $data = [
            'title'           => 'Create Manga',
            'item'            => null,
            'categories'      => $categories,
            'statuses'        => $statuses,
            'types'           => $types,
            'selectedCatIds'  => [],
            'selectedAuthors' => [],
            'selectedArtists' => [],
            'selectedTags'    => [],
        ];
        return view('admin/manga/form', $data);
    }

    public function store()
    {
        if (!$this->validate([
            'name'      => 'required|min_length[1]|max_length[500]',
            'status_id' => 'permit_empty|integer',
            'type_id'   => 'permit_empty|integer',
        ])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $name = trim($this->request->getPost('name'));
        $slug = trim($this->request->getPost('slug'));
        if (!$slug) {
            $slug = url_title($name, '-', true);
        }

        // Check slug trùng
        $existing = $this->db->table('manga')->where('slug', $slug)->get()->getRow();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Slug "' . $slug . '" đã tồn tại (manga: ' . $existing->name . ')');
        }

        $id = $this->model->insert([
            'name'            => $name,
            'slug'            => $slug,
            'otherNames'      => $this->request->getPost('otherNames') ?? '',
            'summary'         => $this->request->getPost('summary') ?? '',
            'from_manga18fx'  => $this->request->getPost('from_manga18fx') ?? '',
            'cover'           => '',
            'status_id'       => $this->request->getPost('status_id') ?: null,
            'type_id'         => $this->request->getPost('type_id') ?: null,
            'is_public'       => (int) $this->request->getPost('is_public'),
            'hot'             => (int) $this->request->getPost('hot'),
            'is_new'          => (int) $this->request->getPost('is_new'),
            'caution'         => (int) $this->request->getPost('caution'),
            'views'           => 0,
            'view_day'        => 0,
            'view_month'      => 0,
            'create_at'       => time(),
            'update_at'       => time(),
        ]);

        // Handle cover: file upload or URL fetch
        $tmpFile = null;
        $file = $this->request->getFile('cover');
        $coverUrl = $this->request->getPost('cover_url');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $tmpFile = $file->getTempName();
        } elseif ($coverUrl) {
            $coverUrl = filter_var($coverUrl, FILTER_VALIDATE_URL);
            if ($coverUrl) {
                $ch = curl_init($coverUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    CURLOPT_HTTPHEADER     => [
                        'Referer: ' . parse_url($coverUrl, PHP_URL_SCHEME) . '://' . parse_url($coverUrl, PHP_URL_HOST) . '/',
                    ],
                ]);
                $imgData = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($imgData && $httpCode === 200) {
                    $tmpFile = tempnam(sys_get_temp_dir(), 'cover_');
                    file_put_contents($tmpFile, $imgData);
                }
            }
        }

        if ($tmpFile) {
            $savePath = config('Manga')->savePath . $slug . '/cover/';
            if (!is_dir($savePath)) {
                mkdir($savePath, 0755, true);
            }
            $imgService = \Config\Services::image();
            $imgService->withFile($tmpFile)
                ->resize(250, 350, true, 'height')
                ->save($savePath . 'cover_250x350.jpg', 90);
            $imgService->withFile($savePath . 'cover_250x350.jpg')
                ->resize(150, 210, true, 'height')
                ->save($savePath . 'cover_thumb.jpg', 85);
            $imgService->withFile($savePath . 'cover_250x350.jpg')
                ->resize(100, 140, true, 'height')
                ->save($savePath . 'cover_thumb_2.webp', 85);
            if ($coverUrl && $tmpFile) {
                @unlink($tmpFile);
            }
        }

        // Save categories
        $catIds = $this->request->getPost('category_ids') ?? [];
        $batch = [];
        foreach ($catIds as $catId) {
            $batch[] = ['manga_id' => $id, 'category_id' => (int) $catId];
        }
        if ($batch) {
            $this->db->table('category_manga')->insertBatch($batch);
        }

        // Save authors (type=1)
        $authorNames = $this->request->getPost('authors') ?? [];
        $authorNamesStr = [];
        foreach ($authorNames as $aName) {
            $aName = trim($aName);
            if (!$aName) continue;
            $author = $this->db->table('author')->where('name', $aName)->get()->getRow();
            if (!$author) {
                $this->db->table('author')->insert(['name' => $aName, 'slug' => url_title($aName, '-', true)]);
                $authorId = $this->db->insertID();
            } else {
                $authorId = $author->id;
            }
            $this->db->table('author_manga')->insert(['manga_id' => $id, 'author_id' => $authorId, 'type' => 1]);
            $authorNamesStr[] = $aName;
        }
        if ($authorNamesStr) {
            $this->model->update($id, ['_authors' => implode(', ', $authorNamesStr)]);
        }

        // Save artists (type=2)
        $artistNames = $this->request->getPost('artists') ?? [];
        $artistNamesStr = [];
        foreach ($artistNames as $aName) {
            $aName = trim($aName);
            if (!$aName) continue;
            $artist = $this->db->table('author')->where('name', $aName)->get()->getRow();
            if (!$artist) {
                $this->db->table('author')->insert(['name' => $aName, 'slug' => url_title($aName, '-', true)]);
                $artistId = $this->db->insertID();
            } else {
                $artistId = $artist->id;
            }
            $this->db->table('author_manga')->insert(['manga_id' => $id, 'author_id' => $artistId, 'type' => 2]);
            $artistNamesStr[] = $aName;
        }
        if ($artistNamesStr) {
            $this->model->update($id, ['_artists' => implode(', ', $artistNamesStr)]);
        }

        // Save tags
        $tagNames = $this->request->getPost('tags') ?? [];
        foreach ($tagNames as $tName) {
            $tName = trim($tName);
            if (!$tName) continue;
            $tag = $this->db->table('tag')->where('name', $tName)->get()->getRow();
            if (!$tag) {
                $this->db->table('tag')->insert(['name' => $tName, 'slug' => url_title($tName, '-', true)]);
                $tagId = $this->db->insertID();
            } else {
                $tagId = $tag->id;
            }
            $this->db->table('manga_tag')->insert(['manga_id' => $id, 'tag_id' => $tagId]);
        }

        return redirect()->to('/admin/manga/edit/' . $id)->with('success', 'Manga created successfully.');
    }

    public function delete($id)
    {
        $manga = $this->model->find($id);
        if (!$manga) return redirect()->to('/admin/manga');

        // Delete all related records
        $this->db->table('category_manga')->where('manga_id', $id)->delete();
        $this->db->table('author_manga')->where('manga_id', $id)->delete();
        $this->db->table('manga_tag')->where('manga_id', $id)->delete();
        $this->db->table('bookmarks')->where('manga_id', $id)->delete();

        // Delete all pages of all chapters
        $chapters = $this->db->table('chapter')->where('manga_id', $id)->get()->getResult();
        foreach ($chapters as $ch) {
            $this->db->table('page')->where('chapter_id', $ch->id)->delete();
        }
        $this->db->table('chapter')->where('manga_id', $id)->delete();

        // Delete manga record
        $this->model->delete($id);

        return redirect()->to('/admin/manga')->with('success', 'Manga "' . esc($manga->name) . '" deleted.');
    }
}
