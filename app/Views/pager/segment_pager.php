<?php
/**
 * Segment-based pagination for URLs like /latest-release/2, /bookmarks/3
 * Pass: $current_page, $total_pages, $base_url
 */
if (!isset($total_pages) || $total_pages <= 1) return;

$range = 1;
$start = max(1, $current_page - $range);
$end = min($total_pages, $current_page + $range);
?>
<ul class="pagination">
    <?php if ($current_page > 1): ?>
        <li><a href="<?= $base_url ?>1">&laquo;</a></li>
        <li><a href="<?= $base_url . ($current_page - 1) ?>">&lsaquo;</a></li>
    <?php endif; ?>
    <?php if ($start > 1): ?><li><a href="<?= $base_url ?>1">1</a></li><li class="disabled"><span>...</span></li><?php endif; ?>
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <li class="<?= $i == $current_page ? 'active' : '' ?>"><a href="<?= $base_url . $i ?>"><?= $i ?></a></li>
    <?php endfor; ?>
    <?php if ($end < $total_pages): ?><li class="disabled"><span>...</span></li><li><a href="<?= $base_url . $total_pages ?>"><?= $total_pages ?></a></li><?php endif; ?>
    <?php if ($current_page < $total_pages): ?>
        <li><a href="<?= $base_url . ($current_page + 1) ?>">&rsaquo;</a></li>
        <li><a href="<?= $base_url . $total_pages ?>">&raquo;</a></li>
    <?php endif; ?>
</ul>
