<?php
/**
 * Reusable page header component.
 */

$title = $title ?? '';
$description = $description ?? '';
$kicker = $kicker ?? '';
$icon = $icon ?? 'fa-circle-info';
$breadcrumbs = $breadcrumbs ?? [];
?>
<section class="portal-hero">
    <?php if ($breadcrumbs): ?>
        <?php $items = $breadcrumbs; require __DIR__ . '/breadcrumbs.php'; ?>
    <?php endif; ?>
    <?php if ($kicker): ?>
        <span class="portal-kicker"><i class="fa-solid <?php echo sms_e($icon); ?>"></i><?php echo sms_e($kicker); ?></span>
    <?php endif; ?>
    <h3 class="mt-3 mb-2"><?php echo sms_e($title); ?></h3>
    <?php if ($description): ?>
        <p class="text-muted mb-0"><?php echo sms_e($description); ?></p>
    <?php endif; ?>
</section>



