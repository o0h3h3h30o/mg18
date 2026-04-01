<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {

        $data = $this->getCommonData();
        $data['ads'] = $this->getAds(1);
        $data['vip'] = 0;

        $sql = 'SELECT m.id as manga_id, m.is_new as is_new, m.slug as manga_slug,
                m.cover as manga_cover, m.name as manga_name, m.hot as hot,
                m.chapter_1, m.chapter_2, m.chap_1_slug, m.chap_2_slug,
                m.time_chap_1, m.flag_chap_1, m.flag_chap_2, m.time_chap_2
                FROM manga m WHERE m.is_public = 1 ORDER BY update_at DESC LIMIT 80';

        $data['listChapters'] = $this->db->query($sql)->getResult();
        $data['bookmarks'] = [];

        $data['top_day'] = $this->getTopDay();
        $data['top_month'] = $this->getTopMonth();

        $topAll = $this->db->query(
            'SELECT m.id as manga_id, m.name, m.slug, m.views as view, m.time_chap_1 FROM manga m ORDER BY m.views DESC LIMIT 10'
        )->getResult();
        $data['top_all'] = $topAll;

        $total = $this->db->table('manga')->where('is_public', 1)->countAllResults();

        $data['current_page'] = 1;
        $data['total_pages'] = (int) ceil($total / 32);
        $data['base_url'] = '/latest-release/';
        $data['links'] = view('pager/segment_pager', $data);

        // Batch SEO options in 1 query instead of 3
        $seoOptions = $this->getOptionsByKeys(['seo.title', 'seo.description', 'seo.keywords']);
        $data['heading_title'] = $seoOptions['seo.title'] ?? '';
        $data['seo_title'] = $seoOptions['seo.title'] ?? '';
        $data['seo_description'] = $seoOptions['seo.description'] ?? '';
        $data['seo_keyword'] = $seoOptions['seo.keywords'] ?? '';

        return view('home', $data);
    }

    public function updateChapter()
    {
        $this->db->query('UPDATE chapter SET is_show = 1');

        $page = (int)($this->request->getGet('page') ?? 0);
        $offset = max(($page * 20) - 20, 0);

        $sql = 'SELECT m.id as manga_id, m.is_new, m.slug as manga_slug,
                m.cover as manga_cover, m.name as manga_name, m.hot,
                (SELECT c.id FROM chapter c WHERE c.manga_id = m.id ORDER BY c.id DESC LIMIT 1) as chapter_id
                FROM manga m ORDER BY chapter_id DESC LIMIT ?, 20';

        $listChapters = $this->db->query($sql, [$offset])->getResult();

        foreach ($listChapters as $key => $value) {
            $chapters = $this->db->query(
                'SELECT chapter.number as chapter_number, chapter.name as chapter_name,
                 chapter.slug as chapter_slug, created_at as chapter_created_at
                 FROM chapter WHERE manga_id = ? GROUP BY number
                 ORDER BY CAST(number AS UNSIGNED) DESC LIMIT 3',
                [$value->manga_id]
            )->getResult();

            $sortedChapters = [];
            foreach ($chapters as $chapter) {
                $sortedChapters[$chapter->chapter_number] = $chapter;
            }
            array_multisort(array_keys($sortedChapters), SORT_DESC, SORT_NATURAL, $sortedChapters);
            $listChapters[$key]->chapters = array_values($sortedChapters);
        }

        foreach ($listChapters as $value) {
            if (isset($value->chapters[0])) {
                $updateData = [
                    'update_at'  => (int)strtotime($value->chapters[0]->chapter_created_at),
                    'chapter_1'  => floatval($value->chapters[0]->chapter_number),
                    'chap_1_slug' => $value->chapters[0]->chapter_slug,
                    'time_chap_1' => strtotime($value->chapters[0]->chapter_created_at),
                ];
                if (isset($value->chapters[1])) {
                    $updateData['chapter_2'] = floatval($value->chapters[1]->chapter_number);
                    $updateData['chap_2_slug'] = $value->chapters[1]->chapter_slug;
                    $updateData['time_chap_2'] = strtotime($value->chapters[1]->chapter_created_at);
                }
                $this->db->table('manga')->where('id', $value->manga_id)->update($updateData);
            }
        }

        return $this->response->setJSON(['status' => 'ok', 'data' => $listChapters]);
    }

    public function updateRate()
    {
        $page = (int)($this->request->getGet('page') ?? 0);
        $offset = max(($page * 20) - 20, 0);

        $sql = 'SELECT m.id as manga_id FROM manga m
                ORDER BY (SELECT c.id FROM chapter c WHERE c.manga_id = m.id ORDER BY c.id DESC LIMIT 1) DESC
                LIMIT ?, 30';

        $listChapters = $this->db->query($sql, [$offset])->getResult();
        $ids = array_column($listChapters, 'manga_id');

        if (!empty($ids)) {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            // Batch: get AVG + COUNT in 1 query
            $ratings = $this->db->query(
                "SELECT item_id, AVG(score) as rate, COUNT(*) as total
                 FROM item_ratings WHERE item_id IN ({$ph}) GROUP BY item_id",
                $ids
            )->getResult();

            $rateMap = [];
            foreach ($ratings as $r) {
                $rateMap[$r->item_id] = $r;
            }

            foreach ($ids as $mangaId) {
                $r = $rateMap[$mangaId] ?? null;
                $this->db->table('manga')->where('id', $mangaId)->update([
                    'rate' => $r ? number_format((float)$r->rate, 2, '.', ',') : '0.00',
                    'vote_number' => $r ? (int)$r->total : 0,
                ]);
            }
        }

        return $this->response->setJSON(['status' => 'ok']);
    }

    public function getNotification()
    {
        if ($this->is_logged == 1) {
            $notifications = $this->db->table('notifications n')
                ->select('n.*, u.username as actor_name')
                ->join('users u', 'u.id = n.actor_id', 'left')
                ->where('n.user_id', $this->user_info->id)
                ->where('n.is_read', 0)
                ->orderBy('n.created_at', 'DESC')
                ->limit(20)
                ->get()
                ->getResult();

            $unreadCount = $this->db->table('notifications')
                ->where('user_id', $this->user_info->id)
                ->where('is_read', 0)
                ->countAllResults();

            // Build display fields
            foreach ($notifications as &$n) {
                $n->time_ago = time_elapsed_string($n->created_at);
                $actor = $n->actor_name ?: 'Someone';

                // Build link
                if ($n->manga_slug) {
                    $n->link = $n->chapter_slug
                        ? '/manhwa/' . $n->manga_slug . '/' . $n->chapter_slug
                        : '/manhwa/' . $n->manga_slug;
                } else {
                    $n->link = '/';
                }

                // Build display title & icon
                switch ($n->type) {
                    case 'reply':
                        $n->title = '@' . $actor . ' replied to your comment';
                        $n->icon_class = 'ti-comment';
                        break;
                    case 'mention':
                        $n->title = '@' . $actor . ' mentioned you in a comment';
                        $n->icon_class = 'ti-comment-alt';
                        break;
                    case 'report_resolved':
                        $n->title = 'Report Resolved';
                        $n->icon_class = 'ti-check';
                        break;
                    default:
                        $n->title = 'Notification';
                        $n->icon_class = 'ti-bell';
                }

                // preview as message
                $n->message = $n->preview ?: '';
            }

            return $this->response->setJSON([
                'status' => 1,
                'notifications' => $notifications,
                'count' => $unreadCount,
            ]);
        }

        return $this->response->setJSON([
            'status' => 0,
            'count' => 0,
            'notifications' => [],
        ]);
    }

    /**
     * GET /api/me — returns current user info for CF-cached pages
     */
    public function me()
    {
        // Bypass CF cache
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('CDN-Cache-Control', 'no-store');

        if ($this->is_logged == 1) {
            $unreadCount = $this->db->table('notifications')
                ->where('user_id', $this->user_info->id)
                ->where('is_read', 0)
                ->countAllResults();

            return $this->response->setJSON([
                'logged'      => true,
                'username'    => $this->user_info->username,
                'email'       => $this->user_info->email,
                'notif_count' => (int)$unreadCount,
            ]);
        }

        return $this->response->setJSON(['logged' => false]);
    }

    /**
     * GET /notification/go/{id} — mark single notification as read & redirect
     */
    public function goNotification($id = null)
    {
        if ($this->is_logged == 1 && $id) {
            $notif = $this->db->table('notifications')
                ->where('id', (int) $id)
                ->where('user_id', $this->user_info->id)
                ->get()->getRow();

            if ($notif) {
                // Mark as read
                $this->db->table('notifications')
                    ->where('id', (int) $id)
                    ->update(['is_read' => 1]);

                // Build redirect link
                $link = '/';
                if ($notif->manga_slug) {
                    $link = $notif->chapter_slug
                        ? '/manhwa/' . $notif->manga_slug . '/' . $notif->chapter_slug
                        : '/manhwa/' . $notif->manga_slug;
                }
                return redirect()->to($link);
            }
        }
        return redirect()->to('/notification');
    }

    public function markNotificationsRead()
    {
        if ($this->is_logged == 1) {
            $this->db->table('notifications')
                ->where('user_id', $this->user_info->id)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            return $this->response->setJSON(['status' => 1]);
        }
        return $this->response->setJSON(['status' => 0]);
    }

    public function search()
    {
        $search = $this->request->getGet('search');
        if ($search) {
            $listManga = $this->db->table('manga')
                ->select('id, name, slug, otherNames')
                ->like('name', $search)
                ->orLike('otherNames', $search)
                ->limit(10)
                ->get()
                ->getResult();

            return $this->response->setJSON(['status' => 0, 'data' => $listManga]);
        }

        return $this->response->setJSON(['status' => 0, 'data' => []]);
    }

    public function search2()
    {
        $search = $this->request->getGet('search');
        if ($search) {
            $listManga = $this->db->table('manga')
                ->select('id, name, slug, otherNames, chapter_1')
                ->like('name', $search)
                ->orLike('otherNames', $search)
                ->limit(10)
                ->get()
                ->getResult();

            foreach ($listManga as $k => $manga) {
                $listManga[$k]->name = $manga->name . ' - ' . $manga->chapter_1;
            }

            return $this->response->setJSON(['status' => 0, 'data' => $listManga]);
        }

        return $this->response->setJSON(['status' => 0, 'data' => []]);
    }

    public function resetDay()
    {
        $this->db->query('UPDATE manga SET view_day = 0');
        return $this->response->setJSON(['status' => 'ok']);
    }

    public function resetMonth()
    {
        $this->db->query('UPDATE manga SET view_month = 0');
        return $this->response->setJSON(['status' => 'ok']);
    }

    public function feed()
    {
        $sql = 'SELECT m.id as manga_id, m.is_new, m.slug as manga_slug,
                m.cover as manga_cover, m.name as manga_name, m.hot,
                m.chapter_1, m.chapter_2, m.chap_1_slug, m.chap_2_slug,
                m.time_chap_1, m.time_chap_2
                FROM manga m ORDER BY update_at DESC LIMIT 100';

        $listChapters = $this->db->query($sql)->getResult();

        $datePublished = date(DATE_ATOM);
        foreach ($listChapters as $key => $value) {
            $time = date('Y-m-d H:i:s', $value->time_chap_1);
            $listChapters[$key]->publishtime = date(DATE_ATOM, strtotime($time));
        }

        $data['listChapters'] = $listChapters;
        $data['datePublished'] = $datePublished;

        return view('feed', $data);
    }

    public function updatePublishChapter()
    {
        $chapter_id = (int)($this->request->getGet('chapter_id') ?? 0);
        if (!$chapter_id) {
            return $this->response->setBody('Error');
        }

        $chapter_info = $this->db->table('chapter')->where('id', $chapter_id)->get()->getRow();
        if (!$chapter_info || $chapter_info->id != $chapter_id) {
            return $this->response->setBody('Not found');
        }

        $this->db->table('chapter')->where('id', $chapter_id)->update(['is_show' => 1]);

        $chapters = $this->db->query(
            'SELECT chapter.number as chapter_number, chapter.name as chapter_name,
             chapter.slug as chapter_slug, created_at as chapter_created_at,
             chapter.flag as chapter_flag
             FROM chapter WHERE manga_id = ? AND is_show = 1
             GROUP BY number ORDER BY CAST(number AS UNSIGNED) DESC LIMIT 3',
            [$chapter_info->manga_id]
        )->getResult();

        $sortedChapters = [];
        foreach ($chapters as $chapter) {
            $sortedChapters[$chapter->chapter_number] = $chapter;
        }
        array_multisort(array_keys($sortedChapters), SORT_DESC, SORT_NATURAL, $sortedChapters);
        $sortedChapters = array_values($sortedChapters);

        if (isset($sortedChapters[0])) {
            $updateData = [
                'update_at'   => time() + 4 * 3600,
                'chapter_1'   => floatval($sortedChapters[0]->chapter_number),
                'chap_1_slug' => $sortedChapters[0]->chapter_slug,
                'time_chap_1' => strtotime($sortedChapters[0]->chapter_created_at),
                'flag_chap_1' => $sortedChapters[0]->chapter_flag,
            ];
            if (isset($sortedChapters[1])) {
                $updateData['chapter_2']   = floatval($sortedChapters[1]->chapter_number);
                $updateData['chap_2_slug'] = $sortedChapters[1]->chapter_slug;
                $updateData['time_chap_2'] = strtotime($sortedChapters[1]->chapter_created_at);
                $updateData['flag_chap_2'] = $sortedChapters[1]->chapter_flag;
            }
            $this->db->table('manga')->where('id', $chapter_info->manga_id)->update($updateData);
        }

        return $this->response->setBody('Updated successfully');
    }

    public function home2()
    {
        $data = $this->getCommonData();
        $data['top_day'] = $this->getTopDay();
        $data['top_month'] = $this->getTopMonth();
        $data['top_all'] = $this->db->query(
            'SELECT m.id as manga_id, m.name, m.slug, m.views as view, m.time_chap_1 FROM manga m ORDER BY m.views DESC LIMIT 10'
        )->getResult();
        return view('home2', $data);
    }
}
