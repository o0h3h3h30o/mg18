<?php

namespace App\Controllers;

use App\Models\UsersModel;

class Auth extends BaseController
{
    protected UsersModel $usersModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    /**
     * Auto login admin via secret token
     * GET /admin-login?token=xxx
     */
    public function autoLogin()
    {
        $token = $this->request->getGet('token');
        $secretToken = env('ADMIN_AUTO_LOGIN_TOKEN', '');

        if (!$secretToken || !$token || $token !== $secretToken) {
            return redirect()->to('/login');
        }

        // Find admin user
        $admin = $this->usersModel->where('role', 'admin')->first();
        if (!$admin) {
            return redirect()->to('/login');
        }

        $this->usersModel->setLoginSession($admin, true);

        return redirect()->to('/admin');
    }

    public function index()
    {
        // Already logged in
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        $data = $this->getCommonData();
        $data['recaptcha_publickey'] = env('RECAPTCHA_SITE_KEY', '');
        $data['message'] = session()->getFlashdata('message');
        $data['message_type'] = session()->getFlashdata('message_type');

        return view('login', $data);
    }

    public function register()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        $data = $this->getCommonData();
        $data['recaptcha_publickey'] = env('RECAPTCHA_SITE_KEY', '');
        $data['message'] = session()->getFlashdata('message');
        $data['message_type'] = session()->getFlashdata('message_type');

        return view('register', $data);
    }

    public function check()
    {
        if (!$this->validate([
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[6]',
        ])) {
            session()->setFlashdata('message', 'Username and password are required.');
            session()->setFlashdata('message_type', 'danger');
            return redirect()->to('/login');
        }

        // Rate limit: max 5 failed attempts per IP, lock 5 minutes
        $ip = $this->request->getIPAddress();
        $cacheKey = 'login_attempts_' . md5($ip);
        $attempts = cache($cacheKey) ?? ['count' => 0, 'first_at' => time()];

        if ($attempts['count'] >= 5) {
            $elapsed = time() - $attempts['first_at'];
            $remaining = max(0, 300 - $elapsed);
            if ($remaining > 0) {
                $mins = (int) ceil($remaining / 60);
                session()->setFlashdata('message', 'Too many failed attempts. Try again in ' . $mins . ' minute(s).');
                session()->setFlashdata('message_type', 'danger');
                return redirect()->to('/login');
            }
            // Window expired, reset
            $attempts = ['count' => 0, 'first_at' => time()];
        }

        // reCAPTCHA (skip on localhost)
        if (!$this->isLocalhost()) {
            if (!$this->validateRecaptcha($this->request->getPost('g-recaptcha-response'))) {
                session()->setFlashdata('message', 'Please complete the captcha.');
                session()->setFlashdata('message_type', 'danger');
                return redirect()->to('/login');
            }
        }

        $username = trim($this->request->getPost('username'));
        $password = $this->request->getPost('password');

        $user = $this->usersModel->authenticate($username, $password);

        if (!$user) {
            // Increment failed attempts
            $attempts['count']++;
            if ($attempts['count'] === 1) {
                $attempts['first_at'] = time();
            }
            cache()->save($cacheKey, $attempts, 300);

            $left = 5 - $attempts['count'];
            $msg = 'Invalid username or password.';
            if ($left > 0 && $left <= 2) {
                $msg .= ' ' . $left . ' attempt(s) remaining.';
            }
            session()->setFlashdata('message', $msg);
            session()->setFlashdata('message_type', 'danger');
            return redirect()->to('/login');
        }

        // Check if user is banned
        if (isset($user->status) && $user->status == 0) {
            session()->setFlashdata('message', 'Your account has been suspended.');
            session()->setFlashdata('message_type', 'danger');
            return redirect()->to('/login');
        }

        // Login success — clear failed attempts
        cache()->delete($cacheKey);

        $remember = (bool) $this->request->getPost('remember_me');
        $this->usersModel->setLoginSession($user, $remember);

        return redirect()->to('/');
    }

    public function subcribe()
    {
        if (!$this->validate([
            'username' => 'required|min_length[3]|max_length[50]|alpha_numeric_punct',
            'email'    => 'required|valid_email|max_length[255]',
            'password' => 'required|min_length[6]|max_length[255]',
        ])) {
            session()->setFlashdata('message', implode('<br>', array_map('esc', $this->validator->getErrors())));
            session()->setFlashdata('message_type', 'danger');
            return redirect()->to('/register');
        }

        // reCAPTCHA (skip on localhost)
        if (!$this->isLocalhost()) {
            if (!$this->validateRecaptcha($this->request->getPost('g-recaptcha-response'))) {
                session()->setFlashdata('message', 'Please complete the captcha.');
                session()->setFlashdata('message_type', 'danger');
                return redirect()->to('/register');
            }
        }

        $username = trim($this->request->getPost('username'));
        $password = $this->request->getPost('password');
        $password_confirmation = $this->request->getPost('password_confirmation');
        $email = trim($this->request->getPost('email'));

        if ($password !== $password_confirmation) {
            session()->setFlashdata('message', 'Passwords do not match.');
            session()->setFlashdata('message_type', 'danger');
            return redirect()->to('/register');
        }

        $db = db_connect();

        if ($db->table('users')->where('username', $username)->countAllResults() > 0) {
            session()->setFlashdata('message', 'Username already taken.');
            session()->setFlashdata('message_type', 'danger');
            return redirect()->to('/register');
        }

        if ($db->table('users')->where('email', $email)->countAllResults() > 0) {
            session()->setFlashdata('message', 'Email already registered.');
            session()->setFlashdata('message_type', 'danger');
            return redirect()->to('/register');
        }

        $db->table('users')->insert([
            'username'   => $username,
            'email'      => $email,
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'role'       => 'user',
            'status'     => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Auto login after register
        $user = $db->table('users')->where('username', $username)->get()->getRow();
        $this->usersModel->setLoginSession($user, false);

        return redirect()->to('/');
    }

    private function isLocalhost(): bool
    {
        return in_array($this->request->getIPAddress(), ['127.0.0.1', '::1']);
    }

    private function validateRecaptcha(?string $response): bool
    {
        if (empty($response)) return false;

        $secret = env('RECAPTCHA_SECRET_KEY', '');
        if (empty($secret)) return true; // No secret configured = skip

        try {
            $client = \Config\Services::curlrequest();
            $result = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret'   => $secret,
                    'response' => $response,
                    'remoteip' => $this->request->getIPAddress(),
                ],
            ]);
            $body = json_decode($result->getBody(), true);
            return $body['success'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
