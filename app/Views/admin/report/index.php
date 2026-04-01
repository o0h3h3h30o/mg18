<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Chapter Reports</h4>
    <div class="btn-group btn-group-sm">
        <a href="?status=pending" class="btn btn-<?= $status === 'pending' ? 'warning' : 'outline-warning' ?>">
            Pending <span class="badge bg-dark"><?= $counts['pending'] ?></span>
        </a>
        <a href="?status=resolved" class="btn btn-<?= $status === 'resolved' ? 'success' : 'outline-success' ?>">
            Resolved <span class="badge bg-dark"><?= $counts['resolved'] ?></span>
        </a>
        <a href="?status=dismissed" class="btn btn-<?= $status === 'dismissed' ? 'secondary' : 'outline-secondary' ?>">
            Dismissed <span class="badge bg-dark"><?= $counts['dismissed'] ?></span>
        </a>
        <a href="?status=all" class="btn btn-<?= $status === 'all' ? 'info' : 'outline-info' ?>">All</a>
    </div>
</div>

<form method="post" id="bulkForm">
    <?= csrf_field() ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <input type="checkbox" id="selectAll" onchange="document.querySelectorAll('.report-cb').forEach(c=>c.checked=this.checked)"> Select All
            </div>
            <?php if ($status === 'pending'): ?>
            <div class="btn-group btn-group-sm">
                <button type="submit" formaction="/admin/reports/bulk-resolve" class="btn btn-outline-success btn-sm" onclick="return confirm('Resolve selected?')">Resolve Selected</button>
                <button type="submit" formaction="/admin/reports/bulk-dismiss" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Dismiss selected?')">Dismiss Selected</button>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead><tr>
                    <th width="30"></th>
                    <th width="50">ID</th>
                    <th>Manga</th>
                    <th>Chapter</th>
                    <th>Reason</th>
                    <th>Note</th>
                    <th>User</th>
                    <th>IP</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr></thead>
                <tbody>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="11" class="text-center text-muted p-3">No reports found.</td></tr>
                <?php endif; ?>
                <?php foreach ($reports as $r): ?>
                    <tr>
                        <td><input type="checkbox" name="report_ids[]" value="<?= $r->id ?>" class="report-cb"></td>
                        <td><?= $r->id ?></td>
                        <td><a href="/admin/chapters/<?= $r->manga_id ?>" title="<?= esc($r->manga_name) ?>"><?= esc(mb_substr($r->manga_name ?? 'N/A', 0, 25)) ?></a></td>
                        <td>
                            <a href="/admin/chapters/edit/<?= $r->chapter_id ?>">Ch.<?= esc($r->chapter_number ?? $r->chapter_id) ?></a>
                        </td>
                        <?php
                            $reasonLabels = [
                                'broken_images' => 'Broken/Not loading',
                                'wrong_images'  => 'Wrong images',
                                'missing_pages' => 'Missing pages',
                                'low_quality'   => 'Low quality',
                                'wrong_order'   => 'Wrong order',
                                'other'         => 'Other',
                            ];
                        ?>
                        <td><span class="badge bg-danger"><?= esc($reasonLabels[$r->reason] ?? $r->reason) ?></span></td>
                        <td title="<?= esc($r->note) ?>"><?= esc(mb_substr($r->note ?? '', 0, 30)) ?></td>
                        <td><?= esc($r->username ?? 'Guest') ?></td>
                        <td><small><?= esc($r->ip_address) ?></small></td>
                        <td><small><?= date('d/m H:i', strtotime($r->created_at)) ?></small></td>
                        <td>
                            <?php if ($r->status === 'pending'): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php elseif ($r->status === 'resolved'): ?>
                                <span class="badge bg-success">Resolved</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Dismissed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r->status === 'pending'): ?>
                                <a href="/admin/reports/resolve/<?= $r->id ?>" class="btn btn-outline-success btn-sm">Resolve</a>
                                <a href="/admin/reports/dismiss/<?= $r->id ?>" class="btn btn-outline-secondary btn-sm">Dismiss</a>
                            <?php endif; ?>
                            <a href="/admin/reports/delete/<?= $r->id ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete?')">Del</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<?php $totalPages = (int) ceil($total / $perPage); if ($totalPages > 1): ?>
<?php
    $range = 5;
    $start = max(1, $page - $range);
    $end = min($totalPages, $page + $range);
?>
<nav class="mt-3 d-flex align-items-center gap-3">
    <ul class="pagination pagination-sm mb-0">
        <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?status=<?= $status ?>&page=1">&laquo;</a></li>
        <?php endif; ?>
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?status=<?= $status ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="?status=<?= $status ?>&page=<?= $totalPages ?>">&raquo;</a></li>
        <?php endif; ?>
    </ul>
    <small class="text-muted">Page <?= $page ?> / <?= number_format($totalPages) ?></small>
</nav>
<?php endif; ?>
<script>
function reportAction(url) {
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = url;
    var t = document.createElement('input');
    t.type = 'hidden';
    t.name = '<?= csrf_token() ?>';
    t.value = '<?= csrf_hash() ?>';
    f.appendChild(t);
    document.body.appendChild(f);
    f.submit();
}
</script>
<?= $this->endSection() ?>
