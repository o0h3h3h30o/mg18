<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuthorModel;

class AuthorController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new AuthorModel();
    }

    public function index()
    {
        $search = $this->request->getGet('q') ?? '';
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $builder = $this->db->table('author');
        if ($search) {
            $builder->like('name', $search);
        }

        $total = $builder->countAllResults(false);
        $authors = $builder->orderBy('name', 'ASC')->limit($perPage, $offset)->get()->getResult();

        $data = [
            'title'   => 'Authors / Artists',
            'authors' => $authors,
            'search'  => $search,
            'page'    => $page,
            'perPage' => $perPage,
            'total'   => $total,
        ];
        return view('admin/author/index', $data);
    }

    public function create()
    {
        return view('admin/author/form', ['title' => 'Add Author/Artist', 'item' => null]);
    }

    public function store()
    {
        if (!$this->validate(['name' => 'required|min_length[1]|max_length[255]'])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $name = trim($this->request->getPost('name'));
        $this->model->insert([
            'name'       => $name,
            'slug'       => url_title($name, '-', true),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/authors')->with('success', 'Author created.');
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/authors');
        return view('admin/author/form', ['title' => 'Edit Author/Artist', 'item' => $item]);
    }

    public function update($id)
    {
        if (!$this->validate(['name' => 'required|min_length[1]|max_length[255]'])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $name = trim($this->request->getPost('name'));
        $this->model->update($id, [
            'name'       => $name,
            'slug'       => url_title($name, '-', true),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/authors')->with('success', 'Author updated.');
    }

    public function delete($id)
    {
        $this->db->table('author_manga')->where('author_id', $id)->delete();
        $this->model->delete($id);
        return redirect()->to('/admin/authors')->with('success', 'Author deleted.');
    }
}
