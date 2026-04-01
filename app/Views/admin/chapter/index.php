<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Chapters - <?= esc($manga->name) ?> (<?= count($chapters) ?>)</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info btn-sm" id="btnFetchSource" onclick="fetchFromSource()">
            <i class="bi bi-cloud-download"></i> Fetch from Manga18fx
        </button>
        <a href="/admin/chapters/create/<?= $manga->id ?>" class="btn btn-success btn-sm"><i class="bi bi-plus-lg"></i> Create Chapter</a>
        <a href="/admin/manga/edit/<?= $manga->id ?>" class="btn btn-secondary btn-sm">Back to Manga</a>
    </div>
</div>

<!-- Fetch Chapters Panel (hidden by default) -->
<div class="card mb-3 d-none" id="fetchPanel">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-cloud-download"></i> Chapters from Manga18fx</strong>
        <div>
            <span id="fetchInfo" class="text-muted me-2"></span>
            <button class="btn btn-success btn-sm" id="btnImportSelected" onclick="importSelected()" disabled>
                <i class="bi bi-download"></i> Import Selected (<span id="importCount">0</span>)
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('fetchPanel').classList.add('d-none')">Close</button>
        </div>
    </div>
    <div class="card-body p-0" id="fetchBody">
        <div class="text-center p-3" id="fetchLoading"><span class="spinner-border spinner-border-sm"></span> Fetching...</div>
        <table class="table table-sm table-hover mb-0 d-none" id="fetchTable">
            <thead><tr>
                <th width="30"><input type="checkbox" id="fetchSelectAll" onchange="toggleFetchAll(this)"></th>
                <th>Number</th><th>Name</th><th>URL</th><th>Status</th>
            </tr></thead>
            <tbody id="fetchTbody"></tbody>
        </table>
    </div>
</div>

<!-- Chapter List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2" id="bulkBar" style="display:none !important;">
        <div>
            <input type="checkbox" id="selectAll" onchange="toggleAll(this)"> Select All
        </div>
        <div>
            <button class="btn btn-danger btn-sm" id="btnBulkDelete" onclick="bulkDelete()" disabled>
                <i class="bi bi-trash"></i> Delete Selected (<span id="deleteCount">0</span>)
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <form id="bulkDeleteForm" action="/admin/chapters/bulk-delete/<?= $manga->id ?>" method="post" onsubmit="return confirm('Delete selected chapters and their files? This cannot be undone!')">
            <?= csrf_field() ?>
            <table class="table table-sm table-hover mb-0">
                <thead><tr>
                    <th width="30"><input type="checkbox" id="selectAllTop" onchange="toggleAll(this)"></th>
                    <th>ID</th><th>Number</th><th>Name</th><th>Views</th><th>Created</th><th>Crawl</th><th>Show</th><th>Login</th><th width="150">Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($chapters as $ch): ?>
                    <tr>
                        <td><input type="checkbox" name="chapter_ids[]" value="<?= $ch->id ?>" class="ch-check" onchange="updateDeleteCount()"></td>
                        <td><?= $ch->id ?></td>
                        <td><?= esc($ch->number) ?></td>
                        <td>
                            <?= esc($ch->name ?: '-') ?>
                            <?php if (!empty($ch->source_url)): ?>
                                <a href="<?= esc($ch->source_url) ?>" target="_blank" class="text-muted ms-1" title="Source"><i class="bi bi-box-arrow-up-right" style="font-size:10px"></i></a>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($ch->view) ?></td>
                        <td><small class="text-muted"><?= !empty($ch->created_at) ? date('d/m/Y H:i', strtotime($ch->created_at)) : '-' ?></small></td>
                        <td>
                            <?php if ((int)$ch->is_crawling === 0): ?>
                                <span class="badge bg-success">Done</span>
                            <?php elseif ((int)$ch->is_crawling === 1): ?>
                                <span class="badge bg-warning">Crawling</span>
                            <?php else: ?>
                                <span class="badge bg-info">Need crawl</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $ch->is_show ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                        <td><?= $ch->need_login ? '<span class="badge bg-warning">Yes</span>' : '-' ?></td>
                        <td>
                            <a href="/admin/pages/<?= $ch->id ?>" class="btn btn-outline-info btn-sm" title="Manage Images">Pages</a>
                            <a href="/admin/chapters/edit/<?= $ch->id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                            <form action="/admin/chapters/delete/<?= $ch->id ?>" method="post" class="d-inline" onsubmit="return confirm('Delete?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline-danger btn-sm">Del</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </div>
</div>

<script>
// ====== BULK DELETE ======
function toggleAll(el) {
    document.querySelectorAll('.ch-check').forEach(c => c.checked = el.checked);
    document.getElementById('selectAllTop').checked = el.checked;
    updateDeleteCount();
}
function updateDeleteCount() {
    var count = document.querySelectorAll('.ch-check:checked').length;
    document.getElementById('deleteCount').textContent = count;
    document.getElementById('btnBulkDelete').disabled = count === 0;
    // Show/hide bulk bar
    document.getElementById('bulkBar').style.display = count > 0 ? 'flex' : 'none';
    document.getElementById('bulkBar').classList.toggle('d-none', count === 0);
}
function bulkDelete() {
    var count = document.querySelectorAll('.ch-check:checked').length;
    if (count && confirm('Delete ' + count + ' chapter(s) and all their files? This cannot be undone!')) {
        document.getElementById('bulkDeleteForm').submit();
    }
}

// ====== FETCH FROM SOURCE ======
var fetchedChapters = [];

function fetchFromSource() {
    var panel = document.getElementById('fetchPanel');
    panel.classList.remove('d-none');
    document.getElementById('fetchLoading').classList.remove('d-none');
    document.getElementById('fetchTable').classList.add('d-none');
    document.getElementById('fetchInfo').textContent = '';

    fetch('/admin/chapters/fetch-source/<?= $manga->id ?>')
        .then(r => r.json())
        .then(d => {
            document.getElementById('fetchLoading').classList.add('d-none');
            if (d.status !== 'ok') {
                document.getElementById('fetchInfo').innerHTML = '<span class="text-danger">' + d.message + '</span>';
                return;
            }
            fetchedChapters = d.chapters;
            document.getElementById('fetchInfo').textContent = d.total + ' chapters found, ' + d.new + ' new';
            renderFetchTable(d.chapters);
        })
        .catch(e => {
            document.getElementById('fetchLoading').classList.add('d-none');
            document.getElementById('fetchInfo').innerHTML = '<span class="text-danger">Error: ' + e.message + '</span>';
        });
}

function renderFetchTable(chapters) {
    var tbody = document.getElementById('fetchTbody');
    tbody.innerHTML = '';
    chapters.forEach(function(ch, i) {
        var tr = document.createElement('tr');
        if (ch.exists) tr.style.opacity = '0.5';
        tr.innerHTML =
            '<td>' + (ch.exists ? '<span class="text-muted">✓</span>' : '<input type="checkbox" class="fetch-check" data-idx="' + i + '" onchange="updateImportCount()"' + (!ch.exists ? ' checked' : '') + '>') + '</td>' +
            '<td>' + ch.number + '</td>' +
            '<td>' + ch.name + '</td>' +
            '<td><a href="' + ch.url + '" target="_blank" class="text-muted" style="font-size:11px;">' + ch.url.substring(0, 60) + '...</a></td>' +
            '<td>' + (ch.exists ? '<span class="badge bg-secondary">Exists</span>' : '<span class="badge bg-success">New</span>') + '</td>';
        tbody.appendChild(tr);
    });
    document.getElementById('fetchTable').classList.remove('d-none');
    updateImportCount();
}

function toggleFetchAll(el) {
    document.querySelectorAll('.fetch-check').forEach(c => c.checked = el.checked);
    updateImportCount();
}

function updateImportCount() {
    var count = document.querySelectorAll('.fetch-check:checked').length;
    document.getElementById('importCount').textContent = count;
    document.getElementById('btnImportSelected').disabled = count === 0;
}

function importSelected() {
    var checks = document.querySelectorAll('.fetch-check:checked');
    if (!checks.length) return;

    var items = [];
    checks.forEach(function(c) {
        var ch = fetchedChapters[c.dataset.idx];
        items.push({ number: ch.number, url: ch.url });
    });

    if (!confirm('Import ' + items.length + ' chapter(s)? Crawler will auto-crawl them.')) return;

    document.getElementById('btnImportSelected').disabled = true;
    document.getElementById('btnImportSelected').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Importing...';

    fetch('/admin/chapters/import/<?= $manga->id ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(items)
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'ok') {
            alert(d.message);
            location.reload();
        } else {
            alert('Error: ' + d.message);
            document.getElementById('btnImportSelected').disabled = false;
            document.getElementById('btnImportSelected').innerHTML = '<i class="bi bi-download"></i> Import Selected';
        }
    })
    .catch(e => {
        alert('Error: ' + e.message);
        document.getElementById('btnImportSelected').disabled = false;
        document.getElementById('btnImportSelected').innerHTML = '<i class="bi bi-download"></i> Import Selected';
    });
}
</script>
<?= $this->endSection() ?>
