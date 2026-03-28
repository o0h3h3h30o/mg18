<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UsersModel;

class UserController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new UsersModel();
    }

    public function index()
    {
        $search = $this->request->getGet('q') ?? '';
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $builder = $this->db->table('users');
        if ($search) {
            $builder->groupStart()
                ->like('username', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $users = $builder->orderBy('id', 'ASC')->limit($perPage, $offset)->get()->getResult();

        $data = [
            'title'   => 'Users',
            'users'   => $users,
            'search'  => $search,
            'page'    => $page,
            'perPage' => $perPage,
            'total'   => $total,
        ];
        return view('admin/user/index', $data);
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (!$item) return redirect()->to('/admin/users');
        return view('admin/user/form', ['title' => 'Edit User', 'item' => $item]);
    }

    public function update($id)
    {
        $rules = [
            'role'   => 'required|in_list[admin,user]',
            'is_vip' => 'permit_empty|in_list[0,1]',
            'status' => 'permit_empty|integer',
        ];
        $newPassword = $this->request->getPost('new_password');
        if ($newPassword) {
            $rules['new_password'] = 'min_length[6]|max_length[255]';
        }
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $updateData = [
            'role'   => $this->request->getPost('role'),
            'is_vip' => (int) $this->request->getPost('is_vip'),
            'status' => (int) $this->request->getPost('status'),
        ];

        if ($newPassword) {
            $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $this->model->update($id, $updateData);
        return redirect()->to('/admin/users')->with('success', 'User updated.');
    }

    public function delete($id)
    {
        // Don't allow deleting yourself
        $currentUserId = $this->session->get('user_id');
        if ($currentUserId && (int) $currentUserId === (int) $id) {
            return redirect()->to('/admin/users')->with('error', 'Cannot delete yourself.');
        }

        $this->db->table('comments')->where('user_id', $id)->delete();
        $this->db->table('bookmarks')->where('user_id', $id)->delete();
        $this->model->delete($id);
        return redirect()->to('/admin/users')->with('success', 'User deleted.');
    }
}
