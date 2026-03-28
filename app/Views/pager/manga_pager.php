<?php
/**
 * Frontend pagination matching CI3 theme CSS
 *
 * Usage: Pass $current_page, $total_pages, $base_url to view
 * URL pattern: {base_url}{page_number}  e.g. /latest-release/2
 *
 * Or use CI4 Pager: $pager variable will be available
 */

if (isset($pager)) {
    // CI4 Pager mode
    $pager->setSurroundCount(3);
?>
<ul class="pagination">
    <?php if ($pager->hasPrevious()): ?>
        <li><a href="<?= $pager->getFirst() ?>">&laquo;</a></li>
        <li><a href="<?= $pager->getPrevious() ?>">&lsaquo;</a></li>
    <?php endif; ?>
    <?php foreach ($pager->links() as $link): ?>
        <li class="<?= $link['active'] ? 'active' : '' ?>"><a href="<?= $link['uri'] ?>"><?= $link['title'] ?></a></li>
    <?php endforeach; ?>
    <?php if ($pager->hasNext()): ?>
        <li><a href="<?= $pager->getNext() ?>">&rsaquo;</a></li>
        <li><a href="<?= $pager->getLast() ?>">&raquo;</a></li>
    <?php endif; ?>
</ul>
<?php } ?>
