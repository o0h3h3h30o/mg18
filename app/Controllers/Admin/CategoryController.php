<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;

class CategoryController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new CategoryModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Categories',
            'categories' => $this->model->orderBy('name', 'ASC')->findAll(),
        ];
        return view('admin/category/index', $data);
    }

    public function create()
    {
        return view('admin/category/form', ['title' => 'Add Category', 'item' => null]);
    }

    public function store()
    {
        $rules = ['name' => 'required|min_length[2]'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = $this->request->getPost('name');
        $this->model->insert([
            'name'      => $name,
            'slug'      => url_title($name, '-', true),
            'show_home' => (int) $this->request->getPost('show_home'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/categories')->with('success', 'Category created.');
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/categories');

        return view('admin/category/form', ['title' => 'Edit Category', 'item' => $item]);
    }

    public function update($id)
    {
        $rules = ['name' => 'required|min_length[2]'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = $this->request->getPost('name');
        $this->model->update($id, [
            'name'      => $name,
            'slug'      => url_title($name, '-', true),
            'show_home' => (int) $this->request->getPost('show_home'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/categories')->with('success', 'Category updated.');
    }

    public function delete($id)
    {
        $this->db->table('category_manga')->where('category_id', $id)->delete();
        $this->model->delete($id);
        return redirect()->to('/admin/categories')->with('success', 'Category deleted.');
    }
}
