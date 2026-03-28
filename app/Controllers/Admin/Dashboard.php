<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $data = [
            'title'          => 'Dashboard',
            'totalManga'     => $this->db->table('manga')->countAllResults(),
            'totalChapter'   => $this->db->table('chapter')->countAllResults(),
            'totalUser'      => $this->db->table('users')->countAllResults(),
            'totalComment'   => $this->db->table('comments')->countAllResults(),
            'totalCategory'  => $this->db->table('category')->countAllResults(),
            'totalTag'       => $this->db->table('tag')->countAllResults(),
            'totalAuthor'    => $this->db->table('author')->countAllResults(),
            'latestManga'    => $this->db->table('manga')->orderBy('id', 'DESC')->limit(10)->get()->getResult(),
            'latestComments' => $this->db->query('SELECT c.*, u.username FROM comments c LEFT JOIN users u ON u.id = c.user_id ORDER BY c.id DESC LIMIT 10')->getResult(),
            'latestChapters' => $this->db->query('SELECT ch.*, m.name as manga_name, m.slug as manga_slug FROM chapter ch LEFT JOIN manga m ON m.id = ch.manga_id ORDER BY ch.created_at DESC LIMIT 15')->getResult(),
        ];

        return view('admin/dashboard', $data);
    }
}
