<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Edit Page #<?= $item->id ?> — <?= esc($manga->name) ?> / Ch.<?= esc($chapter->number) ?></h4>
    <a href="/admin/pages/<?= $chapter->id ?>" class="btn btn-secondary btn-sm">Back to Pages</a>
</div>
<div class="card mb-4">
    <div class="card-body">
        <form action="/admin/pages/update/<?= $item->id ?>" method="post">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-2 mb-3">
                    <label class="form-label">Order</label>
                    <input type="number" name="slug" class="form-control" value="<?= esc($item->slug) ?>" required>
                </div>
                <div class="col-md-7 mb-3">
                    <label class="form-label">Image URL/Path</label>
                    <input type="text" name="image" class="form-control" value="<?= esc($item->image) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">External</label>
                    <select name="external" class="form-select">
                        <option value="0" <?= !$item->external ? 'selected' : '' ?>>No (Local)</option>
                        <option value="1" <?= $item->external ? 'selected' : '' ?>>Yes (External URL)</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<?php
    $imgBaseUrl = config('Manga')->cdnUrl . '/manga/' . $manga->slug . '/chapters/' . $chapter->slug . '/';
?>
<div class="card">
    <div class="card-header"><strong>All Pages in this Chapter</strong> (<?= count($allPages) ?> images)</div>
    <div class="card-body">
        <div class="row g-2">
            <?php foreach ($allPages as $p): ?>
                <?php $imgSrc = $p->external ? $p->image : $imgBaseUrl . $p->image; ?>
                <div class="col-6 col-md-3 col-lg-2 text-center">
                    <a href="/admin/pages/edit/<?= $p->id ?>" class="d-block border rounded p-1 <?= $p->id == $item->id ? 'border-primary border-3' : '' ?>">
                        <img src="<?= esc($imgSrc) ?>" class="img-fluid" style="max-height:150px;" loading="lazy">
                        <small class="d-block mt-1 text-muted">#<?= $p->slug ?></small>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
