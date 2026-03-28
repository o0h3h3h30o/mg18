<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $item ? 'Edit' : 'Create' ?> Ad</h4>
    <a href="/admin/ads" class="btn btn-secondary btn-sm">Back</a>
</div>
<div class="card">
    <div class="card-body">
        <form action="<?= $item ? '/admin/ads/update/' . $item->id : '/admin/ads/store' ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Name (bloc_id)</label>
                <input type="text" name="bloc_id" class="form-control" value="<?= esc($item->bloc_id ?? '') ?>" required placeholder="e.g. widget exo, banner top...">
            </div>
            <div class="mb-3">
                <label class="form-label">Ad Code (HTML/JS)</label>
                <textarea name="code" class="form-control font-monospace" rows="10" placeholder="Paste ad script here..."><?= esc($item->code ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= $item ? 'Update' : 'Create' ?></button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
