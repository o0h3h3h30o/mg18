<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($item); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $isEdit ? 'Edit Chapter' : 'Create Chapter' ?> - <?= esc($manga->name) ?></h4>
    <div class="d-flex gap-2">
        <a href="/admin/chapters/<?= $manga->id ?>" class="btn btn-outline-info btn-sm">Chapters</a>
        <a href="/admin/manga/edit/<?= $manga->id ?>" class="btn btn-secondary btn-sm">Back to Manga</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= $isEdit ? '/admin/chapters/update/' . $item->id : '/admin/chapters/store/' . $manga->id ?>" method="post">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Number <span class="text-danger">*</span></label>
                        <input type="text" name="number" class="form-control" value="<?= esc($isEdit ? $item->number : ($nextNumber ?? old('number'))) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= esc($isEdit ? $item->name : old('name')) ?>" placeholder="e.g. Chapter 10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?= esc($isEdit ? $item->slug : old('slug')) ?>" placeholder="Auto-generated if empty">
                        <?php if (!$isEdit): ?><small class="text-muted">Leave blank to auto-generate from name</small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Source URL</label>
                        <input type="text" name="source_url" class="form-control" value="<?= esc($isEdit ? ($item->source_url ?? '') : old('source_url')) ?>" placeholder="https://manga18fx.com/manga/xxx/chapter-1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Crawl Status</label>
                        <select name="is_crawling" class="form-select">
                            <?php $crawlVal = $isEdit ? (int)$item->is_crawling : 0; ?>
                            <option value="0" <?= $crawlVal === 0 ? 'selected' : '' ?>>0 - Done</option>
                            <option value="1" <?= $crawlVal === 1 ? 'selected' : '' ?>>1 - Crawling</option>
                            <option value="2" <?= $crawlVal === 2 ? 'selected' : '' ?>>2 - Need crawl</option>
                        </select>
                    </div>
                    <div class="form-check mb-2">
                        <input type="hidden" name="is_show" value="0">
                        <input type="checkbox" name="is_show" value="1" class="form-check-input" <?= ($isEdit && $item->is_show) ? 'checked' : '' ?>>
                        <label class="form-check-label">Visible</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="hidden" name="need_login" value="0">
                        <input type="checkbox" name="need_login" value="1" class="form-check-input" <?= ($isEdit && $item->need_login) ? 'checked' : '' ?>>
                        <label class="form-check-label">Need Login</label>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary"><?= $isEdit ? 'Save' : 'Create Chapter' ?></button>
            <?php if ($isEdit): ?>
            <a href="/admin/updatePublishChapter?chapter_id=<?= $item->id ?>" class="btn btn-success" onclick="return confirm('Publish this chapter and update manga info?')">
                <i class="bi bi-send-fill"></i> Publish Chapter
            </a>
            <?php endif; ?>
            <a href="/admin/manga/edit/<?= $manga->id ?>" class="btn btn-secondary">Back to Manga</a>
        </form>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            var nameEl = document.querySelector('input[name="name"]');
            var numberEl = document.querySelector('input[name="number"]');
            var slugEl = document.querySelector('input[name="slug"]');
            function genSlug(){
                if(slugEl.dataset.manual) return;
                var name = nameEl.value.trim();
                var number = numberEl.value.trim();
                var raw = name || ('chapter-' + number);
                slugEl.value = raw.toLowerCase()
                    .replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g,'a')
                    .replace(/[èéẹẻẽêềếệểễ]/g,'e')
                    .replace(/[ìíịỉĩ]/g,'i')
                    .replace(/[òóọỏõôồốộổỗơờớợởỡ]/g,'o')
                    .replace(/[ùúụủũưừứựửữ]/g,'u')
                    .replace(/[ỳýỵỷỹ]/g,'y')
                    .replace(/đ/g,'d')
                    .replace(/[^a-z0-9\s-]/g,'')
                    .replace(/[\s_]+/g,'-')
                    .replace(/-+/g,'-')
                    .replace(/^-|-$/g,'');
            }
            function genName(){
                if(nameEl.dataset.manual) return;
                var number = numberEl.value.trim();
                if(number) nameEl.value = 'Chapter ' + number;
            }
            nameEl.addEventListener('input', function(){
                this.dataset.manual = this.value ? '1' : '';
                genSlug();
            });
            nameEl.addEventListener('keyup', genSlug);
            numberEl.addEventListener('input', function(){ genName(); genSlug(); });
            numberEl.addEventListener('keyup', function(){ genName(); genSlug(); });
            slugEl.addEventListener('input', function(){ this.dataset.manual = this.value ? '1' : ''; });
            // Auto generate on load if number has value (create mode)
            if(numberEl.value.trim() && !nameEl.value.trim()){
                genName(); genSlug();
            }
        });
        </script>
    </div>
</div>

<?php if ($isEdit): ?>
<!-- Page Upload Section -->
<div class="card mt-4">
    <div class="card-header">
        <strong><i class="bi bi-upload"></i> Upload Pages</strong>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs" id="uploadTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab_upload_single">
                    <i class="bi bi-image"></i> Single Image
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab_upload_zip">
                    <i class="bi bi-file-earmark-zip"></i> ZIP Upload
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab_upload_bulk">
                    <i class="bi bi-link-45deg"></i> Bulk URLs
                </a>
            </li>
        </ul>
        <div class="tab-content pt-3">
            <!-- Multi Image AJAX Upload -->
            <div class="tab-pane active" id="tab_upload_single">
                <div class="mb-3">
                    <label class="form-label">Select Images <small class="text-muted">(multiple)</small></label>
                    <input type="file" id="multiImageInput" class="form-control" accept="image/*" multiple>
                </div>
                <div id="uploadQueue" class="mb-3"></div>
                <button type="button" class="btn btn-primary" id="btnStartUpload" disabled>
                    <i class="bi bi-cloud-upload"></i> Upload All (<span id="uploadCount">0</span>)
                </button>
                <div id="uploadProgress" class="mt-2 d-none">
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar" id="uploadBar" style="width:0%"></div>
                    </div>
                    <small class="text-muted" id="uploadProgressText"></small>
                </div>
            </div>

            <!-- ZIP Upload -->
            <div class="tab-pane" id="tab_upload_zip">
                <form action="/admin/pages/upload-zip/<?= $item->id ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <label class="form-label">Select ZIP File</label>
                            <input type="file" name="zipfile" class="form-control" accept=".zip" required>
                            <small class="text-muted">ZIP containing images (JPG/PNG/WebP/GIF). Images will be sorted by filename and stored locally.</small>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100"><i class="bi bi-file-earmark-zip"></i> Extract & Upload</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Bulk URL Paste -->
            <div class="tab-pane" id="tab_upload_bulk">
                <form action="/admin/pages/upload-bulk/<?= $item->id ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Paste Image URLs (one per line)</label>
                        <textarea name="urls" class="form-control font-monospace" rows="8" placeholder="https://example.com/image1.jpg
https://example.com/image2.jpg
https://example.com/image3.jpg" required></textarea>
                        <small class="text-muted">Each URL on a separate line. Images will be stored as external links (not downloaded).</small>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-link-45deg"></i> Add All URLs</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Existing Pages -->
<?php
    $imgBaseUrl = config('Manga')->cdnUrl . '/manga/' . $manga->slug . '/chapters/' . $item->slug . '/';
?>
<?php if (!empty($pages)): ?>
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Chapter Images (<?= count($pages) ?> pages)</strong>
        <div>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleSelectAll()">Select All</button>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteSelected()" id="btnDeleteSelected" disabled>Delete Selected (<span id="selectedCount">0</span>)</button>
            <form action="/admin/pages/delete-all/<?= $item->id ?>" method="post" class="d-inline" onsubmit="return confirm('DELETE ALL <?= count($pages) ?> pages? This cannot be undone!')">
                <?= csrf_field() ?>
                <button class="btn btn-danger btn-sm">Delete All</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <form action="/admin/pages/delete-batch" method="post" id="batchDeleteForm" onsubmit="return confirm('Delete selected pages?')">
            <?= csrf_field() ?>
            <input type="hidden" name="chapter_id" value="<?= $item->id ?>">
            <div class="row g-2">
                <?php foreach ($pages as $p): ?>
                    <?php $imgSrc = $p->external ? $p->image : $imgBaseUrl . $p->image; ?>
                    <div class="col-6 col-md-3 col-lg-2 text-center">
                        <div class="border rounded p-1 position-relative page-item" data-id="<?= $p->id ?>">
                            <input type="checkbox" name="page_ids[]" value="<?= $p->id ?>" class="form-check-input position-absolute page-checkbox" style="top:5px;left:5px;z-index:1;" onchange="updateSelectedCount()">
                            <img src="<?= esc($imgSrc) ?>" class="img-fluid" style="max-height:200px;cursor:pointer;" loading="lazy" onclick="this.closest('.page-item').querySelector('.page-checkbox').click()">
                            <small class="d-block mt-1 text-muted">#<?= $p->slug ?> <?= $p->external ? '<span class="badge bg-info">ext</span>' : '' ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</div>
<script>
function toggleSelectAll() {
    const boxes = document.querySelectorAll('.page-checkbox');
    const allChecked = [...boxes].every(b => b.checked);
    boxes.forEach(b => b.checked = !allChecked);
    updateSelectedCount();
}
function updateSelectedCount() {
    const count = document.querySelectorAll('.page-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('btnDeleteSelected').disabled = count === 0;
    document.querySelectorAll('.page-item').forEach(el => {
        const cb = el.querySelector('.page-checkbox');
        el.style.outline = cb.checked ? '3px solid #dc3545' : '';
    });
}
function deleteSelected() {
    const count = document.querySelectorAll('.page-checkbox:checked').length;
    if (count && confirm('Delete ' + count + ' selected pages?')) {
        document.getElementById('batchDeleteForm').submit();
    }
}
</script>
<?php else: ?>
<div class="alert alert-info mt-4">No pages/images in this chapter yet. Use the upload options above to add pages.</div>
<?php endif; ?>

<!-- AJAX Multi Upload Script -->
<script>
(function(){
    var chapterId = <?= $item->id ?>;
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var nextSlug = <?= count($pages) + 1 ?>;
    var queue = [];
    var fileInput = document.getElementById('multiImageInput');
    var queueDiv = document.getElementById('uploadQueue');
    var btnStart = document.getElementById('btnStartUpload');
    var countSpan = document.getElementById('uploadCount');

    fileInput.addEventListener('change', function(){
        var files = Array.from(this.files);
        // Sort by name
        files.sort(function(a,b){ return a.name.localeCompare(b.name, undefined, {numeric:true}); });
        files.forEach(function(f){
            var idx = queue.length;
            queue.push({file: f, slug: nextSlug, status: 'pending'});
            var row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2 mb-1 upload-row';
            row.id = 'urow-' + idx;
            row.innerHTML = '<img src="'+URL.createObjectURL(f)+'" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">' +
                '<span class="flex-grow-1 text-truncate" style="font-size:12px;">'+f.name+'</span>' +
                '<input type="number" class="form-control form-control-sm" style="width:70px;" value="'+nextSlug+'" data-idx="'+idx+'" onchange="updateQueueSlug(this)">' +
                '<span class="upload-status" id="ustatus-'+idx+'"><i class="bi bi-hourglass text-muted"></i></span>' +
                '<button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFromQueue('+idx+',this)"><i class="bi bi-x"></i></button>';
            queueDiv.appendChild(row);
            nextSlug++;
        });
        countSpan.textContent = queue.filter(function(q){return q.status==='pending';}).length;
        btnStart.disabled = queue.filter(function(q){return q.status==='pending';}).length === 0;
        this.value = '';
    });

    window.updateQueueSlug = function(el){
        queue[el.dataset.idx].slug = el.value;
    };
    window.removeFromQueue = function(idx, btn){
        queue[idx].status = 'removed';
        btn.closest('.upload-row').remove();
        countSpan.textContent = queue.filter(function(q){return q.status==='pending';}).length;
        btnStart.disabled = queue.filter(function(q){return q.status==='pending';}).length === 0;
    };

    btnStart.addEventListener('click', async function(){
        var pending = queue.filter(function(q){return q.status==='pending';});
        if (!pending.length) return;
        btnStart.disabled = true;
        var progWrap = document.getElementById('uploadProgress');
        var progBar = document.getElementById('uploadBar');
        var progText = document.getElementById('uploadProgressText');
        progWrap.classList.remove('d-none');
        var total = pending.length, done = 0, fail = 0;

        for (var i = 0; i < queue.length; i++) {
            if (queue[i].status !== 'pending') continue;
            var statusEl = document.getElementById('ustatus-' + i);
            statusEl.innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span>';

            var fd = new FormData();
            fd.append('image', queue[i].file);
            fd.append('slug', queue[i].slug);
            fd.append(csrfName, csrfHash);

            try {
                var res = await fetch('/admin/pages/upload/' + chapterId, {method:'POST', body:fd});
                var json = await res.json();
                if (json.status === 1) {
                    queue[i].status = 'done';
                    statusEl.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
                    if (json.csrf_hash) csrfHash = json.csrf_hash;
                    done++;
                } else {
                    queue[i].status = 'error';
                    statusEl.innerHTML = '<i class="bi bi-x-circle-fill text-danger" title="'+json.msg+'"></i>';
                    fail++;
                }
            } catch(e) {
                queue[i].status = 'error';
                statusEl.innerHTML = '<i class="bi bi-x-circle-fill text-danger" title="'+e.message+'"></i>';
                fail++;
            }
            progBar.style.width = Math.round((done+fail)/total*100) + '%';
            progText.textContent = (done+fail) + '/' + total + (fail ? ' ('+fail+' failed)' : '');
        }
        progText.textContent = 'Done! ' + done + ' uploaded' + (fail ? ', ' + fail + ' failed' : '') + '. Reloading...';
        setTimeout(function(){ location.reload(); }, 1500);
    });
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
