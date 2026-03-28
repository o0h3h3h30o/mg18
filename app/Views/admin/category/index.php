<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Categories</h4>
    <a href="/admin/categories/create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Add</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Show Home</th><th width="120">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $item): ?>
                <tr>
                    <td><?= $item->id ?></td>
                    <td><?= esc($item->name) ?></td>
                    <td><code><?= esc($item->slug) ?></code></td>
                    <td><?= $item->show_home ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                    <td>
                        <a href="/admin/categories/edit/<?= $item->id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                        <form action="/admin/categories/delete/<?= $item->id ?>" method="post" class="d-inline" onsubmit="return confirm('Delete?')">
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
