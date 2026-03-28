<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Edit User: <?= esc($item->username) ?></h4>
<div class="card">
    <div class="card-body">
        <form action="/admin/users/update/<?= $item->id ?>" method="post">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= esc($item->username) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="<?= esc($item->email) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <small class="text-muted">(leave blank to keep)</small></label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="user" <?= $item->role === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $item->role === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">VIP</label>
                        <select name="is_vip" class="form-select">
                            <option value="0" <?= !$item->is_vip ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= $item->is_vip ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1" <?= $item->status ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= !$item->status ? 'selected' : '' ?>>Banned</option>
                        </select>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="/admin/users" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
