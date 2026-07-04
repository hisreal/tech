<?php
/**
 * Reusable compact profile card.
 */

$name = $name ?? '';
$role = $role ?? '';
$image = $image ?? '';
$meta = $meta ?? [];
?>
<div class="profile-card d-flex align-items-center gap-3">
    <?php if ($image): ?>
        <img src="<?php echo sms_e($image); ?>" alt="<?php echo sms_e($name); ?>" class="rounded-circle" style="width:72px;height:72px;object-fit:cover;">
    <?php endif; ?>
    <div>
        <h5 class="mb-1"><?php echo sms_e($name); ?></h5>
        <p class="text-muted mb-2"><?php echo sms_e($role); ?></p>
        <?php foreach ($meta as $label => $value): ?>
            <span class="badge bg-light text-dark me-1"><?php echo sms_e($label); ?>: <?php echo sms_e($value); ?></span>
        <?php endforeach; ?>
    </div>
</div>
