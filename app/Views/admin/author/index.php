<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Authors / Artists <small class="text-muted">(<?= number_format($total) ?>)</small></h4>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="get">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search..." value="<?= esc($search) ?>">
            <button class="btn btn-outline-primary btn-sm text-nowrap">Search</button>
        </form>
        <a href="/admin/authors/create" class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-plus"></i> Add</a>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th width="120">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($authors as $item): ?>
                <tr>
                    <td><?= $item->id ?></td>
                    <td><?= esc($item->name) ?></td>
                    <td><code><?= esc($item->slug) ?></code></td>
                    <td>
                        <a href="/admin/authors/edit/<?= $item->id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                        <form action="/admin/authors/delete/<?= $item->id ?>" method="post" class="d-inline" onsubmit="return confirm('Delete?')">
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
<nav class="mt-3">
    <ul class="pagination pagination-sm">
        <?php for ($i = 1; $i <= min($totalPages, 20); $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?= $this->endSection() ?>
