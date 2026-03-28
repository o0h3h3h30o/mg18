<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-gear me-2"></i>Settings</h4>
</div>

<form action="/admin/settings/save" method="post">
    <?= csrf_field() ?>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-general">General</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-seo">SEO</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-comment">Comment</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-reader">Reader</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-advanced">Advanced</a></li>
    </ul>

    <div class="tab-content">

        <!-- General -->
        <div class="tab-pane fade show active" id="tab-general">
            <div class="card">
                <div class="card-header"><strong>Site Information</strong></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Site Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="site__name" value="<?= esc($settings['site.name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Slogan</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="site__slogan" value="<?= esc($settings['site.slogan'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Site Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="site__description" rows="4"><?= esc($settings['site.description'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Language</label>
                        <div class="col-sm-9">
                            <select class="form-select" name="site__lang">
                                <option value="en" <?= ($settings['site.lang'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                                <option value="vi" <?= ($settings['site.lang'] ?? '') === 'vi' ? 'selected' : '' ?>>Vietnamese</option>
                                <option value="ja" <?= ($settings['site.lang'] ?? '') === 'ja' ? 'selected' : '' ?>>Japanese</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Orientation</label>
                        <div class="col-sm-9">
                            <select class="form-select" name="site__orientation">
                                <option value="ltr" <?= ($settings['site.orientation'] ?? '') === 'ltr' ? 'selected' : '' ?>>LTR (Left to Right)</option>
                                <option value="rtl" <?= ($settings['site.orientation'] ?? '') === 'rtl' ? 'selected' : '' ?>>RTL (Right to Left)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><strong>Pagination</strong></div>
                <div class="card-body">
                    <?php $pagination = json_decode($settings['site.pagination'] ?? '{}', true); ?>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Homepage</label>
                        <div class="col-sm-3">
                            <input type="number" class="form-control" name="pagination_homepage" value="<?= esc($pagination['homepage'] ?? 120) ?>" disabled>
                            <small class="text-muted">Currently hardcoded (80)</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Manga List</label>
                        <div class="col-sm-3">
                            <input type="number" class="form-control" name="pagination_mangalist" value="<?= esc($pagination['mangalist'] ?? 20) ?>" disabled>
                            <small class="text-muted">Currently hardcoded (20)</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Latest Release</label>
                        <div class="col-sm-3">
                            <input type="number" class="form-control" name="pagination_latest" value="<?= esc($pagination['latest_release'] ?? 40) ?>" disabled>
                            <small class="text-muted">Currently hardcoded (32)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO -->
        <div class="tab-pane fade" id="tab-seo">
            <div class="card">
                <div class="card-header"><strong>SEO Settings</strong></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Site Title</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="seo__title" value="<?= esc($settings['seo.title'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Meta Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="seo__description" rows="3"><?= esc($settings['seo.description'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Meta Keywords</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="seo__keywords" rows="3"><?= esc($settings['seo.keywords'] ?? '') ?></textarea>
                            <small class="text-muted">Comma separated</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><strong>Google</strong></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Analytics ID</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="seo__google__analytics" value="<?= esc($settings['seo.google.analytics'] ?? '') ?>" placeholder="UA-XXXXX or G-XXXXX">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Webmaster Verification</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="seo__google__webmaster" value="<?= esc($settings['seo.google.webmaster'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comment -->
        <div class="tab-pane fade" id="tab-comment">
            <div class="card">
                <div class="card-header"><strong>Comment Settings</strong></div>
                <div class="card-body">
                    <?php $comment = json_decode($settings['site.comment'] ?? '{}', true); ?>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Built-in Comments</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" <?= ($comment['builtin'] ?? '1') === '1' ? 'checked' : '' ?> disabled>
                                <label class="form-check-label text-muted">Always enabled (internal comment system)</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Show on Manga Page</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" checked disabled>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Show on Reader Page</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" checked disabled>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3">Statistics</h6>
                    <?php
                        $totalComments = db_connect()->table('comments')->countAllResults();
                        $totalMangaCmt = db_connect()->table('comments')->where('post_type', 'manga')->where('parent_comment IS NULL')->countAllResults();
                        $totalChapterCmt = db_connect()->table('comments')->where('post_type', 'chapter')->where('parent_comment IS NULL')->countAllResults();
                        $totalReplies = db_connect()->query("SELECT COUNT(*) as cnt FROM comments WHERE parent_comment IS NOT NULL")->getRow()->cnt;
                    ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-light text-center p-3">
                                <div class="fs-4 fw-bold text-primary"><?= number_format($totalComments) ?></div>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light text-center p-3">
                                <div class="fs-4 fw-bold text-success"><?= number_format($totalMangaCmt) ?></div>
                                <small class="text-muted">Manga</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light text-center p-3">
                                <div class="fs-4 fw-bold text-info"><?= number_format($totalChapterCmt) ?></div>
                                <small class="text-muted">Chapter</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light text-center p-3">
                                <div class="fs-4 fw-bold text-secondary"><?= number_format($totalReplies) ?></div>
                                <small class="text-muted">Replies</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reader -->
        <div class="tab-pane fade" id="tab-reader">
            <div class="card">
                <div class="card-header"><strong>Reader Settings</strong></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Reader Type</label>
                        <div class="col-sm-9">
                            <select class="form-select" name="reader__type">
                                <option value="all" <?= ($settings['reader.type'] ?? '') === 'all' ? 'selected' : '' ?>>All (Scroll)</option>
                                <option value="paged" <?= ($settings['reader.type'] ?? '') === 'paged' ? 'selected' : '' ?>>Paged</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Reader Mode</label>
                        <div class="col-sm-9">
                            <select class="form-select" name="reader__mode">
                                <option value="reload" <?= ($settings['reader.mode'] ?? '') === 'reload' ? 'selected' : '' ?>>Reload</option>
                                <option value="ajax" <?= ($settings['reader.mode'] ?? '') === 'ajax' ? 'selected' : '' ?>>AJAX</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Storage Type</label>
                        <div class="col-sm-9">
                            <select class="form-select" name="storage__type">
                                <option value="server" <?= ($settings['storage.type'] ?? '') === 'server' ? 'selected' : '' ?>>Server</option>
                                <option value="gdrive" <?= ($settings['storage.type'] ?? '') === 'gdrive' ? 'selected' : '' ?>>Google Drive</option>
                                <option value="s3" <?= ($settings['storage.type'] ?? '') === 's3' ? 'selected' : '' ?>>S3</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><strong>Manga Options</strong></div>
                <div class="card-body">
                    <?php $mangaOpts = json_decode($settings['manga.options'] ?? '{}', true); ?>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Allow Download Chapter</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" <?= ($mangaOpts['allow_download_chapter'] ?? '0') === '1' ? 'checked' : '' ?> disabled>
                                <label class="form-check-label text-muted">JSON field (read-only)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced -->
        <div class="tab-pane fade" id="tab-advanced">
            <div class="card">
                <div class="card-header"><strong>Captcha</strong></div>
                <div class="card-body">
                    <?php $captcha = json_decode($settings['site.captcha'] ?? '{}', true); ?>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">reCAPTCHA Site Key</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="<?= esc($captcha['site_key'] ?? '') ?>" disabled>
                            <small class="text-muted">JSON field (edit in DB directly)</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">reCAPTCHA Secret Key</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" value="<?= esc($captcha['secret_key'] ?? '') ?>" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><strong>Cache</strong></div>
                <div class="card-body">
                    <?php $cache = json_decode($settings['site.cache'] ?? '{}', true); ?>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Reader Cache</label>
                        <div class="col-sm-9">
                            <select class="form-select" disabled>
                                <option value="0" <?= ($cache['reader'] ?? '0') === '0' ? 'selected' : '' ?>>Disabled</option>
                                <option value="1" <?= ($cache['reader'] ?? '0') === '1' ? 'selected' : '' ?>>Enabled</option>
                            </select>
                            <small class="text-muted">JSON field (read-only)</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>All Options (Raw)</strong>
                    <span class="badge bg-secondary"><?= count($settings) ?> keys</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0" style="font-size:12px;">
                        <thead><tr><th width="200">Key</th><th>Value</th><th width="150">Updated</th></tr></thead>
                        <tbody>
                        <?php
                            $allOptions = db_connect()->table('options')->orderBy('key', 'ASC')->get()->getResult();
                            foreach ($allOptions as $opt):
                                $val = mb_strlen($opt->value) > 100 ? mb_substr($opt->value, 0, 100) . '...' : $opt->value;
                        ?>
                            <tr>
                                <td><code><?= esc($opt->key) ?></code></td>
                                <td class="text-muted"><?= esc($val) ?></td>
                                <td><small><?= $opt->updated_at ? date('d/m/Y H:i', strtotime($opt->updated_at)) : '-' ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Save Button -->
    <div class="mt-4 mb-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
    </div>
</form>

<?= $this->endSection() ?>
