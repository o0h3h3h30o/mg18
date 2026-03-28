<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $item ? 'Edit' : 'Add' ?> Placement</h4>
    <a href="/admin/placements" class="btn btn-secondary btn-sm">Back</a>
</div>
<div class="card">
    <div class="card-body">
        <form action="<?= $item ? '/admin/placements/update/' . $item->id : '/admin/placements/store' ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Page</label>
                <select name="placement_id" class="form-select" required>
                    <option value="">-- Select Page --</option>
                    <?php foreach ($pages as $id => $name): ?>
                        <option value="<?= $id ?>" <?= (isset($item->placement_id) && $item->placement_id == $id) ? 'selected' : '' ?>><?= $name ?> (ID: <?= $id ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Position</label>
                <select name="placement" class="form-select" required>
                    <option value="">-- Select Position --</option>
                    <?php foreach ($positions as $pos): ?>
                        <option value="<?= $pos ?>" <?= (isset($item->placement) && $item->placement == $pos) ? 'selected' : '' ?>><?= $pos ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Ad</label>
                <select name="ad_id" class="form-select" required>
                    <option value="">-- Select Ad --</option>
                    <?php foreach ($ads as $ad): ?>
                        <option value="<?= $ad->id ?>" <?= (isset($item->ad_id) && $item->ad_id == $ad->id) ? 'selected' : '' ?>><?= esc($ad->bloc_id) ?> (ID: <?= $ad->id ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?= $item ? 'Update' : 'Add' ?></button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
