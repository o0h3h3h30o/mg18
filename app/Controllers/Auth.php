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

    public function index()
    {
        // Already logged in
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        $data = $this->getCommonData();
        $data['recaptcha_publickey'] = env('RECAPTCHA_SITE_KEY', '');
        $data['message'] = session()->getFlashdata('message') ?? '';
        $data['message_type'] = session()->getFlashdata('message_type') ?? '';

        return view('login', $data);
    }

    public function register()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        $data = $this->getCommonData();
        $data['recaptcha_publickey'] = env('RECAPTCHA_SITE_KEY', '');
        $data['message'] = session()->getFlashdata('message') ?? '';
        $data['message_type'] = session()->getFlashdata('message_type') ?? '';

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
            session()->setFlashdata('message', 'Invalid username or password');
            session()->setFlashdata('message_type', 'danger');
            return redirect()->to('/login');
        }

        // Check if user is banned
        if (isset($user->status) && $user->status == 0) {
            session()->setFlashdata('message', 'Your account has been suspended.');
            session()->setFlashdata('message_type', 'danger');
            return redirect()->to('/login');
        }

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
            session()->setFlashdata('message', implode('<br>', $this->validator->getErrors()));
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
