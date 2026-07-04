<?php
/**
 * Reusable alert component.
 */

$type = $type ?? 'info';
$message = $message ?? '';
$icon = $icon ?? 'fa-circle-info';
?>
<div class="alert alert-<?php echo sms_e($type); ?> d-flex align-items-center gap-2" role="alert">
    <i class="fa-solid <?php echo sms_e($icon); ?>"></i>
    <span><?php echo sms_e($message); ?></span>
</div>
