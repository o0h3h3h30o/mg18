<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\UsersModel;

abstract class BaseController extends Controller
{
    protected $session;
    protected $db;
    protected array $categories = [];
    protected int $is_logged = 0;
    protected $user_info;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        $this->helpers = ['url', 'form', 'cookie', 'basic'];

        parent::initController($request, $response, $logger);

        $this->session = service('session');
        $this->db = db_connect();

        // Load categories
        $this->categories = $this->db->table('category')->get()->getResult();

        // Check login
        $this->checkAuth();
    }

    private function checkAuth(): void
    {
        $usersModel = new UsersModel();

        // 1. Check session
        if ($this->session->get('logged_in') && $this->session->get('user_id')) {
            $user = $usersModel->find((int) $this->session->get('user_id'));
            if ($user && (!isset($user->status) || $user->status == 1)) {
                $this->is_logged = 1;
                $this->user_info = $user;
                // Ensure JS cookie exists
                if (!isset($_COOKIE['is_logged'])) {
                    setcookie('is_logged', '1', time() + (365 * 24 * 3600), '/', '', false, false);
                }
                return;
            }
            // Invalid session - clear it
            $this->session->remove(['logged_in', 'user_id', 'username', 'role']);
        }

        // 2. Check remember me cookie
        $user = $usersModel->checkRememberMe();
        if ($user) {
            $this->is_logged = 1;
            $this->user_info = $user;
            if (!isset($_COOKIE['is_logged'])) {
                setcookie('is_logged', '1', time() + (365 * 24 * 3600), '/', '', false, false);
            }
            return;
        }

        // Not logged in — clear cookie if present
        $this->is_logged = 0;
        $this->user_info = null;
        if (isset($_COOKIE['is_logged'])) {
            setcookie('is_logged', '', time() - 3600, '/', '', false, false);
        }
    }

    /**
     * Get real client IP behind proxy/Cloudflare
     */
    protected function getRealIP(): string
    {
        // Priority: CF-Connecting-IP > X-Real-IP > X-Forwarded-For > REMOTE_ADDR
        $headers = ['CF-Connecting-IP', 'X-Real-IP', 'X-Forwarded-For'];
        foreach ($headers as $header) {
            $val = $this->request->getServer('HTTP_' . str_replace('-', '_', strtoupper($header)));
            if (!empty($val)) {
                // X-Forwarded-For can contain multiple IPs: client, proxy1, proxy2
                $ip = trim(explode(',', $val)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $this->request->getIPAddress();
    }

    protected function getOptionByKey(string $key): string
    {
        $row = $this->db->table('options')
            ->where('key', $key)
            ->get()
            ->getRow();
        return $row->value ?? '';
    }

    protected function getOptionsByKeys(array $keys): array
    {
        if (empty($keys)) return [];
        $rows = $this->db->table('options')
            ->whereIn('key', $keys)
            ->get()
            ->getResult();
        $result = [];
        foreach ($rows as $row) {
            $result[$row->key] = $row->value ?? '';
        }
        return $result;
    }

    protected function getAds(int $placementId): array
    {
        $ads = $this->db->query(
            'SELECT * FROM ad LEFT JOIN ad_placement ON ad_placement.ad_id = ad.id WHERE placement_id = ?',
            [$placementId]
        )->getResult();

        $adsList = [];
        foreach ($ads as $ad) {
            $adsList[$ad->placement] = $ad->code;
        }

        return $adsList;
    }

    protected function isAdmin(): bool
    {
        return $this->is_logged && $this->user_info && ($this->user_info->role ?? '') === 'admin';
    }

    protected function getCommonData(): array
    {
        return [
            'categories' => $this->categories,
            'is_logged'  => $this->is_logged,
            'user_info'  => $this->user_info,
            'cdnUrl'     => config('Manga')->cdnUrl,
            'is_admin'   => $this->isAdmin(),
        ];
    }

    protected function getTopDay(): array
    {
        return $this->db->query(
            'SELECT m.id as manga_id, m.name, m.slug, m.view_day as view FROM manga m ORDER BY view_day DESC LIMIT 10'
        )->getResult();
    }

    protected function getTopMonth(): array
    {
        return $this->db->query(
            'SELECT m.id as manga_id, m.name, m.slug, m.view_month as view, m.time_chap_1 FROM manga m ORDER BY m.view_month DESC LIMIT 10'
        )->getResult();
    }

    protected function getPaginationConfig(string $baseUrl, int $totalRows, int $perPage, int $segment): array
    {
        return [
            'baseURL'        => $baseUrl,
            'totalRows'      => $totalRows,
            'perPage'        => $perPage,
            'uriSegment'     => $segment,
            'usePageNumbers' => true,
        ];
    }
}
