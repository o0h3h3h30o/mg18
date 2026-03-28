<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Ad Placements</h4>
    <div>
        <a href="/admin/ads" class="btn btn-outline-secondary btn-sm">Back to Ads</a>
        <a href="/admin/placements/create" class="btn btn-primary btn-sm">+ Add Placement</a>
    </div>
</div>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<?php foreach ($pages as $pageId => $pageName): ?>
<?php $pagePlacements = array_filter($placements, fn($p) => $p->placement_id == $pageId); ?>
<div class="card mb-4">
    <div class="card-header">
        <strong><?= $pageName ?></strong> (placement_id = <?= $pageId ?>)
        <span class="badge bg-info"><?= count($pagePlacements) ?> slots</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($pagePlacements)): ?>
            <p class="text-muted p-3 mb-0">No placements for this page.</p>
        <?php else: ?>
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th width="60">ID</th><th>Position</th><th>Ad</th><th width="150">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($pagePlacements as $p): ?>
                <tr>
                    <td><?= $p->id ?></td>
                    <td><span class="badge bg-secondary"><?= esc($p->placement) ?></span></td>
                    <td><?= esc($p->ad_name ?? 'N/A') ?> <small class="text-muted">(ad_id: <?= $p->ad_id ?>)</small></td>
                    <td>
                        <a href="/admin/placements/edit/<?= $p->id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                        <form action="/admin/placements/delete/<?= $p->id ?>" method="post" class="d-inline" onsubmit="return confirm('Remove this placement?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger btn-sm">Del</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
<?= $this->endSection() ?>
