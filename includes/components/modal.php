<?php
/**
 * Reusable Bootstrap modal.
 */

$id = $id ?? 'sharedModal';
$title = $title ?? 'Details';
$body = $body ?? '';
$footer = $footer ?? '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
?>
<div class="modal fade" id="<?php echo sms_e($id); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo sms_e($title); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"><?php echo $body; ?></div>
            <div class="modal-footer"><?php echo $footer; ?></div>
        </div>
    </div>
</div>
