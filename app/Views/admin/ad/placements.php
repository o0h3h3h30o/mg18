<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<?php
// Helper: find current ad_id for a given placement_id + position
function getPlacementAdId($placements, $placementId, $position) {
    foreach ($placements as $p) {
        if ($p->placement_id == $placementId && $p->placement == $position) {
            return $p->ad_id;
        }
    }
    return 0;
}

// Helper: render a select dropdown for an ad slot
function renderAdSelect($ads, $placements, $placementId, $position) {
    $currentAdId = getPlacementAdId($placements, $placementId, $position);
    $html = '<select class="form-select form-select-sm ad-slot-select" name="slot[' . $placementId . '][' . $position . ']" data-placement-id="' . $placementId . '" data-position="' . esc($position) . '">';
    $html .= '<option value="0">NO AD</option>';
    foreach ($ads as $ad) {
        $selected = ($currentAdId == $ad->id) ? ' selected' : '';
        $html .= '<option value="' . $ad->id . '"' . $selected . '>' . esc($ad->bloc_id) . ' (#' . $ad->id . ')</option>';
    }
    $html .= '</select>';
    return $html;
}
?>

<style>
    .placement-tabs .nav-link {
        color: var(--kt-text-muted);
        font-weight: 500;
        font-size: 13px;
        border: 1px solid transparent;
        border-radius: 6px 6px 0 0;
        padding: 10px 20px;
    }
    .placement-tabs .nav-link:hover {
        color: var(--kt-text-dark);
        border-color: var(--kt-border) var(--kt-border) transparent;
    }
    .placement-tabs .nav-link.active {
        color: var(--kt-text-dark);
        background: var(--kt-card-bg);
        border-color: var(--kt-border) var(--kt-border) var(--kt-card-bg);
    }

    .wireframe-box {
        border: 1px dashed #3d3d52;
        background: rgba(255,255,255,.03);
        border-radius: 6px;
        padding: 10px;
        text-align: center;
        min-height: 60px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .wireframe-box .slot-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--kt-text-muted);
        margin-bottom: 2px;
    }
    .wireframe-box select {
        max-width: 200px;
    }

    .wireframe-content {
        border: 2px solid var(--kt-border);
        border-radius: 8px;
        background: rgba(99,102,241,.04);
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--kt-text-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .wireframe-sidebar {
        border: 2px solid var(--kt-border);
        border-radius: 8px;
        background: rgba(245,158,11,.04);
        min-height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--kt-text-muted);
        font-size: 13px;
        font-weight: 600;
        gap: 10px;
        padding: 12px;
    }

    .wireframe-section {
        margin-bottom: 16px;
    }
    .wireframe-section:last-child {
        margin-bottom: 0;
    }

    .section-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--kt-primary);
        margin-bottom: 8px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Ad Placements</h4>
    <div>
        <a href="/admin/ads" class="btn btn-outline-secondary btn-sm">Back to Ads</a>
    </div>
</div>

<!-- Alert container for AJAX responses -->
<div id="save-alert" class="d-none"></div>

<!-- Tab Navigation -->
<ul class="nav nav-tabs placement-tabs mb-0" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-reader" type="button" role="tab">
            <i class="bi bi-book me-1"></i> Reader Page
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-home" type="button" role="tab">
            <i class="bi bi-house me-1"></i> HomePage
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-info" type="button" role="tab">
            <i class="bi bi-info-circle me-1"></i> Manhwa Info Page
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- ==================== TAB 1: Reader Page (placement_id=3) ==================== -->
    <div class="tab-pane fade show active" id="tab-reader" role="tabpanel">
        <div class="card" style="border-top-left-radius:0;">
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:12px;">Configure ad slots for the chapter reader page. The reader has a full-width layout with ads around the main content area.</p>

                <!-- TOP area -->
                <div class="wireframe-section">
                    <div class="section-label">Top Area</div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">TOP_SQRE_1</span>
                                <?= renderAdSelect($ads, $placements, 3, 'TOP_SQRE_1') ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">TOP_SQRE_2</span>
                                <?= renderAdSelect($ads, $placements, 3, 'TOP_SQRE_2') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main content area with LEFT and RIGHT ads -->
                <div class="wireframe-section">
                    <div class="section-label">Content Area</div>
                    <div class="row g-2">
                        <!-- Left side ads -->
                        <div class="col-md-2">
                            <div class="wireframe-box mb-2">
                                <span class="slot-label">LEFT_WIDE_1</span>
                                <?= renderAdSelect($ads, $placements, 3, 'LEFT_WIDE_1') ?>
                            </div>
                            <div class="wireframe-box">
                                <span class="slot-label">LEFT_WIDE_2</span>
                                <?= renderAdSelect($ads, $placements, 3, 'LEFT_WIDE_2') ?>
                            </div>
                        </div>
                        <!-- Main content -->
                        <div class="col-md-8">
                            <div class="wireframe-content" style="min-height:200px;">
                                <div>
                                    <i class="bi bi-image" style="font-size:24px;"></i>
                                    <div style="margin-top:4px;">Chapter Reader Content</div>
                                </div>
                            </div>
                        </div>
                        <!-- Right side ads -->
                        <div class="col-md-2">
                            <div class="wireframe-box mb-2">
                                <span class="slot-label">RIGHT_WIDE_1</span>
                                <?= renderAdSelect($ads, $placements, 3, 'RIGHT_WIDE_1') ?>
                            </div>
                            <div class="wireframe-box">
                                <span class="slot-label">RIGHT_WIDE_2</span>
                                <?= renderAdSelect($ads, $placements, 3, 'RIGHT_WIDE_2') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM area -->
                <div class="wireframe-section">
                    <div class="section-label">Bottom Area</div>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="wireframe-box">
                                <span class="slot-label">BOTTOM_LARGE</span>
                                <?= renderAdSelect($ads, $placements, 3, 'BOTTOM_LARGE') ?>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">BOTTOM_SQRE_1</span>
                                <?= renderAdSelect($ads, $placements, 3, 'BOTTOM_SQRE_1') ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">BOTTOM_SQRE_2</span>
                                <?= renderAdSelect($ads, $placements, 3, 'BOTTOM_SQRE_2') ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ==================== TAB 2: HomePage (placement_id=1) ==================== -->
    <div class="tab-pane fade" id="tab-home" role="tabpanel">
        <div class="card" style="border-top-left-radius:0;">
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:12px;">Configure ad slots for the home page. Layout has a main content area (8 cols) and a sidebar (4 cols).</p>

                <!-- TOP area -->
                <div class="wireframe-section">
                    <div class="section-label">Top Area (Above Content)</div>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="wireframe-box">
                                <span class="slot-label">TOP_LARGE</span>
                                <?= renderAdSelect($ads, $placements, 1, 'TOP_LARGE') ?>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">TOP_SQRE_1</span>
                                <?= renderAdSelect($ads, $placements, 1, 'TOP_SQRE_1') ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">TOP_SQRE_2</span>
                                <?= renderAdSelect($ads, $placements, 1, 'TOP_SQRE_2') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main content + Sidebar -->
                <div class="wireframe-section">
                    <div class="section-label">Main Layout</div>
                    <div class="row g-3">
                        <!-- Main content (8 cols) -->
                        <div class="col-md-8">
                            <div class="wireframe-content" style="min-height:280px;">
                                <div>
                                    <i class="bi bi-grid-3x3-gap" style="font-size:24px;"></i>
                                    <div style="margin-top:4px;">Manga List / Main Content</div>
                                </div>
                            </div>
                        </div>
                        <!-- Sidebar (4 cols) -->
                        <div class="col-md-4">
                            <div class="wireframe-sidebar" style="min-height:280px;">
                                <span style="font-size:11px;color:var(--kt-warning);font-weight:600;">SIDEBAR</span>
                                <div class="wireframe-box w-100">
                                    <span class="slot-label">RIGHT_WIDE_1</span>
                                    <?= renderAdSelect($ads, $placements, 1, 'RIGHT_WIDE_1') ?>
                                </div>
                                <div class="wireframe-box w-100">
                                    <span class="slot-label">RIGHT_SQRE_1</span>
                                    <?= renderAdSelect($ads, $placements, 1, 'RIGHT_SQRE_1') ?>
                                </div>
                                <div style="flex:1;display:flex;align-items:center;color:var(--kt-text-muted);font-size:11px;">
                                    --- sidebar content ---
                                </div>
                                <div class="wireframe-box w-100">
                                    <span class="slot-label">RIGHT_SQRE_2</span>
                                    <?= renderAdSelect($ads, $placements, 1, 'RIGHT_SQRE_2') ?>
                                </div>
                                <div class="wireframe-box w-100">
                                    <span class="slot-label">RIGHT_WIDE_2</span>
                                    <?= renderAdSelect($ads, $placements, 1, 'RIGHT_WIDE_2') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM area -->
                <div class="wireframe-section">
                    <div class="section-label">Bottom Area (Below Manga List)</div>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="wireframe-box">
                                <span class="slot-label">BOTTOM_LARGE</span>
                                <?= renderAdSelect($ads, $placements, 1, 'BOTTOM_LARGE') ?>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">BOTTOM_SQRE_1</span>
                                <?= renderAdSelect($ads, $placements, 1, 'BOTTOM_SQRE_1') ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">BOTTOM_SQRE_2</span>
                                <?= renderAdSelect($ads, $placements, 1, 'BOTTOM_SQRE_2') ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ==================== TAB 3: Manhwa Info Page (placement_id=2) ==================== -->
    <div class="tab-pane fade" id="tab-info" role="tabpanel">
        <div class="card" style="border-top-left-radius:0;">
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:12px;">Configure ad slots for the manhwa detail/info page. This page has no sidebar ads.</p>

                <!-- TOP area -->
                <div class="wireframe-section">
                    <div class="section-label">Top Area</div>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="wireframe-box">
                                <span class="slot-label">TOP_LARGE</span>
                                <?= renderAdSelect($ads, $placements, 2, 'TOP_LARGE') ?>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">TOP_SQRE_1</span>
                                <?= renderAdSelect($ads, $placements, 2, 'TOP_SQRE_1') ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">TOP_SQRE_2</span>
                                <?= renderAdSelect($ads, $placements, 2, 'TOP_SQRE_2') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main content -->
                <div class="wireframe-section">
                    <div class="section-label">Content Area</div>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="wireframe-content" style="min-height:200px;">
                                <div>
                                    <i class="bi bi-file-earmark-text" style="font-size:24px;"></i>
                                    <div style="margin-top:4px;">Manhwa Info / Detail Content</div>
                                    <div style="font-size:11px;color:var(--kt-text-muted);margin-top:2px;">Cover, description, chapters list, etc.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM area -->
                <div class="wireframe-section">
                    <div class="section-label">Bottom Area</div>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="wireframe-box">
                                <span class="slot-label">BOTTOM_LARGE</span>
                                <?= renderAdSelect($ads, $placements, 2, 'BOTTOM_LARGE') ?>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">BOTTOM_SQRE_1</span>
                                <?= renderAdSelect($ads, $placements, 2, 'BOTTOM_SQRE_1') ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="wireframe-box">
                                <span class="slot-label">BOTTOM_SQRE_2</span>
                                <?= renderAdSelect($ads, $placements, 2, 'BOTTOM_SQRE_2') ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- Save Button -->
<div class="d-flex justify-content-end mt-3 mb-4">
    <button type="button" class="btn btn-danger px-4" id="btn-save-all" onclick="saveAllPlacements()">
        <i class="bi bi-check2-all me-1"></i> Save All Placements
    </button>
</div>

<script>
function saveAllPlacements() {
    const btn = document.getElementById('btn-save-all');
    const alertBox = document.getElementById('save-alert');
    const selects = document.querySelectorAll('.ad-slot-select');
    const placements = [];

    selects.forEach(function(sel) {
        const adId = parseInt(sel.value);
        if (adId > 0) {
            placements.push({
                placement_id: parseInt(sel.dataset.placementId),
                placement: sel.dataset.position,
                ad_id: adId
            });
        }
    });

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    fetch('/admin/placements/save-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
        },
        body: JSON.stringify({ placements: placements })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        alertBox.className = 'alert alert-success alert-dismissible fade show';
        alertBox.innerHTML = '<i class="bi bi-check-circle me-1"></i> ' + (data.message || 'All placements saved successfully!') +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-all me-1"></i> Save All Placements';
    })
    .catch(function(err) {
        alertBox.className = 'alert alert-danger alert-dismissible fade show';
        alertBox.innerHTML = '<i class="bi bi-x-circle me-1"></i> Error saving placements: ' + err.message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-all me-1"></i> Save All Placements';
    });
}
</script>

<?= $this->endSection() ?>
