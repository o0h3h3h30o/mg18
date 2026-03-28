<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3"><?= esc($title) ?></h4>
<div class="card">
    <div class="card-body">
        <form action="<?= $item ? '/admin/categories/update/' . $item->id : '/admin/categories/store' ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= esc($item->name ?? old('name')) ?>" required>
            </div>
            <div class="mb-3 form-check">
                <input type="hidden" name="show_home" value="0">
                <input type="checkbox" name="show_home" value="1" class="form-check-input" <?= ($item->show_home ?? 0) ? 'checked' : '' ?>>
                <label class="form-check-label">Show on homepage</label>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="/admin/categories" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
