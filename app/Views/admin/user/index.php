<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Users <small class="text-muted">(<?= number_format($total) ?>)</small></h4>
    <form class="d-flex gap-2" method="get">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search username/email..." value="<?= esc($search) ?>">
        <button class="btn btn-outline-primary btn-sm text-nowrap">Search</button>
    </form>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>VIP</th><th>Status</th><th>Created</th><th width="120">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u->id ?></td>
                    <td><?= esc($u->username) ?></td>
                    <td><?= esc($u->email) ?></td>
                    <td><span class="badge bg-<?= $u->role === 'admin' ? 'danger' : 'info' ?>"><?= esc($u->role) ?></span></td>
                    <td><?= $u->is_vip ? '<span class="badge bg-warning">VIP</span>' : '-' ?></td>
                    <td><?= $u->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Banned</span>' ?></td>
                    <td><?= $u->created_at ? date('d/m/Y', strtotime($u->created_at)) : '-' ?></td>
                    <td>
                        <a href="/admin/users/edit/<?= $u->id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                        <form action="/admin/users/delete/<?= $u->id ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this user?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger btn-sm">Del</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $totalPages = ceil($total / $perPage); if ($totalPages > 1): ?>
<?php
    $range = 5;
    $start = max(1, $page - $range);
    $end = min($totalPages, $page + $range);
?>
<nav class="mt-3 d-flex align-items-center gap-3">
    <ul class="pagination pagination-sm mb-0">
        <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?q=<?= urlencode($search) ?>&page=1">&laquo;</a></li>
            <li class="page-item"><a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">&lsaquo;</a></li>
        <?php endif; ?>
        <?php if ($start > 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($end < $totalPages): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
        <?php if ($page < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">&rsaquo;</a></li>
            <li class="page-item"><a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $totalPages ?>">&raquo;</a></li>
        <?php endif; ?>
    </ul>
    <small class="text-muted">Page <?= $page ?> / <?= number_format($totalPages) ?></small>
</nav>
<?php endif; ?>
<?= $this->endSection() ?>
