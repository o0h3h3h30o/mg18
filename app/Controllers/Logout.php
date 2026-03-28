<?php

namespace App\Controllers;

use App\Models\UsersModel;

class Logout extends BaseController
{
    public function index()
    {
        $usersModel = new UsersModel();
        $usersModel->logout();

        return redirect()->to('/login');
    }
}
