<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<h4 class="mb-4">Dashboard</h4>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card blue">
            <div class="card-body">
                <div class="text-muted small">Total Manga</div>
                <div class="fs-3 fw-bold"><?= number_format($totalManga) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card green">
            <div class="card-body">
                <div class="text-muted small">Total Chapters</div>
                <div class="fs-3 fw-bold"><?= number_format($totalChapter) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card orange">
            <div class="card-body">
                <div class="text-muted small">Total Users</div>
                <div class="fs-3 fw-bold"><?= number_format($totalUser) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card red">
            <div class="card-body">
                <div class="text-muted small">Total Comments</div>
                <div class="fs-3 fw-bold"><?= number_format($totalComment) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">Latest Manga</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>ID</th><th>Name</th><th>Views</th></tr></thead>
                    <tbody>
                    <?php foreach ($latestManga as $m): ?>
                        <tr>
                            <td><?= $m->id ?></td>
                            <td><a href="/admin/manga/edit/<?= $m->id ?>"><?= esc($m->name) ?></a></td>
                            <td><?= number_format($m->views) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Latest Comments</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>User</th><th>Comment</th></tr></thead>
                    <tbody>
                    <?php foreach ($latestComments as $c): ?>
                        <tr>
                            <td class="text-nowrap"><?= esc($c->username ?? 'N/A') ?></td>
                            <td><?= esc(mb_substr($c->comment, 0, 60)) ?><?= mb_strlen($c->comment) > 60 ? '...' : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-md-3"><div class="card card-body text-center"><div class="text-muted small">Categories</div><div class="fs-4 fw-bold"><?= $totalCategory ?></div></div></div>
    <div class="col-md-3"><div class="card card-body text-center"><div class="text-muted small">Tags</div><div class="fs-4 fw-bold"><?= $totalTag ?></div></div></div>
    <div class="col-md-3"><div class="card card-body text-center"><div class="text-muted small">Authors</div><div class="fs-4 fw-bold"><?= $totalAuthor ?></div></div></div>
</div>

<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">Latest Chapters</div>
            <div class="card-body p-0">
                <div class="table-responsive-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>ID</th><th>Manga</th><th>Chapter</th><th>Created</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($latestChapters as $ch): ?>
                        <tr>
                            <td><?= $ch->id ?></td>
                            <td><a href="/admin/manga/edit/<?= $ch->manga_id ?>"><?= esc($ch->manga_name ?? '#' . $ch->manga_id) ?></a></td>
                            <td><?= esc($ch->name) ?></td>
                            <td class="text-nowrap"><?= $ch->created_at ? date('d/m/Y H:i', strtotime($ch->created_at)) : '-' ?></td>
                            <td>
                                <a href="/admin/pages/<?= $ch->id ?>" class="btn btn-outline-primary btn-sm" title="View Pages"><i class="bi bi-images"></i></a>
                                <a href="/admin/chapters/edit/<?= $ch->id ?>" class="btn btn-outline-info btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
