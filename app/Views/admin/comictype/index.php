<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Comic Types</h4>
    <a href="/admin/comictypes/create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Add</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>ID</th><th>Label</th><th width="120">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item->id ?></td>
                    <td><?= esc($item->label) ?></td>
                    <td>
                        <a href="/admin/comictypes/edit/<?= $item->id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                        <form action="/admin/comictypes/delete/<?= $item->id ?>" method="post" class="d-inline" onsubmit="return confirm('Delete?')">
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
