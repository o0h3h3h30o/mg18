<?php

namespace App\Controllers;

class User extends BaseController
{
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        if ($this->is_logged == 0) {
            header('Location: ' . base_url('login'));
            exit;
        }
    }

    public function index()
    {
        $data = $this->getCommonData();

        $data['heading_title'] = 'Manga18 Profile';
        $data['seo_title'] = 'Manga18 Profile';
        $data['seo_description'] = 'Manga18 Profile';
        $data['seo_keyword'] = 'Manga18 Profile';

        $bookmarks = $this->db->table('bookmarks')
            ->select('bookmarks.*, manga.name, manga.slug, manga.chapter_1, manga.chap_1_slug')
            ->join('manga', 'bookmarks.manga_id = manga.id')
            ->where('bookmarks.user_id', $this->user_info->id)
            ->groupBy('bookmarks.manga_id')
            ->orderBy('bookmarks.id', 'DESC')
            ->limit(5)
            ->get()
            ->getResult();

        $data['bookmarks'] = $bookmarks;

        // Stats
        $data['stats'] = [
            'bookmarks' => $this->db->table('bookmarks')->where('user_id', $this->user_info->id)->countAllResults(),
            'comments'  => $this->db->table('comments')->where('user_id', $this->user_info->id)->countAllResults(),
            'history'   => $this->db->table('reading_history')->where('user_id', $this->user_info->id)->countAllResults(),
        ];

        return view('profile', $data);
    }

    public function history($page = null)
    {
        $data = $this->getCommonData();
        $page = (int)($page ?? 0);
        $offset = max(($page * 20) - 20, 0);

        $data['heading_title'] = 'Reading History - Manga18';
        $data['seo_title'] = 'Reading History';
        $data['seo_description'] = 'Your reading history';
        $data['seo_keyword'] = 'reading history manga18';

        $total = $this->db->table('reading_history')
            ->where('user_id', $this->user_info->id)
            ->countAllResults();

        $history = $this->db->table('reading_history h')
            ->select('h.*, m.name as manga_name, m.slug as manga_slug,
                      c.name as chapter_name, c.number as chapter_number, c.slug as chapter_slug')
            ->join('manga m', 'm.id = h.manga_id')
            ->join('chapter c', 'c.id = h.chapter_id')
            ->where('h.user_id', $this->user_info->id)
            ->orderBy('h.read_at', 'DESC')
            ->limit(20, $offset)
            ->get()->getResult();

        $data['history'] = $history;
        $data['current_page'] = max($page, 1);
        $data['total_pages'] = (int) ceil($total / 20);
        $data['base_url'] = '/history/';

        return view('history', $data);
    }

    public function deleteHistory()
    {
        $mangaId = (int) $this->request->getPost('manga_id');
        if ($mangaId > 0) {
            $this->db->table('reading_history')
                ->where('user_id', $this->user_info->id)
                ->where('manga_id', $mangaId)
                ->delete();
            return $this->response->setJSON(['status' => 1]);
        }
        return $this->response->setJSON(['status' => 0, 'message' => 'Invalid manga']);
    }

    public function notification($page = null)
    {
        $data = $this->getCommonData();
        $page = (int)($page ?? 0);
        $offset = max(($page * 20) - 20, 0);
        $filter = $this->request->getGet('filter') ?? 'all'; // all, unread, read

        $data['heading_title'] = 'Notifications - Manga18';
        $data['seo_title'] = 'Notifications';
        $data['seo_description'] = 'Your notifications';
        $data['seo_keyword'] = 'notifications manga18';

        // Count for tabs
        $data['count_all'] = $this->db->table('notifications')
            ->where('user_id', $this->user_info->id)
            ->countAllResults();
        $data['count_unread'] = $this->db->table('notifications')
            ->where('user_id', $this->user_info->id)
            ->where('is_read', 0)
            ->countAllResults();

        // Build query with filter
        $builder = $this->db->table('notifications n')
            ->select('n.*, u.username as actor_name')
            ->join('users u', 'u.id = n.actor_id', 'left')
            ->where('n.user_id', $this->user_info->id);

        if ($filter === 'unread') {
            $builder->where('n.is_read', 0);
        } elseif ($filter === 'read') {
            $builder->where('n.is_read', 1);
        }

        $total = $builder->countAllResults(false);

        $notifications = $builder
            ->orderBy('n.created_at', 'DESC')
            ->limit(20, $offset)
            ->get()->getResult();

        // Build display fields
        foreach ($notifications as &$n) {
            $actor = $n->actor_name ?: 'Someone';
            if ($n->manga_slug) {
                $n->link = $n->chapter_slug
                    ? '/manhwa/' . $n->manga_slug . '/' . $n->chapter_slug
                    : '/manhwa/' . $n->manga_slug;
            } else {
                $n->link = '/';
            }
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
            $n->message = $n->preview ?: '';
        }

        $data['notifications'] = $notifications;
        $data['filter'] = $filter;
        $data['current_page'] = max($page, 1);
        $data['total_pages'] = (int) ceil($total / 20);
        $data['base_url'] = '/notification/';

        return view('notification', $data);
    }

    public function bookmarks($page = null)
    {
        $data = $this->getCommonData();
        $page = (int)($page ?? 0);
        $offset = max(($page * 10) - 10, 0);

        $data['heading_title'] = 'Manga18 Bookmarks';
        $data['seo_title'] = 'Manga18 Bookmarks';
        $data['seo_description'] = 'Manga18 Bookmarks';
        $data['seo_keyword'] = 'Manga18 Bookmarks';

        $total = $this->db->table('bookmarks')
            ->join('manga', 'bookmarks.manga_id = manga.id')
            ->where('bookmarks.user_id', $this->user_info->id)
            ->groupBy('bookmarks.manga_id')
            ->get()
            ->getNumRows();

        $bookmarks = $this->db->table('bookmarks')
            ->select('bookmarks.*, manga.name, manga.slug, manga.chapter_1, manga.chap_1_slug')
            ->join('manga', 'bookmarks.manga_id = manga.id')
            ->where('bookmarks.user_id', $this->user_info->id)
            ->groupBy('bookmarks.manga_id')
            ->limit(10, $offset)
            ->get()
            ->getResult();

        foreach ($bookmarks as $key => $value) {
            $chapters = $this->db->query(
                'SELECT chapter.number as chapter_number, chapter.name as chapter_name,
                 chapter.slug as chapter_slug, created_at as chapter_created_at
                 FROM chapter WHERE manga_id = ?
                 GROUP BY number ORDER BY CAST(number AS UNSIGNED) DESC LIMIT 3',
                [$value->manga_id]
            )->getResult();

            $sortedChapters = [];
            foreach ($chapters as $chapter) {
                $sortedChapters[$chapter->chapter_number] = $chapter;
            }
            array_multisort(array_keys($sortedChapters), SORT_DESC, SORT_NATURAL, $sortedChapters);
            $sortedChapters = array_values($sortedChapters);

            if (isset($sortedChapters[0])) {
                $bookmarks[$key]->last_chapter = $sortedChapters[0];
            }
        }

        $data['bookmarks'] = $bookmarks;

        $data['current_page'] = max($page, 1);
        $data['total_pages'] = (int) ceil($total / 10);
        $data['base_url'] = '/bookmarks/';
        $data['links'] = view('pager/segment_pager', $data);

        return view('bookmarks', $data);
    }

    public function editProfile()
    {
        $data = $this->getCommonData();
        $data['heading_title'] = 'Edit Profile - Manga18';
        $data['seo_title'] = 'Edit Profile';
        $data['seo_description'] = 'Edit your profile';
        $data['seo_keyword'] = 'edit profile manga18';

        return view('edit_profile', $data);
    }

    public function updateProfile()
    {
        $rules = [
            'email' => 'required|valid_email|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/profile/edit')->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $email = trim($this->request->getPost('email'));

        // Check email unique (exclude self)
        $exists = $this->db->table('users')
            ->where('email', $email)
            ->where('id !=', $this->user_info->id)
            ->countAllResults();
        if ($exists) {
            return redirect()->to('/profile/edit')->with('error', 'Email already taken.');
        }

        $updateData = ['email' => $email];

        // Handle avatar upload
        $avatar = $this->request->getFile('avatar');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $maxSize = 2 * 1024 * 1024; // 2MB
            if ($avatar->getSize() > $maxSize) {
                return redirect()->to('/profile/edit')->with('error', 'Avatar file too large. Max 2MB.');
            }

            // Verify real image type via getimagesize (checks magic bytes, not extension/mime)
            $imgInfo = @getimagesize($avatar->getTempName());
            if ($imgInfo === false) {
                return redirect()->to('/profile/edit')->with('error', 'Invalid image file.');
            }

            $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
            if (!in_array($imgInfo[2], $allowedTypes)) {
                return redirect()->to('/profile/edit')->with('error', 'Invalid image format. Use JPG, PNG or WebP.');
            }

            // Check for PHP code hidden in image
            $content = file_get_contents($avatar->getTempName());
            if (preg_match('/<\?(php|=)/i', $content)) {
                return redirect()->to('/profile/edit')->with('error', 'Invalid file content.');
            }

            $uploadPath = FCPATH . 'uploads/users/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $targetFile = $uploadPath . $this->user_info->id . '.jpg';

            // Re-encode to clean JPG (strips any injected code)
            switch ($imgInfo[2]) {
                case IMAGETYPE_PNG:
                    $src = @imagecreatefrompng($avatar->getTempName());
                    break;
                case IMAGETYPE_WEBP:
                    $src = @imagecreatefromwebp($avatar->getTempName());
                    break;
                default:
                    $src = @imagecreatefromjpeg($avatar->getTempName());
            }

            if ($src) {
                $w = imagesx($src);
                $h = imagesy($src);
                $size = min($w, $h);
                $cropX = (int)(($w - $size) / 2);
                $cropY = (int)(($h - $size) / 2);

                // Full: 200x200
                $dst = imagecreatetruecolor(200, 200);
                imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, 200, 200, $size, $size);
                imagejpeg($dst, $targetFile, 85);
                imagedestroy($dst);

                // Thumb: 64x64
                $thumbFile = $uploadPath . $this->user_info->id . '-thumb.jpg';
                $dst = imagecreatetruecolor(64, 64);
                imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, 64, 64, $size, $size);
                imagejpeg($dst, $thumbFile, 80);
                imagedestroy($dst);

                imagedestroy($src);
                $updateData['avatar'] = 1;
            }
        }

        $this->db->table('users')
            ->where('id', $this->user_info->id)
            ->update($updateData);

        return redirect()->to('/profile/edit')->with('success', 'Profile updated successfully!');
    }

    public function changePassword()
    {
        $data = $this->getCommonData();
        $data['heading_title'] = 'Change Password - Manga18';
        $data['seo_title'] = 'Change Password';
        $data['seo_description'] = 'Change your password';
        $data['seo_keyword'] = 'change password manga18';

        return view('change_password', $data);
    }

    public function updatePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[6]|max_length[255]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/changePass')->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $currentPass = $this->request->getPost('current_password');
        $newPass     = $this->request->getPost('new_password');

        // Verify current password
        $user = $this->db->table('users')->where('id', $this->user_info->id)->get()->getRow();
        if (!password_verify($currentPass, $user->password)) {
            return redirect()->to('/changePass')->with('error', 'Current password is incorrect.');
        }

        $this->db->table('users')
            ->where('id', $this->user_info->id)
            ->update(['password' => password_hash($newPass, PASSWORD_BCRYPT)]);

        return redirect()->to('/changePass')->with('success', 'Password changed successfully!');
    }
}
