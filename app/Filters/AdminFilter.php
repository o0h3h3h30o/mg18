<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');
        $usersModel = new UsersModel();
        $user = null;

        // Check session
        if ($session->get('logged_in') && $session->get('user_id')) {
            $user = $usersModel->find((int) $session->get('user_id'));
        }

        // Fallback: check remember me cookie
        if (!$user) {
            $user = $usersModel->checkRememberMe();
        }

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        // Check active
        if (isset($user->status) && $user->status == 0) {
            return redirect()->to('/login')->with('error', 'Account suspended.');
        }

        // Check admin role
        if ($user->role !== 'admin') {
            return redirect()->to('/')->with('error', 'Access denied.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
