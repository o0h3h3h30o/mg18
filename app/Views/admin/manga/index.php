<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Manga <small class="text-muted">(<?= number_format($total) ?>)</small></h4>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="get">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search..." value="<?= esc($search) ?>">
            <button class="btn btn-outline-primary btn-sm text-nowrap">Search</button>
        </form>
        <a href="/admin/manga/create" class="btn btn-success btn-sm text-nowrap"><i class="bi bi-plus-lg"></i> Create Manga</a>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th width="50">ID</th><th width="60">Cover</th><th>Name</th><th>Type</th><th>Status</th><th>Views</th><th>Public</th><th width="180">Actions</th></tr></thead>
            <tbody>
            <?php $cdnUrl = config('Manga')->cdnUrl; ?>
            <?php foreach ($manga as $m): ?>
                <tr>
                    <td><?= $m->id ?></td>
                    <td><img src="<?= $cdnUrl ?>/manga/<?= $m->slug ?>/cover/cover_thumb.jpg" alt="" style="width:45px;height:60px;object-fit:cover;border-radius:4px;"></td>
                    <td>
                        <a href="/admin/manga/edit/<?= $m->id ?>"><?= esc(mb_substr($m->name, 0, 50)) ?></a>
                        <?php if ($m->hot): ?><span class="badge bg-danger">HOT</span><?php endif; ?>
                    </td>
                    <td><?= esc($m->type_label ?? '-') ?></td>
                    <td><?= esc($m->status_label ?? '-') ?></td>
                    <td><?= number_format($m->views) ?></td>
                    <td><?= $m->is_public ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                    <td>
                        <a href="/admin/chapters/<?= $m->id ?>" class="btn btn-outline-info btn-sm">Chapters</a>
                        <a href="/admin/manga/edit/<?= $m->id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                        <form action="/admin/manga/delete/<?= $m->id ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this manga and all chapters?')">
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
        <?php if ($totalPages > 20): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
            <li class="page-item <?= $page == $totalPages ? 'active' : '' ?>">
                <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $totalPages ?>"><?= $totalPages ?></a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
<?= $this->endSection() ?>
