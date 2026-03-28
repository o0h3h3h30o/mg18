<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin') ?> - Manga18 Admin</title>
    <link href="/vendor/css/bootstrap-5.3.3.min.css" rel="stylesheet">
    <link href="/vendor/css/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --kt-sidebar-width: 250px;
        --kt-sidebar-bg: #111318;
        --kt-sidebar-menu-bg: #0e1014;
        --kt-sidebar-link: #7c7e8a;
        --kt-sidebar-link-hover: #e0e0e5;
        --kt-sidebar-active-bg: rgba(99,102,241,.12);
        --kt-sidebar-active-color: #a5b4fc;
        --kt-body-bg: #151521;
        --kt-card-bg: #1e1e2d;
        --kt-border: #2d2d3f;
        --kt-text-dark: #cdcde4;
        --kt-text-muted: #6c6e82;
        --kt-primary: #6366f1;
        --kt-success: #22c55e;
        --kt-danger: #ef4444;
        --kt-warning: #f59e0b;
        --kt-info: #3b82f6;
    }
    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
    body { background: var(--kt-body-bg); font-size: 13px; color: var(--kt-text-dark); margin: 0; }

    /* Sidebar */
    .kt-sidebar {
        position: fixed; top: 0; left: 0; bottom: 0;
        width: var(--kt-sidebar-width); background: var(--kt-sidebar-bg);
        z-index: 100; overflow-y: auto; overflow-x: hidden;
        scrollbar-width: thin; scrollbar-color: #2d2d3f transparent;
    }
    .kt-sidebar::-webkit-scrollbar { width: 4px; }
    .kt-sidebar::-webkit-scrollbar-thumb { background: #2d2d3f; border-radius: 4px; }

    .kt-sidebar-brand {
        padding: 20px 24px; display: flex; align-items: center; gap: 10px;
        border-bottom: 1px solid rgba(255,255,255,.05);
    }
    .kt-sidebar-brand a {
        color: #fff; text-decoration: none; font-size: 18px; font-weight: 700; letter-spacing: -.3px;
    }
    .kt-sidebar-brand .brand-dot {
        width: 8px; height: 8px; border-radius: 50%; background: var(--kt-primary); display: inline-block;
    }

    .kt-menu { padding: 12px 0; }
    .kt-menu-section {
        padding: 16px 24px 6px; font-size: 11px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .8px; color: #4a4b65;
    }
    .kt-menu-item a {
        display: flex; align-items: center; gap: 12px;
        padding: 9px 24px; color: var(--kt-sidebar-link);
        text-decoration: none; font-size: 13px; font-weight: 500;
        transition: all .15s; border-radius: 0; position: relative;
    }
    .kt-menu-item a:hover {
        color: var(--kt-sidebar-link-hover); background: rgba(255,255,255,.03);
    }
    .kt-menu-item a.active {
        color: var(--kt-sidebar-active-color); background: var(--kt-sidebar-active-bg);
    }
    .kt-menu-item a.active::before {
        content: ''; position: absolute; left: 0; top: 6px; bottom: 6px;
        width: 3px; background: var(--kt-primary); border-radius: 0 3px 3px 0;
    }
    .kt-menu-item a i { font-size: 16px; width: 20px; text-align: center; opacity: .7; }
    .kt-menu-item a.active i { opacity: 1; }
    .kt-menu-item .badge { font-size: 10px; padding: 3px 6px; }

    /* Content */
    .kt-content {
        margin-left: var(--kt-sidebar-width); min-height: 100vh;
        padding: 0;
    }

    /* Top bar */
    .kt-topbar {
        background: var(--kt-card-bg); border-bottom: 1px solid var(--kt-border);
        padding: 12px 30px; display: flex; align-items: center; justify-content: space-between;
        position: sticky; top: 0; z-index: 50;
    }
    .kt-topbar-title { font-size: 15px; font-weight: 600; color: var(--kt-text-dark); }
    .kt-topbar-actions { display: flex; align-items: center; gap: 12px; }
    .kt-topbar-actions a {
        color: var(--kt-text-muted); text-decoration: none; font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 5px; transition: color .15s;
    }
    .kt-topbar-actions a:hover { color: var(--kt-primary); }

    .kt-page { padding: 24px 30px; }

    /* Cards */
    .card {
        border: 1px solid var(--kt-border); border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,.04); background: var(--kt-card-bg);
    }
    .card-header {
        background: transparent; border-bottom: 1px solid var(--kt-border);
        padding: 16px 20px; font-weight: 600; font-size: 14px;
    }
    .card-body { padding: 20px; }

    /* Tables */
    .table { font-size: 13px; color: var(--kt-text-dark); }
    .table thead th {
        background: #181825; font-weight: 600; font-size: 12px;
        text-transform: uppercase; letter-spacing: .5px; color: var(--kt-text-muted);
        border-bottom: 1px solid var(--kt-border); padding: 12px 16px;
    }
    .table tbody td {
        padding: 12px 16px; vertical-align: middle;
        border-bottom: 1px solid #252536; color: var(--kt-text-dark);
    }
    .table-hover tbody tr:hover { background: #1a1a2e; }
    .table tbody tr:last-child td { border-bottom: none; }

    /* Buttons */
    .btn { font-size: 12px; font-weight: 500; border-radius: 6px; padding: 6px 14px; }
    .btn-sm { padding: 5px 10px; font-size: 11px; }
    .btn-primary { background: var(--kt-primary); border-color: var(--kt-primary); }
    .btn-primary:hover { background: #4f46e5; border-color: #4f46e5; }
    .btn-success { background: var(--kt-success); border-color: var(--kt-success); }
    .btn-danger { background: var(--kt-danger); border-color: var(--kt-danger); }
    .btn-outline-primary { color: #a5b4fc; border-color: #3730a3; }
    .btn-outline-primary:hover { background: rgba(99,102,241,.15); color: #a5b4fc; border-color: var(--kt-primary); }
    .btn-outline-info { color: #93c5fd; border-color: #1e3a5f; }
    .btn-outline-info:hover { background: rgba(59,130,246,.12); color: #93c5fd; border-color: var(--kt-info); }
    .btn-outline-danger { color: #fca5a5; border-color: #7f1d1d; }
    .btn-outline-danger:hover { background: rgba(239,68,68,.12); color: #fca5a5; border-color: var(--kt-danger); }
    .btn-outline-secondary { color: var(--kt-text-muted); border-color: var(--kt-border); }
    .btn-outline-secondary:hover { background: rgba(255,255,255,.05); color: var(--kt-text-dark); }

    /* Badges */
    .badge { font-weight: 500; font-size: 11px; padding: 4px 8px; border-radius: 4px; }
    .badge.bg-success { background: rgba(34,197,94,.15) !important; color: #4ade80 !important; }
    .badge.bg-danger { background: rgba(239,68,68,.15) !important; color: #fca5a5 !important; }
    .badge.bg-secondary { background: rgba(108,110,130,.2) !important; color: #9ca0b8 !important; }
    .badge.bg-warning { background: rgba(245,158,11,.15) !important; color: #fbbf24 !important; }
    .badge.bg-info { background: rgba(59,130,246,.15) !important; color: #93c5fd !important; }

    /* Forms */
    .form-control, .form-select {
        border: 1px solid var(--kt-border); border-radius: 6px; font-size: 13px;
        padding: 8px 12px; transition: border-color .15s, box-shadow .15s;
        background: #1a1a2e; color: var(--kt-text-dark);
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--kt-primary); box-shadow: 0 0 0 3px rgba(99,102,241,.12);
        background: #1a1a2e; color: var(--kt-text-dark);
    }
    .form-control::placeholder { color: var(--kt-text-muted); }
    .form-label { font-size: 13px; font-weight: 500; color: var(--kt-text-dark); margin-bottom: 6px; }
    .form-check-input:checked { background-color: var(--kt-primary); border-color: var(--kt-primary); }

    /* Alerts */
    .alert { border-radius: 8px; font-size: 13px; border: none; }
    .alert-success { background: rgba(34,197,94,.12); color: #4ade80; }
    .alert-danger { background: rgba(239,68,68,.12); color: #fca5a5; }
    .alert .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }

    /* Pagination */
    .pagination { gap: 4px; }
    .page-link {
        border-radius: 6px !important; border: none; font-size: 12px; font-weight: 500;
        color: var(--kt-text-muted); padding: 6px 12px; background: transparent;
    }
    .page-link:hover { background: rgba(99,102,241,.12); color: var(--kt-primary); }
    .page-item.active .page-link {
        background: var(--kt-primary); color: #fff; box-shadow: 0 2px 4px rgba(99,102,241,.3);
    }

    /* Stat cards */
    .stat-card {
        border: none; border-radius: 8px; padding: 20px;
        background: var(--kt-card-bg); box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .stat-card .stat-icon {
        width: 44px; height: 44px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .stat-card.blue .stat-icon { background: rgba(99,102,241,.15); color: #a5b4fc; }
    .stat-card.green .stat-icon { background: rgba(34,197,94,.15); color: #4ade80; }
    .stat-card.orange .stat-icon { background: rgba(245,158,11,.15); color: #fbbf24; }
    .stat-card.red .stat-icon { background: rgba(239,68,68,.15); color: #fca5a5; }
    .stat-card .stat-value { font-size: 22px; font-weight: 700; color: var(--kt-text-dark); }
    .stat-card .stat-label { font-size: 12px; font-weight: 500; color: var(--kt-text-muted); }

    /* Scrollbar global */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #2d2d3f; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #3d3d52; }

    /* List group */
    .list-group-item {
        border-color: var(--kt-border); font-size: 13px; padding: 10px 16px;
        background: var(--kt-card-bg); color: var(--kt-text-dark);
    }

    /* Dark mode helpers */
    .text-muted { color: var(--kt-text-muted) !important; }
    a { color: #a5b4fc; }
    a:hover { color: #c7d2fe; }
    .modal-content { background: var(--kt-card-bg); border-color: var(--kt-border); color: var(--kt-text-dark); }
    .modal-header { border-color: var(--kt-border); }
    .modal-footer { border-color: var(--kt-border); }
    .dropdown-menu { background: var(--kt-card-bg); border-color: var(--kt-border); }
    .dropdown-item { color: var(--kt-text-dark); }
    .dropdown-item:hover { background: rgba(255,255,255,.05); color: #fff; }

    /* Responsive tablet */
    @media (max-width: 991px) {
        .kt-sidebar { width: 220px; }
        .kt-content { margin-left: 220px; }
        .kt-page { padding: 16px; }
    }

    /* Responsive mobile */
    @media (max-width: 767px) {
        .kt-sidebar {
            transform: translateX(-100%);
            transition: transform .25s ease;
            width: 260px;
            box-shadow: 4px 0 20px rgba(0,0,0,.3);
        }
        .kt-sidebar.show { transform: translateX(0); }
        .kt-sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.4); z-index: 99;
        }
        .kt-sidebar-overlay.show { display: block; }
        .kt-content { margin-left: 0; }
        .kt-topbar { padding: 10px 16px; }
        .kt-topbar-title { font-size: 14px; }
        .kt-page { padding: 14px; }
        .kt-menu-toggle { display: flex !important; }

        /* Table scroll */
        .table-responsive-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .card-body { padding: 12px; }
        .card-header { padding: 12px 14px; }

        /* Buttons stack */
        .d-flex.gap-2 { flex-wrap: wrap; }
    }

    /* Toggle button - hidden on desktop, shown on mobile via media query */
    .kt-menu-toggle {
        display: none; background: none; border: none;
        color: var(--kt-text-dark); font-size: 20px; padding: 4px 8px;
        cursor: pointer; border-radius: 6px;
    }
    .kt-menu-toggle:hover { background: rgba(255,255,255,.08); }
    </style>
</head>
<body>

<!-- Sidebar overlay (mobile) -->
<div class="kt-sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<div class="kt-sidebar" id="ktSidebar">
    <div class="kt-sidebar-brand">
        <a href="/admin"><span class="brand-dot"></span> Manga18</a>
    </div>
    <div class="kt-menu">
        <div class="kt-menu-section">Main</div>
        <div class="kt-menu-item">
            <a href="/admin" class="<?= uri_string() === 'admin' ? 'active' : '' ?>"><i class="bi bi-grid"></i> Dashboard</a>
        </div>

        <div class="kt-menu-section">Content</div>
        <div class="kt-menu-item">
            <a href="/admin/manga" class="<?= str_starts_with(uri_string(), 'admin/manga') ? 'active' : '' ?>"><i class="bi bi-book"></i> Manga</a>
        </div>
        <div class="kt-menu-item">
            <a href="/admin/categories" class="<?= str_starts_with(uri_string(), 'admin/categories') ? 'active' : '' ?>"><i class="bi bi-folder2"></i> Categories</a>
        </div>
        <div class="kt-menu-item">
            <a href="/admin/tags" class="<?= str_starts_with(uri_string(), 'admin/tags') ? 'active' : '' ?>"><i class="bi bi-tags"></i> Tags</a>
        </div>
        <div class="kt-menu-item">
            <a href="/admin/authors" class="<?= str_starts_with(uri_string(), 'admin/authors') ? 'active' : '' ?>"><i class="bi bi-people"></i> Authors</a>
        </div>

        <div class="kt-menu-section">Monetize</div>
        <div class="kt-menu-item">
            <a href="/admin/ads" class="<?= str_starts_with(uri_string(), 'admin/ads') ? 'active' : '' ?>"><i class="bi bi-megaphone"></i> Ads</a>
        </div>
        <div class="kt-menu-item">
            <a href="/admin/placements" class="<?= str_starts_with(uri_string(), 'admin/placements') ? 'active' : '' ?>"><i class="bi bi-layout-split"></i> Placements</a>
        </div>

        <div class="kt-menu-section">System</div>
        <div class="kt-menu-item">
            <a href="/admin/settings" class="<?= str_starts_with(uri_string(), 'admin/settings') ? 'active' : '' ?>"><i class="bi bi-gear"></i> Settings</a>
        </div>
        <div class="kt-menu-item">
            <a href="/admin/comictypes" class="<?= str_starts_with(uri_string(), 'admin/comictypes') ? 'active' : '' ?>"><i class="bi bi-collection"></i> Comic Types</a>
        </div>
        <div class="kt-menu-item">
            <a href="/admin/statuses" class="<?= str_starts_with(uri_string(), 'admin/statuses') ? 'active' : '' ?>"><i class="bi bi-flag"></i> Statuses</a>
        </div>

        <div class="kt-menu-section">Users</div>
        <div class="kt-menu-item">
            <a href="/admin/users" class="<?= str_starts_with(uri_string(), 'admin/users') ? 'active' : '' ?>"><i class="bi bi-person"></i> Users</a>
        </div>
        <div class="kt-menu-item">
            <a href="/admin/comments" class="<?= str_starts_with(uri_string(), 'admin/comments') ? 'active' : '' ?>"><i class="bi bi-chat-dots"></i> Comments</a>
        </div>
        <div class="kt-menu-item">
            <a href="/admin/reports" class="<?= str_starts_with(uri_string(), 'admin/reports') ? 'active' : '' ?>">
                <i class="bi bi-exclamation-triangle"></i> Reports
                <?php
                    $pendingCount = db_connect()->table('chapter_report')->where('status', 'pending')->countAllResults();
                    if ($pendingCount > 0) echo '<span class="badge bg-danger ms-auto">' . $pendingCount . '</span>';
                ?>
            </a>
        </div>

        <div class="kt-menu-section" style="margin-top:16px"></div>
        <div class="kt-menu-item">
            <a href="/" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View Site</a>
        </div>
        <div class="kt-menu-item">
            <a href="/logout"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </div>
    </div>
</div>

<!-- Content -->
<div class="kt-content">
    <div class="kt-topbar">
        <div style="display:flex;align-items:center;gap:10px;">
            <button class="kt-menu-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <div class="kt-topbar-title"><?= esc($title ?? 'Dashboard') ?></div>
        </div>
        <div class="kt-topbar-actions">
            <a href="/" target="_blank"><i class="bi bi-globe2"></i> Site</a>
            <a href="/logout"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </div>
    </div>
    <div class="kt-page">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle me-1"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $e): ?>
                    <li><?= esc($e) ?></li>
                <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </div>
</div>

<script src="/vendor/js/bootstrap-5.3.3.bundle.min.js"></script>
<script>
function toggleSidebar(){
    document.getElementById('ktSidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
</script>
</body>
</html>
