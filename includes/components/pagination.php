<?php
/**
 * Lightweight pagination placeholder component.
 */

$current = (int) ($current ?? 1);
$total = max(1, (int) ($total ?? 1));
?>
<div class="d-flex gap-2 flex-wrap">
    <?php for ($page = 1; $page <= $total; $page++): ?>
        <button type="button" class="page-btn <?php echo $page === $current ? 'active' : ''; ?>" data-page="<?php echo $page; ?>"><?php echo $page; ?></button>
    <?php endfor; ?>
</div>
