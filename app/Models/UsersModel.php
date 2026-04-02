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
        setcookie('is_logged', '1', [
            'expires'  => time() + (365 * 24 * 3600),
            'path'     => '/',
            'secure'   => false,
            'httponly'  => false,
            'samesite' => 'Lax',
        ]);

        // Remember me cookie (30 days)
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->update($user->id, ['token' => password_hash($token, PASSWORD_BCRYPT)]);
            setcookie('remember_user', $user->id . ':' . $token, [
                'expires'  => time() + (30 * 24 * 3600),
                'path'     => '/',
                'secure'   => false,
                'httponly'  => true,
                'samesite' => 'Lax',
            ]);
            log_message('info', 'Remember cookie set for user #' . $user->id);
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
            // Restore session
            $this->setLoginSession($user, false);
            // Renew remember cookie with new token (extend 30 days)
            $this->renewRememberToken($user);
            return $user;
        }

        // Invalid token - clear cookie
        $this->clearRememberCookie();
        return null;
    }

    private function renewRememberToken(object $user): void
    {
        $token = bin2hex(random_bytes(32));
        $this->update($user->id, ['token' => password_hash($token, PASSWORD_BCRYPT)]);
        setcookie('remember_user', $user->id . ':' . $token, [
            'expires'  => time() + (30 * 24 * 3600),
            'path'     => '/',
            'secure'   => false, // Behind reverse proxy, HTTPS not reliably detected
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);
        log_message('info', 'Remember cookie renewed for user #' . $user->id);
    }

    private function clearRememberCookie(): void
    {
        setcookie('remember_user', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => false,
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);
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
        $this->clearRememberCookie();
        setcookie('is_logged', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => false,
            'httponly'  => false,
            'samesite' => 'Lax',
        ]);
    }

    public function getUserById(int $uid): ?object
    {
        return $this->find($uid);
    }
}
