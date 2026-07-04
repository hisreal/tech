<?php
/**
 * Reusable breadcrumb component.
 * Usage: sms_render_component('breadcrumbs', ['items' => [['label' => 'Dashboard', 'url' => 'dashboard.php'], ...]]);
 */

$items = $items ?? [];
?>
<div class="breadcrumb-line">
    <?php foreach ($items as $index => $item): ?>
        <?php if (!empty($item['url']) && $index < count($items) - 1): ?>
            <a href="<?php echo sms_e($item['url']); ?>"><?php echo sms_e($item['label']); ?></a>
        <?php else: ?>
            <span><?php echo sms_e($item['label']); ?></span>
        <?php endif; ?>
        <?php if ($index < count($items) - 1): ?>
            <i class="fa-solid fa-chevron-right mx-2"></i>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
