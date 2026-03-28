<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($item); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $isEdit ? 'Edit: ' . esc($item->name) : 'Create Manga' ?></h4>
    <div>
        <?php if ($isEdit): ?>
            <a href="/admin/chapters/<?= $item->id ?>" class="btn btn-outline-info btn-sm">View Chapters</a>
        <?php endif; ?>
        <a href="/admin/manga" class="btn btn-secondary btn-sm">Back</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form action="<?= $isEdit ? '/admin/manga/update/' . $item->id : '/admin/manga/store' ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="mangaName" class="form-control" value="<?= esc($isEdit ? $item->name : old('name')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <div class="input-group">
                            <input type="text" name="slug" id="mangaSlug" class="form-control" value="<?= esc($isEdit ? $item->slug : old('slug')) ?>" placeholder="auto-generated-from-name">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="generateSlug()"><i class="bi bi-arrow-repeat"></i> Generate</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">From Manga18fx</label>
                        <div class="input-group">
                            <input type="text" name="from_manga18fx" id="manga18fxUrl" class="form-control" value="<?= esc($isEdit ? ($item->from_manga18fx ?? '') : old('from_manga18fx')) ?>" placeholder="URL or slug, e.g. wireless-onahole-raw">
                            <button type="button" class="btn btn-outline-info" id="btnFetchManga18fx">
                                <i class="bi bi-cloud-download"></i> Fetch
                            </button>
                            <button type="button" class="btn btn-outline-warning" id="btnPasteHtml" title="Paste page source HTML">
                                <i class="bi bi-clipboard"></i> Paste HTML
                            </button>
                        </div>
                        <div id="fetchStatus" class="mt-1"></div>
                        <div id="pasteHtmlWrap" class="mt-2 d-none">
                            <textarea id="pasteHtmlArea" class="form-control form-control-sm" rows="4" placeholder="Paste full HTML source from manga18fx page here (Ctrl+U in browser)..."></textarea>
                            <button type="button" class="btn btn-warning btn-sm mt-1" id="btnParseHtml"><i class="bi bi-lightning"></i> Parse</button>
                            <button type="button" class="btn btn-secondary btn-sm mt-1" id="btnCancelPaste">Cancel</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Other Names</label>
                        <input type="text" name="otherNames" class="form-control" value="<?= esc($isEdit ? $item->otherNames : old('otherNames')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Summary</label>
                        <textarea name="summary" class="form-control" rows="8" id="summaryEditor"><?= $isEdit ? $item->summary : old('summary') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categories</label>
                        <div class="row">
                        <?php foreach ($categories as $cat): ?>
                            <div class="col-md-3 col-6">
                                <div class="form-check">
                                    <input type="checkbox" name="category_ids[]" value="<?= $cat->id ?>" class="form-check-input"
                                        <?= in_array($cat->id, $selectedCatIds) ? 'checked' : '' ?>>
                                    <label class="form-check-label"><?= esc($cat->name) ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Authors</label>
                        <div id="authorsWrap" class="tag-input-wrap">
                            <?php foreach ($selectedAuthors as $a): ?>
                            <span class="tag-badge"><?= esc($a->name) ?><input type="hidden" name="authors[]" value="<?= esc($a->name) ?>"><button type="button" class="tag-remove" onclick="this.parentElement.remove()">&times;</button></span>
                            <?php endforeach; ?>
                            <input type="text" class="tag-input" id="authorInput" placeholder="Type to search..." autocomplete="off">
                        </div>
                        <div class="tag-suggestions" id="authorSuggestions"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Artists</label>
                        <div id="artistsWrap" class="tag-input-wrap">
                            <?php foreach ($selectedArtists as $a): ?>
                            <span class="tag-badge"><?= esc($a->name) ?><input type="hidden" name="artists[]" value="<?= esc($a->name) ?>"><button type="button" class="tag-remove" onclick="this.parentElement.remove()">&times;</button></span>
                            <?php endforeach; ?>
                            <input type="text" class="tag-input" id="artistInput" placeholder="Type to search..." autocomplete="off">
                        </div>
                        <div class="tag-suggestions" id="artistSuggestions"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <div id="tagsWrap" class="tag-input-wrap">
                            <?php foreach ($selectedTags as $t): ?>
                            <span class="tag-badge"><?= esc($t->name) ?><input type="hidden" name="tags[]" value="<?= esc($t->name) ?>"><button type="button" class="tag-remove" onclick="this.parentElement.remove()">&times;</button></span>
                            <?php endforeach; ?>
                            <input type="text" class="tag-input" id="tagInput" placeholder="Type to search..." autocomplete="off">
                        </div>
                        <div class="tag-suggestions" id="tagSuggestions"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Cover</label>
                        <div class="mb-2 text-center">
                            <?php if ($isEdit): ?>
                            <img src="/manga/<?= $item->slug ?>/cover/cover_250x350.jpg?t=<?= time() ?>" class="img-fluid rounded border" style="max-height:200px;" id="coverPreview" onerror="this.src='<?= config('Manga')->cdnUrl ?>/manga/<?= $item->slug ?>/cover/cover_250x350.jpg?t=<?= time() ?>'">
                            <?php else: ?>
                            <img src="" class="img-fluid rounded border d-none" style="max-height:200px;" id="coverPreview">
                            <?php endif; ?>
                        </div>
                        <div class="mb-2">
                            <input type="file" <?= $isEdit ? '' : 'name="cover"' ?> id="coverFile" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" id="coverUrl" class="form-control" placeholder="Or paste image URL...">
                            <button type="button" class="btn btn-outline-primary" id="btnFetchCover">Fetch</button>
                        </div>
                        <?php if ($isEdit): ?>
                        <button type="button" class="btn btn-success btn-sm w-100 d-none" id="btnSaveCover"><i class="bi bi-cloud-upload"></i> Save Cover</button>
                        <?php else: ?>
                        <input type="hidden" name="cover_url" id="coverUrlHidden">
                        <?php endif; ?>
                        <div id="coverStatus" class="mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status_id" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= $s->id ?>" <?= ($isEdit && $item->status_id == $s->id) ? 'selected' : '' ?>><?= esc($s->label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type_id" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?= $t->id ?>" <?= ($isEdit && $item->type_id == $t->id) ? 'selected' : '' ?>><?= esc($t->label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($isEdit): ?>
                    <div class="mb-3">
                        <label class="form-label">Stats</label>
                        <ul class="list-group list-group-sm">
                            <li class="list-group-item d-flex justify-content-between"><span>Views</span><strong><?= number_format($item->views) ?></strong></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Day</span><strong><?= number_format($item->view_day) ?></strong></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Month</span><strong><?= number_format($item->view_month) ?></strong></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Rating</span><strong><?= $item->rating ?></strong></li>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <div class="form-check mb-2">
                        <input type="hidden" name="is_public" value="0">
                        <input type="checkbox" name="is_public" value="1" class="form-check-input" <?= ($isEdit ? $item->is_public : 1) ? 'checked' : '' ?>>
                        <label class="form-check-label">Public</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="hidden" name="hot" value="0">
                        <input type="checkbox" name="hot" value="1" class="form-check-input" <?= ($isEdit && $item->hot) ? 'checked' : '' ?>>
                        <label class="form-check-label">Hot</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="hidden" name="is_new" value="0">
                        <input type="checkbox" name="is_new" value="1" class="form-check-input" <?= ($isEdit ? $item->is_new : 1) ? 'checked' : '' ?>>
                        <label class="form-check-label">New</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="hidden" name="caution" value="0">
                        <input type="checkbox" name="caution" value="1" class="form-check-input" <?= ($isEdit && $item->caution) ? 'checked' : '' ?>>
                        <label class="form-check-label text-danger"><i class="bi bi-exclamation-triangle"></i> 18+</label>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary"><?= $isEdit ? 'Save' : 'Create Manga' ?></button>
            <a href="/admin/manga" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
<style>
/* Tag input */
.tag-input-wrap{display:flex;flex-wrap:wrap;align-items:center;gap:6px;padding:8px 12px;border:1px solid var(--kt-border,#2d2d3f);border-radius:8px;background:var(--kt-card-bg,#1e1e2d);min-height:44px;cursor:text;transition:border-color .2s,box-shadow .2s}
.tag-input-wrap:focus-within{border-color:var(--kt-primary,#6366f1);box-shadow:0 0 0 3px rgba(99,102,241,.15)}
.tag-badge{display:inline-flex;align-items:center;gap:5px;background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(99,102,241,.08));color:#a5b4fc;border-radius:6px;padding:4px 10px 4px 12px;font-size:12.5px;font-weight:500;border:1px solid rgba(99,102,241,.22);transition:all .15s;white-space:nowrap}
.tag-badge:hover{background:linear-gradient(135deg,rgba(99,102,241,.28),rgba(99,102,241,.15));border-color:rgba(99,102,241,.35)}
.tag-remove{background:none;border:none;color:#7c7e9a;cursor:pointer;font-size:16px;padding:0 1px;line-height:1;transition:color .15s;display:flex;align-items:center}
.tag-remove:hover{color:#ef4444}
.tag-input{border:none;outline:none;flex:1;min-width:120px;font-size:13px;background:transparent;color:var(--kt-text,#cdcde4);padding:2px 0}
.tag-input::placeholder{color:var(--kt-text-muted,#6c6e82)}
.tag-suggestions{position:relative}
.tag-suggestions ul{position:absolute;z-index:999;background:var(--kt-card-bg,#1e1e2d);border:1px solid var(--kt-border,#2d2d3f);border-radius:8px;list-style:none;padding:4px 0;margin:4px 0 0;width:100%;max-height:240px;overflow-y:auto;box-shadow:0 12px 32px rgba(0,0,0,.4)}
.tag-suggestions ul::-webkit-scrollbar{width:6px}
.tag-suggestions ul::-webkit-scrollbar-thumb{background:#3d3d5c;border-radius:3px}
.tag-suggestions li{padding:9px 14px;cursor:pointer;font-size:13px;color:var(--kt-text,#cdcde4);transition:all .1s;border-left:2px solid transparent}
.tag-suggestions li:hover{background:rgba(99,102,241,.08);color:#e0e0ff;border-left-color:var(--kt-primary,#6366f1)}
.tag-suggestions li.active{background:var(--kt-primary,#6366f1);color:#fff;border-left-color:#fff}
.tag-suggestions li.tag-create{color:#4ade80;font-style:italic;border-top:1px solid var(--kt-border,#2d2d3f)}
.tag-suggestions li.tag-create:hover{background:rgba(34,197,94,.1);color:#4ade80;border-left-color:#4ade80}

/* Fetch manga18fx */
#fetchStatus .spinner-border{width:14px;height:14px;border-width:2px}
</style>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
CKEDITOR.config.versionCheck = false;
CKEDITOR.replace('summaryEditor', {
    height: 250,
    toolbar: [
        { name: 'basic', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
        { name: 'list', items: ['BulletedList', 'NumberedList'] },
        { name: 'link', items: ['Link', 'Unlink'] },
        { name: 'tools', items: ['Source'] }
    ],
    removePlugins: 'image,uploadimage',
    allowedContent: true
});
function generateSlug() {
    var name = document.getElementById('mangaName').value;
    var slug = name.toLowerCase()
        .replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g, 'a')
        .replace(/[èéẹẻẽêềếệểễ]/g, 'e')
        .replace(/[ìíịỉĩ]/g, 'i')
        .replace(/[òóọỏõôồốộổỗơờớợởỡ]/g, 'o')
        .replace(/[ùúụủũưừứựửữ]/g, 'u')
        .replace(/[ỳýỵỷỹ]/g, 'y')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s_]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    document.getElementById('mangaSlug').value = slug;
}
document.addEventListener('DOMContentLoaded', function() {
    var nameEl = document.getElementById('mangaName');
    var slugEl = document.getElementById('mangaSlug');
    nameEl.addEventListener('input', function() {
        if (!slugEl.dataset.manual) generateSlug();
    });
    nameEl.addEventListener('keyup', function() {
        if (!slugEl.dataset.manual) generateSlug();
    });
    slugEl.addEventListener('input', function() {
        this.dataset.manual = this.value ? '1' : '';
    });
});

var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
var pendingCoverFile = null;
var preview = document.getElementById('coverPreview');
var status = document.getElementById('coverStatus');
var fileInput = document.getElementById('coverFile');

function showPreview(src) {
    preview.src = src;
    preview.classList.remove('d-none');
}

// File input: preview
fileInput.addEventListener('change', function(){
    if (!this.files[0]) return;
    showPreview(URL.createObjectURL(this.files[0]));
    if (isEdit) {
        pendingCoverFile = this.files[0];
        status.innerHTML = '<span class="text-warning"><i class="bi bi-info-circle"></i> Preview - click Save Cover to apply</span>';
        document.getElementById('btnSaveCover').classList.remove('d-none');
    }
});

// Fetch URL: preview
document.getElementById('btnFetchCover').addEventListener('click', function(){
    var url = document.getElementById('coverUrl').value.trim();
    if (!url) return;
    var btn = this;
    btn.disabled = true;
    status.innerHTML = '<span class="text-info"><i class="bi bi-arrow-repeat"></i> Fetching...</span>';

    fetch(url, {mode: 'cors'})
    .then(function(r){ if (!r.ok) throw new Error('HTTP '+r.status); return r.blob(); })
    .then(function(blob){
        var ext = url.split('.').pop().split('?')[0] || 'jpg';
        var file = new File([blob], 'cover.'+ext, {type: blob.type});
        showPreview(URL.createObjectURL(blob));
        if (isEdit) {
            pendingCoverFile = file;
            status.innerHTML = '<span class="text-warning"><i class="bi bi-info-circle"></i> Preview - click Save Cover to apply</span>';
            document.getElementById('btnSaveCover').classList.remove('d-none');
        } else {
            // Set on file input for form submit
            var dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            document.getElementById('coverUrlHidden').value = '';
            status.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Fetched</span>';
            setTimeout(function(){ status.innerHTML = ''; }, 3000);
        }
        btn.disabled = false;
    })
    .catch(function(){
        // Fallback: canvas trick
        var img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function(){
            var canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            canvas.getContext('2d').drawImage(img, 0, 0);
            canvas.toBlob(function(blob2){
                var file2 = new File([blob2], 'cover.jpg', {type: 'image/jpeg'});
                showPreview(URL.createObjectURL(blob2));
                if (isEdit) {
                    pendingCoverFile = file2;
                    status.innerHTML = '<span class="text-warning"><i class="bi bi-info-circle"></i> Preview - click Save Cover to apply</span>';
                    document.getElementById('btnSaveCover').classList.remove('d-none');
                } else {
                    var dt = new DataTransfer();
                    dt.items.add(file2);
                    fileInput.files = dt.files;
                    document.getElementById('coverUrlHidden').value = '';
                    status.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Fetched</span>';
                    setTimeout(function(){ status.innerHTML = ''; }, 3000);
                }
                btn.disabled = false;
            }, 'image/jpeg', 0.92);
        };
        img.onerror = function(){
            // Last fallback: server-side on save
            showPreview(url);
            if (isEdit) {
                pendingCoverFile = '__server_url__' + url;
                status.innerHTML = '<span class="text-warning"><i class="bi bi-info-circle"></i> Preview - click Save Cover (server fetch)</span>';
                document.getElementById('btnSaveCover').classList.remove('d-none');
            } else {
                document.getElementById('coverUrlHidden').value = url;
                status.innerHTML = '<span class="text-warning"><i class="bi bi-info-circle"></i> Will fetch on save</span>';
            }
            btn.disabled = false;
        };
        img.src = url;
    });
});

<?php if ($isEdit): ?>
// Save Cover button: AJAX upload (edit only)
document.getElementById('btnSaveCover').addEventListener('click', function(){
    if (!pendingCoverFile) return;
    var fd = new FormData();
    if (typeof pendingCoverFile === 'string' && pendingCoverFile.startsWith('__server_url__')) {
        fd.append('image_url', pendingCoverFile.replace('__server_url__', ''));
    } else {
        fd.append('cover_file', pendingCoverFile);
    }
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    var saveBtn = this;
    saveBtn.disabled = true;
    status.innerHTML = '<span class="text-info"><i class="bi bi-arrow-repeat"></i> Saving...</span>';
    fetch('/admin/manga/upload-cover/<?= $item->id ?>', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(res){
        if (res.status === 1) {
            preview.src = res.cover;
            status.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> ' + res.msg + '</span>';
            pendingCoverFile = null;
            saveBtn.classList.add('d-none');
        } else {
            status.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> ' + res.msg + '</span>';
        }
        saveBtn.disabled = false;
        setTimeout(function(){ status.innerHTML = ''; }, 3000);
    })
    .catch(function(e){
        status.innerHTML = '<span class="text-danger">Error: ' + e.message + '</span>';
        saveBtn.disabled = false;
    });
});
<?php endif; ?>

// Tag input autocomplete
function initTagInput(inputId, suggestionsId, wrapId, apiUrl, fieldName) {
    var input = document.getElementById(inputId);
    var suggestions = document.getElementById(suggestionsId);
    var wrap = document.getElementById(wrapId);
    var timer = null;

    wrap.addEventListener('click', function(){ input.focus(); });

    input.addEventListener('input', function(){
        clearTimeout(timer);
        var q = this.value.trim();
        if (q.length < 1) { suggestions.innerHTML = ''; return; }
        timer = setTimeout(function(){
            fetch(apiUrl + '?q=' + encodeURIComponent(q))
            .then(function(r){ return r.json(); })
            .then(function(items){
                var html = '<ul>';
                items.forEach(function(item){
                    html += '<li data-name="'+item.name.replace(/"/g,'&quot;')+'">'+item.name+'</li>';
                });
                // Add "create new" option
                var q2 = input.value.trim();
                if (q2) {
                    var exists = items.some(function(item){ return item.name.toLowerCase() === q2.toLowerCase(); });
                    if (!exists) {
                        html += '<li data-name="'+q2.replace(/"/g,'&quot;')+'" class="tag-create"><i class="bi bi-plus-circle"></i> Create "'+q2+'"</li>';
                    }
                }
                html += '</ul>';
                suggestions.innerHTML = html;
            });
        }, 200);
    });

    input.addEventListener('keydown', function(e){
        // Tab or Enter to select
        if (e.key === 'Tab' || e.key === 'Enter') {
            var active = suggestions.querySelector('li.active');
            var firstItem = suggestions.querySelector('li');
            var inputVal = this.value.trim();
            if (e.key === 'Tab') {
                if (active) {
                    e.preventDefault();
                    addTag(active.dataset.name);
                } else if (firstItem) {
                    e.preventDefault();
                    addTag(firstItem.dataset.name);
                } else if (inputVal) {
                    e.preventDefault();
                    addTag(inputVal);
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                var val = active ? active.dataset.name : inputVal;
                if (val) addTag(val);
            }
        }
        // Arrow keys
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            var items = suggestions.querySelectorAll('li');
            if (!items.length) return;
            var current = suggestions.querySelector('li.active');
            if (current) current.classList.remove('active');
            if (e.key === 'ArrowDown') {
                var next = current ? current.nextElementSibling : items[0];
                if (next) next.classList.add('active');
            } else {
                var prev = current ? current.previousElementSibling : items[items.length-1];
                if (prev) prev.classList.add('active');
            }
        }
        // Backspace to remove last tag
        if (e.key === 'Backspace' && !this.value) {
            var badges = wrap.querySelectorAll('.tag-badge');
            if (badges.length) badges[badges.length-1].remove();
        }
    });

    suggestions.addEventListener('click', function(e){
        if (e.target.tagName === 'LI') addTag(e.target.dataset.name);
    });

    document.addEventListener('click', function(e){
        if (!wrap.contains(e.target) && !suggestions.contains(e.target)) suggestions.innerHTML = '';
    });

    function addTag(name) {
        // Check duplicate
        var existing = wrap.querySelectorAll('input[type=hidden]');
        for (var i = 0; i < existing.length; i++) {
            if (existing[i].value.toLowerCase() === name.toLowerCase()) { input.value = ''; suggestions.innerHTML = ''; return; }
        }
        var span = document.createElement('span');
        span.className = 'tag-badge';
        span.innerHTML = name + '<input type="hidden" name="'+fieldName+'" value="'+name.replace(/"/g,'&quot;')+'">' +
            '<button type="button" class="tag-remove" onclick="this.parentElement.remove()">&times;</button>';
        wrap.insertBefore(span, input);
        input.value = '';
        suggestions.innerHTML = '';
        input.focus();
    }
}

initTagInput('authorInput', 'authorSuggestions', 'authorsWrap', '/admin/api/search-authors', 'authors[]');
initTagInput('artistInput', 'artistSuggestions', 'artistsWrap', '/admin/api/search-authors', 'artists[]');
initTagInput('tagInput', 'tagSuggestions', 'tagsWrap', '/admin/api/search-tags', 'tags[]');

// ===== Paste HTML toggle =====
document.getElementById('btnPasteHtml').addEventListener('click', function(){
    document.getElementById('pasteHtmlWrap').classList.toggle('d-none');
    document.getElementById('pasteHtmlArea').focus();
});
document.getElementById('btnCancelPaste').addEventListener('click', function(){
    document.getElementById('pasteHtmlWrap').classList.add('d-none');
    document.getElementById('pasteHtmlArea').value = '';
});
document.getElementById('btnParseHtml').addEventListener('click', function(){
    var html = document.getElementById('pasteHtmlArea').value.trim();
    if (!html) return;
    var btn = this;
    var fetchStatus = document.getElementById('fetchStatus');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Parsing...';

    var fd = new FormData();
    fd.append('html', html);
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('/admin/manga/parse-manga18fx', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(res){
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning"></i> Parse';
        if (res.status !== 1) {
            fetchStatus.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> ' + res.msg + '</span>';
            return;
        }
        document.getElementById('pasteHtmlWrap').classList.add('d-none');
        document.getElementById('pasteHtmlArea').value = '';
        fillFormFromData(res.data);
        fetchStatus.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Parsed from HTML!</span>';
        setTimeout(function(){ fetchStatus.innerHTML = ''; }, 3000);
    })
    .catch(function(e){
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning"></i> Parse';
        fetchStatus.innerHTML = '<span class="text-danger">Error: ' + e.message + '</span>';
    });
});

// ===== Fetch Manga18fx =====
document.getElementById('btnFetchManga18fx').addEventListener('click', function(){
    var urlInput = document.getElementById('manga18fxUrl');
    var url = urlInput.value.trim();
    if (!url) { urlInput.focus(); return; }
    var btn = this;
    var fetchStatus = document.getElementById('fetchStatus');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Fetching...';
    fetchStatus.innerHTML = '';

    var fd = new FormData();
    fd.append('url', url);
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('/admin/manga/fetch-manga18fx', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(res){
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-download"></i> Fetch';

        if (res.status !== 1) {
            fetchStatus.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> ' + res.msg + '</span>';
            return;
        }
        fillFormFromData(res.data);
        fetchStatus.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Fetched! Form filled.</span>';
        setTimeout(function(){ fetchStatus.innerHTML = ''; }, 3000);
    })
    .catch(function(e){
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-download"></i> Fetch';
        fetchStatus.innerHTML = '<span class="text-danger">Error: ' + e.message + '</span>';
    });
});

function clearTagInput(wrapId, inputId) {
    var wrap = document.getElementById(wrapId);
    var badges = wrap.querySelectorAll('.tag-badge');
    badges.forEach(function(b){ b.remove(); });
}

function addTagProgrammatic(wrapId, inputId, fieldName, name) {
    var wrap = document.getElementById(wrapId);
    var input = document.getElementById(inputId);
    // Check duplicate
    var existing = wrap.querySelectorAll('input[type=hidden]');
    for (var i = 0; i < existing.length; i++) {
        if (existing[i].value.toLowerCase() === name.toLowerCase()) return;
    }
    var span = document.createElement('span');
    span.className = 'tag-badge';
    span.innerHTML = name + '<input type="hidden" name="'+fieldName+'" value="'+name.replace(/"/g,'&quot;')+'">' +
        '<button type="button" class="tag-remove" onclick="this.parentElement.remove()">&times;</button>';
    wrap.insertBefore(span, input);
}

function fillFormFromData(d) {
    // Name + Slug
    if (d.name) {
        document.getElementById('mangaName').value = d.name;
        generateSlug();
    }
    if (d.slug) {
        document.getElementById('mangaSlug').value = d.slug;
        document.getElementById('mangaSlug').dataset.manual = '1';
    }
    if (d.otherNames) {
        var otherNamesEl = document.querySelector('input[name="otherNames"]');
        if (otherNamesEl) otherNamesEl.value = d.otherNames;
    }
    if (d.summary) {
        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.summaryEditor) {
            CKEDITOR.instances.summaryEditor.setData(d.summary);
        } else {
            document.getElementById('summaryEditor').value = d.summary;
        }
    }
    // Authors
    if (d.authors && d.authors.length) {
        clearTagInput('authorsWrap', 'authorInput');
        d.authors.forEach(function(name){ addTagProgrammatic('authorsWrap', 'authorInput', 'authors[]', name); });
    }
    // Artists
    if (d.artists && d.artists.length) {
        clearTagInput('artistsWrap', 'artistInput');
        d.artists.forEach(function(name){ addTagProgrammatic('artistsWrap', 'artistInput', 'artists[]', name); });
    }
    // Genres → categories + tags
    if (d.genres && d.genres.length) {
        matchGenresToCategories(d.genres);
        clearTagInput('tagsWrap', 'tagInput');
        d.genres.forEach(function(name){ addTagProgrammatic('tagsWrap', 'tagInput', 'tags[]', name); });
    }
    // Type
    if (d.type) {
        var typeSelect = document.querySelector('select[name="type_id"]');
        if (typeSelect) {
            var typeLower = d.type.toLowerCase().trim();
            for (var i = 0; i < typeSelect.options.length; i++) {
                if (typeSelect.options[i].text.toLowerCase().trim() === typeLower) {
                    typeSelect.selectedIndex = i; break;
                }
            }
        }
    }
    // Status
    if (d.status) {
        var statusSelect = document.querySelector('select[name="status_id"]');
        if (statusSelect) {
            var statusLower = d.status.toLowerCase().trim();
            for (var i = 0; i < statusSelect.options.length; i++) {
                if (statusSelect.options[i].text.toLowerCase().trim() === statusLower) {
                    statusSelect.selectedIndex = i; break;
                }
            }
        }
    }
    // Cover
    if (d.cover) {
        document.getElementById('coverUrl').value = d.cover;
        showPreview(d.cover);
        if (!isEdit) {
            document.getElementById('coverUrlHidden').value = d.cover;
            status.innerHTML = '<span class="text-warning"><i class="bi bi-info-circle"></i> Cover will be fetched on save</span>';
        }
    }
    // Caution: 18+/Adult/Mature/Smut
    if (d.genres) {
        var adultGenres = d.genres.map(function(g){ return g.toLowerCase(); });
        if (adultGenres.indexOf('18+') >= 0 || adultGenres.indexOf('adult') >= 0 || adultGenres.indexOf('mature') >= 0 || adultGenres.indexOf('smut') >= 0) {
            var cautionCheck = document.querySelector('input[name="caution"][type="checkbox"]');
            if (cautionCheck) cautionCheck.checked = true;
        }
    }
    // from_manga18fx - set source URL
    if (d.source_url) {
        document.getElementById('manga18fxUrl').value = d.source_url;
    }
}

function matchGenresToCategories(genres) {
    // Uncheck all first
    var checks = document.querySelectorAll('input[name="category_ids[]"]');
    checks.forEach(function(c){ c.checked = false; });
    // Match genres to category labels
    genres.forEach(function(genre){
        var genreLower = genre.toLowerCase().trim();
        checks.forEach(function(c){
            var label = c.parentElement.querySelector('.form-check-label');
            if (label && label.textContent.toLowerCase().trim() === genreLower) {
                c.checked = true;
            }
        });
    });
}
</script>
<?= $this->endSection() ?>
