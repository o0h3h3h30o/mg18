<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TagModel;

class TagController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new TagModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Tags',
            'tags'  => $this->model->orderBy('name', 'ASC')->findAll(),
        ];
        return view('admin/tag/index', $data);
    }

    public function create()
    {
        return view('admin/tag/form', ['title' => 'Add Tag', 'item' => null]);
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
        return redirect()->to('/admin/tags')->with('success', 'Tag created.');
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/tags');
        return view('admin/tag/form', ['title' => 'Edit Tag', 'item' => $item]);
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
        return redirect()->to('/admin/tags')->with('success', 'Tag updated.');
    }

    public function delete($id)
    {
        $this->db->table('manga_tag')->where('tag_id', $id)->delete();
        $this->model->delete($id);
        return redirect()->to('/admin/tags')->with('success', 'Tag deleted.');
    }
}
