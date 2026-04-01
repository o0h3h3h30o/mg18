<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Chapters - <?= esc($manga->name) ?></h4>
    <div class="d-flex gap-2">
        <a href="/admin/chapters/create/<?= $manga->id ?>" class="btn btn-success btn-sm"><i class="bi bi-plus-lg"></i> Create Chapter</a>
        <a href="/admin/manga/edit/<?= $manga->id ?>" class="btn btn-secondary btn-sm">Back to Manga</a>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>ID</th><th>Number</th><th>Name</th><th>Views</th><th>Created</th><th>Crawl</th><th>Show</th><th>Login</th><th width="150">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($chapters as $ch): ?>
                <tr>
                    <td><?= $ch->id ?></td>
                    <td><?= esc($ch->number) ?></td>
                    <td>
                        <?= esc($ch->name ?: '-') ?>
                        <?php if (!empty($ch->source_url)): ?>
                            <a href="<?= esc($ch->source_url) ?>" target="_blank" class="text-muted ms-1" title="Source"><i class="bi bi-box-arrow-up-right" style="font-size:10px"></i></a>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($ch->view) ?></td>
                    <td><small class="text-muted"><?= !empty($ch->created_at) ? date('d/m/Y H:i', strtotime($ch->created_at)) : '-' ?></small></td>
                    <td>
                        <?php if ((int)$ch->is_crawling === 0): ?>
                            <span class="badge bg-success">Done</span>
                        <?php elseif ((int)$ch->is_crawling === 1): ?>
                            <span class="badge bg-warning">Crawling</span>
                        <?php else: ?>
                            <span class="badge bg-info">Need crawl</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $ch->is_show ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                    <td><?= $ch->need_login ? '<span class="badge bg-warning">Yes</span>' : '-' ?></td>
                    <td>
                        <a href="/admin/pages/<?= $ch->id ?>" class="btn btn-outline-info btn-sm" title="Manage Images">Pages</a>
                        <a href="/admin/chapters/edit/<?= $ch->id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                        <form action="/admin/chapters/delete/<?= $ch->id ?>" method="post" class="d-inline" onsubmit="return confirm('Delete?')">
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
<?= $this->endSection() ?>
