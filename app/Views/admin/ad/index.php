<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Ads <small class="text-muted">(<?= count($ads) ?>)</small></h4>
    <div>
        <a href="/admin/placements" class="btn btn-outline-info btn-sm">Manage Placements</a>
        <a href="/admin/ads/create" class="btn btn-primary btn-sm">+ New Ad</a>
    </div>
</div>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th width="60">ID</th><th>Name</th><th>Code</th><th width="150">Created</th><th width="150">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($ads as $ad): ?>
                <tr>
                    <td><?= $ad->id ?></td>
                    <td><strong><?= esc($ad->bloc_id) ?></strong></td>
                    <td><code class="text-truncate d-inline-block" style="max-width:400px;"><?= esc(mb_substr($ad->code ?? '', 0, 100)) ?><?= mb_strlen($ad->code ?? '') > 100 ? '...' : '' ?></code></td>
                    <td><?= $ad->created_at ? date('d/m/Y H:i', strtotime($ad->created_at)) : '-' ?></td>
                    <td>
                        <a href="/admin/ads/edit/<?= $ad->id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                        <form action="/admin/ads/delete/<?= $ad->id ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this ad and all its placements?')">
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
