<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = [
        'username', 'email', 'password', 'last_login',
        'created_at', 'updated_at', 'is_vip', 'name', 'role', 'status', 'token'
    ];

    /**
     * Verify username/email + password
     */
    public function authenticate(string $username, string $password): ?object
    {
        $user = $this->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user) {
            return null;
        }

        // Check with password_verify (bcrypt)
        if (password_verify($password, $user->password)) {
            return $user;
        }

        // Fallback: legacy phpass bcrypt
        if (class_exists('\App\Libraries\Bcrypt')) {
            $bcrypt = new \App\Libraries\Bcrypt();
            if ($bcrypt->check_password($password, $user->password)) {
                // Upgrade to modern hash
                $this->update($user->id, [
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                ]);
                return $user;
            }
        }

        return null;
    }

    /**
     * Set login session
     */
    public function setLoginSession(object $user, bool $remember = false): void
    {
        $session = service('session');

        $session->set([
            'user_id'  => (int) $user->id,
            'username' => $user->username,
            'role'     => $user->role ?? 'user',
            'logged_in' => true,
        ]);

        // Update last login
        $this->update($user->id, ['last_login' => date('Y-m-d H:i:s')]);

        // JS-readable cookie for CF-cached pages (not httpOnly)
        setcookie('is_logged', '1', time() + (365 * 24 * 3600), '/', '', false, false);

        // Remember me cookie
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            // Store token in DB
            $this->update($user->id, ['token' => password_hash($token, PASSWORD_BCRYPT)]);
            $expiry = time() + (7 * 24 * 60 * 60);
            setcookie('remember_user', $user->id . ':' . $token, $expiry, '/', '', false, true);
        }
    }

    /**
     * Check remember me cookie and restore session
     */
    public function checkRememberMe(): ?object
    {
        $cookie = $_COOKIE['remember_user'] ?? null;
        if (!$cookie) return null;

        $parts = explode(':', $cookie, 2);
        if (count($parts) !== 2) return null;

        [$userId, $token] = $parts;
        $user = $this->find((int) $userId);

        if (!$user || empty($user->token)) return null;

        if (password_verify($token, $user->token)) {
            $this->setLoginSession($user, false);
            return $user;
        }

        // Invalid token - clear cookie
        setcookie('remember_user', '', time() - 3600, '/', '', false, true);
        return null;
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        $session = service('session');
        $userId = $session->get('user_id');

        // Clear remember token in DB
        if ($userId) {
            $this->update($userId, ['token' => null]);
        }

        $session->destroy();
        setcookie('remember_user', '', time() - 3600, '/', '', false, true);
        setcookie('is_logged', '', time() - 3600, '/', '', false, false);
    }

    public function getUserById(int $uid): ?object
    {
        return $this->find($uid);
    }
}
