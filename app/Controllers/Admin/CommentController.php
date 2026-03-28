<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CommentModel;

class CommentController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new CommentModel();
    }

    public function index()
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $total = $this->db->table('comments')->countAllResults(false);
        $search = $this->request->getGet('type') ?? '';
        $builder = $this->db->table('comments c')
            ->select('c.*, u.username')
            ->join('users u', 'u.id = c.user_id', 'left');
        if ($search && in_array($search, ['manga', 'chapter'])) {
            $builder->where('c.post_type', $search);
        }
        $total = $builder->countAllResults(false);
        $comments = $builder->orderBy('c.id', 'DESC')->limit($perPage, $offset)->get()->getResult();

        // Fetch related manga/chapter names
        foreach ($comments as &$c) {
            if ($c->post_type === 'manga') {
                $m = $this->db->table('manga')->select('name')->where('id', $c->post_id)->get()->getRow();
                $c->post_name = $m ? $m->name : 'N/A';
                $c->post_link = '/admin/manga/edit/' . $c->post_id;
            } else {
                $ch = $this->db->table('chapter')->select('name, number, manga_id')->where('id', $c->post_id)->get()->getRow();
                $c->post_name = $ch ? 'Ch.' . ($ch->number ?? $ch->name) : 'N/A';
                $c->post_link = $ch ? '/admin/chapters/edit/' . $c->post_id : '#';
            }
        }

        $data = [
            'title'    => 'Comments',
            'comments' => $comments,
            'page'     => $page,
            'perPage'  => $perPage,
            'total'    => $total,
            'type'     => $search,
        ];
        return view('admin/comment/index', $data);
    }

    public function delete($id)
    {
        // Delete child comments first
        $this->model->where('parent_comment', $id)->delete();
        $this->model->delete($id);
        return redirect()->to('/admin/comments')->with('success', 'Comment deleted.');
    }
}
