<?php

namespace App\Controllers;

class Manga extends BaseController
{
    public function show($slug = null)
    {
        if (!$slug) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = $this->getCommonData();
        $data['ads'] = $this->getAds(2);

        $manga_info = $this->db->table('manga')->where('slug', $slug)->get()->getRow();

        if (!$manga_info) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Author + Artist in 1 query
        $authorArtist = $this->db->query(
            'SELECT a.name, a.slug, am.type FROM author a LEFT JOIN author_manga am ON a.id = am.author_id WHERE am.manga_id = ? AND am.type IN (1, 2)',
            [$manga_info->id]
        )->getResult();
        $manga_info->author = 'Updating';
        $manga_info->author_slug = '';
        $manga_info->artist = 'Updating';
        $manga_info->artist_slug = '';
        foreach ($authorArtist as $aa) {
            if ($aa->type == 1) { $manga_info->author = $aa->name; $manga_info->author_slug = $aa->slug; }
            if ($aa->type == 2) { $manga_info->artist = $aa->name; $manga_info->artist_slug = $aa->slug; }
        }

        // Categories
        $manga_info->categories = $this->db->query(
            'SELECT c.id, c.slug, c.name FROM category c LEFT JOIN category_manga cm ON cm.category_id = c.id WHERE cm.manga_id = ?',
            [$manga_info->id]
        )->getResult();

        // Check bookmark
        $data['check_bookmark'] = 0;
        if ($this->is_logged == 1) {
            $bookmarkCount = $this->db->table('bookmarks')
                ->where('manga_id', $manga_info->id)
                ->where('user_id', $this->user_info->id)
                ->countAllResults();
            $data['check_bookmark'] = $bookmarkCount > 0 ? 1 : 0;
        }

        // Ratings - 1 query
        $ratingInfo = $this->db->query(
            'SELECT AVG(score) as rate, COUNT(*) as total FROM item_ratings WHERE item_id = ?',
            [$manga_info->id]
        )->getRow();
        $manga_info->avg = number_format((float)($ratingInfo->rate ?? 0), 2, '.', ',');
        $manga_info->total_rate = $ratingInfo->total ?? 0;

        $data['top_day'] = $this->getTopDay();
        $data['top_month'] = $this->getTopMonth();
        $data['top_all'] = $this->db->query(
            'SELECT m.id as manga_id, m.name, m.slug, m.view_month as view, m.time_chap_1 FROM manga m ORDER BY m.views DESC LIMIT 10'
        )->getResult();

        // SEO
        $data['heading_title'] = $manga_info->name . ' Manga - Read Manga, Hentai 18+ For Free at Manga18.club';
        $data['seo_title'] = $manga_info->name;
        $data['seo_image'] = 'https://azmin.manga18.club/uploads/manga/' . $manga_info->slug . '/cover/cover_250x350.jpg';
        $data['seo_description'] = trim(strip_tags(html_entity_decode($manga_info->summary ?? '', ENT_QUOTES, 'UTF-8')));
        $data['seo_keyword'] = $manga_info->name . ', ' . $manga_info->name . ' manga18';

        // Breadcrumbs
        $data['breadcrums'] = [
            ['title' => 'Home', 'link' => '/'],
            ['title' => 'Manga', 'link' => '/list-manga'],
            ['title' => $manga_info->name, 'link' => ''],
        ];

        // Chapters
        $chapters = $this->db->query(
            'SELECT * FROM chapter WHERE manga_id = ? AND is_show = 1',
            [$manga_info->id]
        )->getResult();

        $sortedChapters = [];
        foreach ($chapters as $chapter) {
            $sortedChapters[$chapter->number] = $chapter;
        }
        array_multisort(array_keys($sortedChapters), SORT_DESC, SORT_NATURAL, $sortedChapters);
        $manga_info->chapters = $sortedChapters;

        $total_bookmarks = $this->db->query(
            'SELECT COUNT(DISTINCT id) as total FROM bookmarks WHERE manga_id = ?',
            [$manga_info->id]
        )->getRow()->total;
        $data['total_bookmarks'] = $total_bookmarks;


        $data['manga_info'] = $manga_info;

        return view('show', $data);
    }

    public function read($manga_slug = null)
    {
        if (!$manga_slug) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = $this->getCommonData();
        $data['ads'] = $this->getAds(3);

        $chapter_slug = $this->request->getUri()->getSegment(3, '');

        $manga_info = $this->db->table('manga')->where('slug', $manga_slug)->get()->getRow();
        if (!$manga_info) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['seo_image'] = 'https://azmin.manga18.club/uploads/manga/' . $manga_info->slug . '/cover/cover_250x350.jpg';

        $chapter_info = $this->db->query(
            'SELECT * FROM chapter WHERE manga_id = ? AND is_show = 1 AND slug = ?',
            [$manga_info->id, $chapter_slug]
        )->getResult();

        if (empty($chapter_info)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Check bookmark
        $data['check_bookmark'] = 0;
        if ($this->is_logged == 1) {
            $bookmarkCount = $this->db->table('bookmarks')
                ->where('manga_id', $manga_info->id)
                ->where('user_id', $this->user_info->id)
                ->countAllResults();
            $data['check_bookmark'] = $bookmarkCount > 0 ? 1 : 0;
        }

        // Get pages
        $ids = array_map(fn($ch) => $ch->id, $chapter_info);
        $allPages = $this->db->table('page')
            ->where('image !=', 'README.txt')
            ->whereIn('chapter_id', $ids)
            ->groupBy('slug')
            ->get()
            ->getResult();

        // View counting moved to /api/track-view (JS-based, works with CF cache)

        // Get all chapters and sort
        $chapters = $this->db->query(
            'SELECT * FROM chapter WHERE is_show = 1 AND manga_id = ?',
            [$manga_info->id]
        )->getResult();

        $sortedChapters = [];
        $keys = [];
        foreach ($chapters as $chapter) {
            $chapter->current = ($chapter->slug == $chapter_slug) ? 1 : 0;
            $sortedChapters[] = $chapter;
            $keys[] = $chapter->number;
        }
        array_multisort($keys, SORT_DESC, SORT_NATURAL, $sortedChapters);
        $manga_info->chapters = $sortedChapters;

        // Find next/prev chapters
        $nextChapter = null;
        $prevChapter = null;
        for ($i = 0; $i < count($sortedChapters); $i++) {
            if ($sortedChapters[$i]->slug == $chapter_slug) {
                $nextChapter = $sortedChapters[$i - 1] ?? null;
                $prevChapter = $sortedChapters[$i + 1] ?? null;
                break;
            }
        }

        $data['heading_title'] = $manga_info->name . ' - ' . $chapter_info[0]->name;
        $data['seo_description'] = trim(strip_tags(html_entity_decode($manga_info->summary ?? '', ENT_QUOTES, 'UTF-8')));
        $data['seo_keyword'] = $manga_info->name . ' ' . $chapter_info[0]->name;

        $data['breadcrums'] = [
            ['title' => 'HOME', 'link' => '/'],
            ['title' => $manga_info->name, 'link' => base_url('manhwa/' . $manga_info->slug)],
        ];

        $data['manga_info'] = $manga_info;
        $data['chapter_info'] = $chapter_info[0];
        $data['chapter_info']->nextChapter = $nextChapter;
        $data['chapter_info']->prevChapter = $prevChapter;
        $data['allPages'] = $allPages;

        // Reading history moved to /api/track-view (JS-based, works with CF cache)

        return view('reader', $data);
    }

    public function doujin($manga_slug = null, $chapter_slug = null)
    {
        // Same as read with doujin view
        return $this->read($manga_slug);
    }

    public function apiAddChapter()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);

        if (empty($data)) {
            return $this->response->setBody('0');
        }

        // Validate required fields
        $mangaId = (int)($data['mangaId'] ?? 0);
        $chapterNumber = $data['chapterNumber'] ?? null;
        $pages = $data['pages'] ?? [];

        if (!$mangaId || !$chapterNumber) {
            return $this->response->setBody('0');
        }

        // Verify manga exists
        $manga = $this->db->table('manga')->where('id', $mangaId)->get()->getRow();
        if (!$manga) {
            return $this->response->setBody('0');
        }

        $this->db->table('chapter')->insert([
            'manga_id'   => $mangaId,
            'is_show'    => 0,
            'is_crawling' => 2,
            'slug'       => 'chapter-' . $chapterNumber,
            'number'     => $chapterNumber,
            'name'       => 'Chapter ' . $chapterNumber,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id'    => 1,
            'volume'     => 0,
        ]);
        $last_id = $this->db->insertID();

        if (!empty($pages)) {
            $now = date('Y-m-d H:i:s');
            $pageData = [];
            foreach ($pages as $k => $page) {
                $pageData[] = [
                    'slug'       => $k,
                    'image'      => $page,
                    'external'   => 1,
                    'chapter_id' => $last_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $this->db->table('page')->insertBatch($pageData);
        }

        return $this->response->setBody('1');
    }

    public function filter($slug = null, $page = null)
    {
        if (!$slug) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $order_by = $this->request->getGet('order_by') ?? 'lastest';
        $data = $this->getCommonData();
        $data['ads'] = $this->getAds(1);

        $page = (int)($page ?? 0);

        $category = $this->db->table('category')->where('slug', $slug)->get()->getRow();
        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['heading_title'] = 'Read ' . $category->name . ' Manga Online with High Quality at Manga18.Club';
        $data['seo_title'] = $data['heading_title'];
        $data['seo_description'] = $data['heading_title'];
        $data['seo_keyword'] = $category->name . ' Manga';

        $url = base_url('/manga-list/' . $category->slug);
        if ($page > 1) $url .= '/' . $page;

        $total = $this->db->query(
            'SELECT COUNT(DISTINCT category_manga.manga_id) as total FROM manga LEFT JOIN category_manga ON manga.id = category_manga.manga_id WHERE category_manga.category_id = ?',
            [$category->id]
        )->getRow()->total;

        $offset = max(($page * 20) - 20, 0);

        $orderClause = match ($order_by) {
            'name' => 'ORDER BY manga.name ASC',
            'views' => 'ORDER BY manga.views DESC',
            default => 'ORDER BY manga.update_at DESC',
        };

        $mangaList = $this->db->query(
            "SELECT manga.* FROM manga LEFT JOIN category_manga ON manga.id = category_manga.manga_id WHERE category_manga.category_id = ? {$orderClause} LIMIT ?, 20",
            [$category->id, $offset]
        )->getResult();

        $data['mangaList'] = $mangaList;
        $data['category'] = $category;
        $data['total'] = $total;
        $data['url'] = $url;
        $data['order_by'] = $order_by;
        $data['current_page'] = max($page, 1);
        $data['total_pages'] = (int) ceil($total / 20);
        $data['base_url'] = '/manga-list/' . $category->slug . '/';
        $data['links'] = view('pager/segment_pager', $data);

        return view('filter', $data);
    }

    public function listManga($page = null)
    {
        $order_by = $this->request->getGet('order_by') ?? 'lastest';
        $data = $this->getCommonData();
        $data['ads'] = $this->getAds(1);

        $page = (int)($page ?? 0);

        $data['heading_title'] = 'Manga List';
        $data['seo_title'] = 'Read Manga Online with High Quality at Manga18.Club';
        $data['seo_description'] = $data['seo_title'];
        $data['seo_keyword'] = 'List Manga - Manga18.Club';

        $url = base_url('list-manga');
        if ($page > 1) $url .= '/' . $page;

        $search = $this->request->getGet('search');
        $author = $this->request->getGet('author');
        $artist = $this->request->getGet('artist');
        $queryParams = [];
        if ($search) $queryParams['search'] = $search;
        if ($author) $queryParams['author'] = $author;
        if ($artist) $queryParams['artist'] = $artist;
        if ($queryParams) {
            $url .= '?' . http_build_query($queryParams);
        }

        if ($author) {
            $data['heading_title'] = 'Author: ' . $author;
            $data['seo_title'] = 'Manga by ' . $author . ' - Manga18.Club';
        } elseif ($artist) {
            $data['heading_title'] = 'Artist: ' . $artist;
            $data['seo_title'] = 'Manga by ' . $artist . ' - Manga18.Club';
        }

        // Count total
        $countBuilder = $this->db->table('manga')->where('is_public', 1);
        if ($search) {
            $countBuilder->groupStart()->like('name', $search)->orLike('otherNames', $search)->groupEnd();
        }
        if ($author) {
            $countBuilder->join('author_manga', 'author_manga.manga_id = manga.id')
                         ->join('author', 'author.id = author_manga.author_id')
                         ->like('author.name', $author)
                         ->where('author_manga.type', 1);
        }
        if ($artist) {
            $countBuilder->join('author_manga', 'author_manga.manga_id = manga.id')
                         ->join('author', 'author.id = author_manga.author_id')
                         ->like('author.name', $artist)
                         ->where('author_manga.type', 2);
        }
        $total = $countBuilder->countAllResults();
        $offset = max(($page * 20) - 20, 0);

        // Get results (single builder)
        $builder = $this->db->table('manga')->select('manga.*')->where('is_public', 1);
        if ($search) {
            $builder->groupStart()->like('name', $search)->orLike('otherNames', $search)->groupEnd();
        }
        if ($author) {
            $builder->join('author_manga', 'author_manga.manga_id = manga.id')
                    ->join('author', 'author.id = author_manga.author_id')
                    ->like('author.name', $author)
                    ->where('author_manga.type', 1);
        }
        if ($artist) {
            $builder->join('author_manga', 'author_manga.manga_id = manga.id')
                    ->join('author', 'author.id = author_manga.author_id')
                    ->like('author.name', $artist)
                    ->where('author_manga.type', 2);
        }
        $orderField = match ($order_by) {
            'name' => ['name', 'ASC'],
            'views' => ['views', 'DESC'],
            default => ['update_at', 'DESC'],
        };
        $mangaList = $builder->orderBy($orderField[0], $orderField[1])->limit(20, $offset)->get()->getResult();

        $data['url'] = $url;
        $data['mangaList'] = $mangaList;
        $data['order_by'] = $order_by;
        $data['total'] = $total;
        $data['current_page'] = max($page, 1);
        $data['total_pages'] = (int) ceil($total / 20);
        $baseUrl = '/list-manga/';
        if ($queryParams) $baseUrl .= '?' . http_build_query($queryParams) . '&page=';
        $data['base_url'] = $baseUrl;
        $data['links'] = view('pager/segment_pager', $data);

        return view('filter', $data);
    }

    /**
     * GET /api/manga-state/{id} — returns rating, bookmark, vote state for CF cache
     */
    public function mangaState($id = null)
    {
        $mangaId = (int) $id;
        if (!$mangaId) {
            return $this->response->setJSON(['status' => 0]);
        }

        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('CDN-Cache-Control', 'no-store');

        // Rating info
        $ratingInfo = $this->db->query(
            'SELECT AVG(score) as rate, COUNT(*) as total FROM item_ratings WHERE item_id = ?',
            [$mangaId]
        )->getRow();

        $avg = number_format((float)($ratingInfo->rate ?? 0), 2, '.', ',');
        $totalRate = (int)($ratingInfo->total ?? 0);

        // Check if current IP already voted
        $ip = $this->request->getIPAddress();
        $voted = $this->db->table('item_ratings')
            ->where('item_id', $mangaId)
            ->where('ip_address', $ip)
            ->countAllResults() > 0;

        // Bookmark info
        $totalBookmarks = (int) $this->db->table('bookmarks')
            ->where('manga_id', $mangaId)
            ->countAllResults();

        $isBookmarked = false;
        if ($this->is_logged == 1) {
            $isBookmarked = $this->db->table('bookmarks')
                ->where('manga_id', $mangaId)
                ->where('user_id', $this->user_info->id)
                ->countAllResults() > 0;
        }

        return $this->response->setJSON([
            'status'          => 1,
            'avg'             => $avg,
            'total_rate'      => $totalRate,
            'voted'           => $voted,
            'is_bookmarked'   => $isBookmarked,
            'total_bookmarks' => $totalBookmarks,
        ]);
    }

    /**
     * POST /api/track-view — increment manga + chapter view count
     * Called via JS so it works even on CF-cached pages
     */
    public function trackView()
    {
        $mangaId     = (int) $this->request->getPost('manga_id');
        $chapterSlug = trim($this->request->getPost('chapter_slug') ?? '');

        if (!$mangaId) {
            return $this->response->setJSON(['status' => 0]);
        }

        // Increment manga views
        $this->db->table('manga')->where('id', $mangaId)
            ->set('views', 'views + 1', false)
            ->set('view_day', 'view_day + 1', false)
            ->set('view_month', 'view_month + 1', false)
            ->update();

        // Increment chapter view
        if ($chapterSlug) {
            $this->db->table('chapter')
                ->where('manga_id', $mangaId)
                ->where('slug', $chapterSlug)
                ->set('view', '`view` + 1', false)
                ->update();
        }

        return $this->response->setJSON(['status' => 1]);
    }

    public function votes()
    {
        $manga_id = (int)$this->request->getPost('manga_id');
        $score = (int)$this->request->getPost('score');

        if (!$manga_id || $score < 1 || $score > 10) {
            return $this->response->setBody('0');
        }

        $ip = $this->request->getIPAddress();

        // Prevent duplicate vote per IP per manga
        $existing = $this->db->table('item_ratings')
            ->where('item_id', $manga_id)
            ->where('ip_address', $ip)
            ->countAllResults();

        if ($existing > 0) {
            return $this->response->setBody('1'); // Silently accept
        }

        $this->db->table('item_ratings')->insert([
            'item_id'    => $manga_id,
            'score'      => $score,
            'ip_address' => $ip,
        ]);

        return $this->response->setBody('1');
    }

    public function bookmarks()
    {
        if ($this->is_logged != 1) {
            return $this->response->setBody('0');
        }

        $user_id = $this->user_info->id;
        $manga_id = (int)$this->request->getPost('manga_id');

        if (!$manga_id) {
            return $this->response->setBody('0');
        }

        $existing = $this->db->table('bookmarks')
            ->where('manga_id', $manga_id)
            ->where('user_id', $user_id)
            ->get()
            ->getResult();

        if (!$existing) {
            $this->db->table('bookmarks')->insert([
                'manga_id' => $manga_id,
                'status'   => 'currently-reading',
                'user_id'  => $user_id,
            ]);
            return $this->response->setBody('1');
        }

        return $this->response->setBody('0');
    }

    public function unbookmarks()
    {
        if ($this->is_logged != 1) {
            return $this->response->setBody('0');
        }

        $user_id = $this->user_info->id;
        $manga_id = (int)$this->request->getPost('manga_id');

        if (!$manga_id) {
            return $this->response->setBody('0');
        }

        $this->db->table('bookmarks')
            ->where('manga_id', $manga_id)
            ->where('user_id', $user_id)
            ->delete();

        return $this->response->setBody('1');
    }

    public function reportCaptcha()
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $session = service('session');
        $session->set('report_captcha', strtolower($code));
        $session->set('report_captcha_time', time());

        $width = 160;
        $height = 50;
        $img = imagecreatetruecolor($width, $height);

        // Background
        $bg = imagecolorallocate($img, 28, 51, 46);
        imagefill($img, 0, 0, $bg);

        // Noise lines
        for ($i = 0; $i < 6; $i++) {
            $lineColor = imagecolorallocate($img, random_int(40, 120), random_int(60, 130), random_int(50, 110));
            imageline($img, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $lineColor);
        }

        // Noise dots
        for ($i = 0; $i < 80; $i++) {
            $dotColor = imagecolorallocate($img, random_int(60, 180), random_int(60, 180), random_int(60, 180));
            imagesetpixel($img, random_int(0, $width), random_int(0, $height), $dotColor);
        }

        // Draw characters
        $fontFile = FCPATH . 'captcha_font.ttf';
        $useGdFont = !file_exists($fontFile);

        for ($i = 0; $i < strlen($code); $i++) {
            $r = random_int(100, 230);
            $g = random_int(180, 255);
            $b = random_int(100, 220);
            $color = imagecolorallocate($img, $r, $g, $b);
            $x = 14 + $i * 28;
            $y = random_int(28, 38);

            if ($useGdFont) {
                $fontSize = 5; // GD built-in font
                imagestring($img, $fontSize, $x, $y - 20, $code[$i], $color);
            } else {
                $fontSize = random_int(20, 26);
                $angle = random_int(-20, 20);
                imagettftext($img, $fontSize, $angle, $x, $y, $color, $fontFile, $code[$i]);
            }
        }

        ob_start();
        imagepng($img);
        $imageData = ob_get_clean();
        imagedestroy($img);

        return $this->response
            ->setStatusCode(200)
            ->setContentType('image/png')
            ->removeHeader('Cache-Control')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setBody($imageData);
    }

    public function reportChapter()
    {
        $chapterId = (int) $this->request->getPost('chapter_id');
        $mangaId = (int) $this->request->getPost('manga_id');
        $allowedReasons = ['broken_images', 'wrong_images', 'missing_pages', 'low_quality', 'wrong_order', 'other'];
        $reason = $this->request->getPost('reason') ?? 'broken_images';
        if (!in_array($reason, $allowedReasons)) {
            $reason = 'broken_images';
        }
        $note = trim($this->request->getPost('note') ?? '');

        if (!$chapterId || !$mangaId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing data']);
        }

        // Verify captcha
        $session = service('session');
        $captchaInput = strtolower(trim($this->request->getPost('captcha') ?? ''));
        $captchaStored = $session->get('report_captcha');
        $captchaTime = $session->get('report_captcha_time');
        $session->remove(['report_captcha', 'report_captcha_time']); // one-time use

        if (!$captchaStored || !$captchaInput || $captchaInput !== $captchaStored) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid captcha']);
        }
        // Expire after 5 minutes
        if (!$captchaTime || (time() - $captchaTime) > 300) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Captcha expired']);
        }

        // Rate limit: 1 report per IP per chapter per day
        $ip = $this->request->getIPAddress();
        $existing = $this->db->table('chapter_report')
            ->where('chapter_id', $chapterId)
            ->where('ip_address', $ip)
            ->where('created_at >', date('Y-m-d H:i:s', strtotime('-1 day')))
            ->countAllResults();

        if ($existing > 0) {
            return $this->response->setJSON(['status' => 'ok', 'message' => 'Already reported']);
        }

        $userId = null;
        if ($this->is_logged && isset($this->user_info->id)) {
            $userId = (int) $this->user_info->id;
        }

        $this->db->table('chapter_report')->insert([
            'chapter_id' => $chapterId,
            'manga_id'   => $mangaId,
            'user_id'    => $userId,
            'reason'     => $reason,
            'note'       => $note,
            'ip_address' => $ip,
            'status'     => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => 'ok', 'message' => 'Report submitted']);
    }
}
