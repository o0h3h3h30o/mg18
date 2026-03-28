<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3"><?= esc($title) ?></h4>
<div class="card">
    <div class="card-body">
        <form action="<?= $item ? '/admin/tags/update/' . $item->id : '/admin/tags/store' ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= esc($item->name ?? old('name')) ?>" required>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="/admin/tags" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
