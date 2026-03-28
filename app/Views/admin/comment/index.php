<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Comments <small class="text-muted">(<?= number_format($total) ?>)</small></h4>
    <div class="btn-group btn-group-sm">
        <a href="?type=" class="btn btn-<?= empty($type) ? 'info' : 'outline-info' ?>">All</a>
        <a href="?type=manga" class="btn btn-<?= $type === 'manga' ? 'primary' : 'outline-primary' ?>">Manga</a>
        <a href="?type=chapter" class="btn btn-<?= $type === 'chapter' ? 'success' : 'outline-success' ?>">Chapter</a>
    </div>
</div>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th width="50">ID</th><th>User</th><th>Type</th><th>Post</th><th>Comment</th><th>Reply To</th><th>Date</th><th width="80">Actions</th></tr></thead>
            <tbody>
            <?php if (empty($comments)): ?>
                <tr><td colspan="8" class="text-center text-muted p-3">No comments found.</td></tr>
            <?php endif; ?>
            <?php foreach ($comments as $c): ?>
                <tr>
                    <td><?= $c->id ?></td>
                    <td><strong><?= esc($c->username ?? 'N/A') ?></strong></td>
                    <td><span class="badge bg-<?= $c->post_type === 'manga' ? 'primary' : 'success' ?>"><?= $c->post_type ?></span></td>
                    <td><a href="<?= $c->post_link ?>" title="<?= esc($c->post_name) ?>"><?= esc(mb_substr($c->post_name, 0, 25)) ?></a></td>
                    <td title="<?= esc($c->comment) ?>"><?= esc(mb_substr($c->comment, 0, 60)) ?><?= mb_strlen($c->comment) > 60 ? '...' : '' ?></td>
                    <td><?= $c->parent_comment ? '#' . $c->parent_comment : '-' ?></td>
                    <td><small><?= $c->created_at ? date('d/m/Y H:i', strtotime($c->created_at)) : '-' ?></small></td>
                    <td>
                        <form action="/admin/comments/delete/<?= $c->id ?>" method="post" onsubmit="return confirm('Delete this comment and its replies?')">
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
<?php $totalPages = (int) ceil($total / $perPage); if ($totalPages > 1): ?>
<?php
    $range = 5;
    $start = max(1, $page - $range);
    $end = min($totalPages, $page + $range);
    $qs = $type ? '&type=' . $type : '';
?>
<nav class="mt-3 d-flex align-items-center gap-3">
    <ul class="pagination pagination-sm mb-0">
        <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=1<?= $qs ?>">&laquo;</a></li>
        <?php endif; ?>
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?><?= $qs ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?><?= $qs ?>">&raquo;</a></li>
        <?php endif; ?>
    </ul>
    <small class="text-muted">Page <?= $page ?> / <?= number_format($totalPages) ?></small>
</nav>
<?php endif; ?>
<?= $this->endSection() ?>
