<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

/**
 * Shows CI4 Debug Toolbar for admin users even in production.
 * On production, admin needs ?debug=1 in URL to activate toolbar.
 */
class AdminDebugToolbar implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // In development: always show (default CI4 behavior)
        if (CI_DEBUG) {
            service('toolbar')->prepare($request, $response);
            return;
        }

        // In production: show only for admin + ?debug=1
        if (!$this->shouldShowToolbar($request)) {
            return;
        }

        // Force CI_DEBUG temporarily via runkit or just call prepare with a workaround
        // Since CI_DEBUG is a constant, we inject toolbar HTML directly
        $this->injectToolbar($request, $response);
    }

    private function shouldShowToolbar(RequestInterface $request): bool
    {
        // Require ?debug=1 param
        if ($request->getGet('debug') !== '1') {
            // Also check cookie for sticky debug mode
            if (($request->getCookie('admin_debug') ?? '') !== '1') {
                return false;
            }
        }

        return $this->isAdmin();
    }

    private function injectToolbar(RequestInterface $request, ResponseInterface $response): void
    {
        try {
            $contentType = $response->getHeaderLine('content-type');
            if (strpos($contentType, 'html') === false && $contentType !== '') {
                return;
            }

            $body = $response->getBody();
            if (empty($body) || strpos($body, '</body>') === false) {
                return;
            }

            // Collect basic debug info
            $db = \Config\Database::connect();
            $queries = $db->getConnectDuration();

            $debugHtml = $this->buildDebugPanel($request);

            // Set debug cookie so subsequent pages also show debug (sticky)
            $response->setCookie('admin_debug', '1', 3600);

            $body = str_replace('</body>', $debugHtml . '</body>', $body);
            $response->setBody($body);
        } catch (\Throwable $e) {
            log_message('error', 'AdminDebugToolbar: ' . $e->getMessage());
        }
    }

    private function buildDebugPanel(RequestInterface $request): string
    {
        $db = \Config\Database::connect();
        $elapsed = defined('CODEIGNITER_START') ? round(microtime(true) - CODEIGNITER_START, 4) : '?';
        $memory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        $ip = $request->getServer('REMOTE_ADDR');
        $realIp = $request->getServer('HTTP_X_REAL_IP') ?? $request->getServer('HTTP_X_FORWARDED_FOR') ?? $ip;
        $cfIp = $request->getServer('HTTP_CF_CONNECTING_IP') ?? 'N/A';
        $sessionId = session_id() ?: 'N/A';
        $method = $request->getMethod();
        $uri = (string) $request->getUri();
        $phpVer = PHP_VERSION;
        $ciVer = \CodeIgniter\CodeIgniter::CI_VERSION;

        // Get DB queries from query collector if available
        $queryLog = '';
        try {
            $queries = $db->getQueries();
            $queryCount = count($queries);
            foreach ($queries as $q) {
                $duration = round($q->getDuration(5) * 1000, 2);
                $sql = htmlspecialchars((string) $q);
                $queryLog .= "<div style='border-bottom:1px solid #333;padding:4px 0;'>"
                    . "<span style='color:#ff0;'>{$duration}ms</span> "
                    . "<span style='color:#aaa;'>{$sql}</span></div>";
            }
        } catch (\Throwable $e) {
            $queryCount = '?';
            $queryLog = '<div style="color:#f66;">Cannot read queries: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }

        $stopUrl = htmlspecialchars(strtok($uri, '?'));

        return <<<HTML
<div id="admDbg" style="position:fixed;bottom:0;left:0;right:0;z-index:999999;font-family:monospace;font-size:12px;">
  <div id="admDbgBar" style="background:#1a1a2e;color:#0f0;padding:6px 14px;display:flex;gap:16px;align-items:center;border-top:2px solid #0f0;cursor:pointer;" onclick="document.getElementById('admDbgDetail').style.display=document.getElementById('admDbgDetail').style.display==='none'?'block':'none'">
    <span style="color:#ff0;font-weight:bold;">🐛 ADMIN DEBUG</span>
    <span>⏱ {$elapsed}s</span>
    <span>💾 {$memory}MB</span>
    <span>🔍 {$queryCount} queries</span>
    <span>📡 {$method}</span>
    <span>🌐 IP: {$realIp}</span>
    <a href="{$stopUrl}" style="color:#f66;margin-left:auto;text-decoration:none;" title="Turn off debug">✕ Close Debug</a>
  </div>
  <div id="admDbgDetail" style="display:none;background:#111;color:#ccc;max-height:50vh;overflow:auto;padding:12px 16px;border-top:1px solid #333;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 24px;margin-bottom:12px;">
      <div><b style="color:#0f0;">REMOTE_ADDR:</b> {$ip}</div>
      <div><b style="color:#0f0;">X-Real-IP:</b> {$realIp}</div>
      <div><b style="color:#0f0;">CF-Connecting-IP:</b> {$cfIp}</div>
      <div><b style="color:#0f0;">Session:</b> {$sessionId}</div>
      <div><b style="color:#0f0;">PHP:</b> {$phpVer}</div>
      <div><b style="color:#0f0;">CI:</b> {$ciVer}</div>
      <div><b style="color:#0f0;">Env:</b> ENVIRONMENT</div>
      <div><b style="color:#0f0;">Memory:</b> {$memory} MB</div>
    </div>
    <div style="margin-top:8px;">
      <b style="color:#ff0;">DB Queries ({$queryCount}):</b>
      <div style="margin-top:4px;font-size:11px;max-height:300px;overflow:auto;">{$queryLog}</div>
    </div>
  </div>
</div>
HTML;
    }

    private function isAdmin(): bool
    {
        $session = service('session');
        if ($session->get('logged_in') && $session->get('user_id')) {
            $user = (new UsersModel())->find((int) $session->get('user_id'));
            return $user && ($user->role ?? '') === 'admin';
        }
        // Don't call checkRememberMe() here - it's already handled by AdminFilter/BaseController
        return false;
    }
}
