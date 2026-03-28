<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3"><?= esc($title) ?></h4>
<div class="card">
    <div class="card-body">
        <form action="<?= $item ? '/admin/comictypes/update/' . $item->id : '/admin/comictypes/store' ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Label</label>
                <input type="text" name="label" class="form-control" value="<?= esc($item->label ?? old('label')) ?>" required>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="/admin/comictypes" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
