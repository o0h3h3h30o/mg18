<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Pages - <?= esc($manga->name) ?> / Ch.<?= esc($chapter->number) ?> <small class="text-muted">(<?= count($pages) ?> images)</small></h4>
    <a href="/admin/chapters/<?= $chapter->manga_id ?>" class="btn btn-secondary btn-sm">Back to Chapters</a>
</div>
<?php
    $imgBaseUrl = config('Manga')->cdnUrl . '/manga/' . $manga->slug . '/chapters/' . $chapter->slug . '/';
?>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th width="60">ID</th><th width="60">Order</th><th>Image</th><th width="80">External</th><th>Preview</th><th width="150">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($pages as $p): ?>
                <?php $imgSrc = $p->external ? $p->image : $imgBaseUrl . $p->image; ?>
                <tr>
                    <td><?= $p->id ?></td>
                    <td><?= $p->slug ?></td>
                    <td><code><?= esc($p->image) ?></code></td>
                    <td><?= $p->external ? '<span class="badge bg-info">Yes</span>' : 'No' ?></td>
                    <td>
                        <img src="<?= esc($imgSrc) ?>" style="max-height:80px;max-width:120px;" loading="lazy">
                    </td>
                    <td>
                        <a href="/admin/pages/edit/<?= $p->id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                        <form action="/admin/pages/delete/<?= $p->id ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this page?')">
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
