<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StatusModel;

class StatusController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new StatusModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Statuses',
            'items' => $this->model->findAll(),
        ];
        return view('admin/status/index', $data);
    }

    public function create()
    {
        return view('admin/status/form', ['title' => 'Add Status', 'item' => null]);
    }

    public function store()
    {
        if (!$this->validate(['label' => 'required|min_length[1]|max_length[255]'])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $this->model->insert([
            'label'      => trim($this->request->getPost('label')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/statuses')->with('success', 'Status created.');
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/statuses');
        return view('admin/status/form', ['title' => 'Edit Status', 'item' => $item]);
    }

    public function update($id)
    {
        if (!$this->validate(['label' => 'required|min_length[1]|max_length[255]'])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }
        $this->model->update($id, [
            'label'      => trim($this->request->getPost('label')),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/statuses')->with('success', 'Status updated.');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        return redirect()->to('/admin/statuses')->with('success', 'Status deleted.');
    }
}
