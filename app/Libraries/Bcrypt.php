<?php

namespace App\Libraries;

/**
 * Legacy Bcrypt library for backward compatibility with CI3 password hashes.
 */
class Bcrypt
{
    private string $_itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    private string $_random_state;
    private int $_iteration_count = 8;
    private bool $_portable_hashes = false;

    public function __construct(array $params = [])
    {
        foreach ($params as $key => $value) {
            $prop = '_' . $key;
            if (property_exists($this, $prop)) {
                $this->$prop = $value;
            }
        }

        if ($this->_iteration_count < 4 || $this->_iteration_count > 31) {
            $this->_iteration_count = 8;
        }

        $this->_random_state = microtime();
        if (function_exists('getmypid')) {
            $this->_random_state .= getmypid();
        }
    }

    protected function get_random_bytes(int $count): string
    {
        $output = '';
        if (is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb'))) {
            $output = fread($fh, $count);
            fclose($fh);
        }

        if (strlen($output) < $count) {
            $output = '';
            for ($i = 0; $i < $count; $i += 16) {
                $this->_random_state = md5(microtime() . $this->_random_state);
                $output .= pack('H*', md5($this->_random_state));
            }
            $output = substr($output, 0, $count);
        }

        return $output;
    }

    protected function encode64(string $input, int $count): string
    {
        $output = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $output .= $this->_itoa64[$value & 0x3f];
            if ($i < $count) $value |= ord($input[$i]) << 8;
            $output .= $this->_itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count) break;
            if ($i < $count) $value |= ord($input[$i]) << 16;
            $output .= $this->_itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count) break;
            $output .= $this->_itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }

    protected function gensalt_private(string $input): string
    {
        $output = '$P$';
        $output .= $this->_itoa64[min($this->_iteration_count + 5, 30)];
        $output .= $this->encode64($input, 6);
        return $output;
    }

    protected function crypt_private(string $password, string $setting): string
    {
        $output = '*0';
        if (substr($setting, 0, 2) == $output) $output = '*1';

        $id = substr($setting, 0, 3);
        if ($id != '$P$' && $id != '$H$') return $output;

        $count_log2 = strpos($this->_itoa64, $setting[3]);
        if ($count_log2 < 7 || $count_log2 > 30) return $output;

        $count = 1 << $count_log2;
        $salt = substr($setting, 4, 8);
        if (strlen($salt) != 8) return $output;

        $hash = md5($salt . $password, true);
        do {
            $hash = md5($hash . $password, true);
        } while (--$count);

        $output = substr($setting, 0, 12);
        $output .= $this->encode64($hash, 16);

        return $output;
    }

    protected function gensalt_blowfish(string $input): string
    {
        $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $output = '$2a$';
        $output .= chr(ord('0') + intdiv($this->_iteration_count, 10));
        $output .= chr(ord('0') + $this->_iteration_count % 10);
        $output .= '$';

        $i = 0;
        do {
            $c1 = ord($input[$i++]);
            $output .= $itoa64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            if ($i >= 16) {
                $output .= $itoa64[$c1];
                break;
            }
            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 4;
            $output .= $itoa64[$c1];
            $c1 = ($c2 & 0x0f) << 2;
            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 6;
            $output .= $itoa64[$c1];
            $output .= $itoa64[$c2 & 0x3f];
        } while (1);

        return $output;
    }

    public function hash_password(string $password): string
    {
        // Use PHP's built-in password_hash for new passwords
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function check_password(string $password, string $stored_hash): bool
    {
        // First try PHP's native password_verify
        if (password_verify($password, $stored_hash)) {
            return true;
        }

        // Fallback to legacy phpass method
        $hash = $this->crypt_private($password, $stored_hash);
        if ($hash[0] == '*') {
            $hash = crypt($password, $stored_hash);
        }

        return hash_equals($stored_hash, $hash);
    }
}
