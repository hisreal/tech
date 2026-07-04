<?php
/**
 * Reusable search/filter form shell. Pass pre-rendered $fields HTML.
 */

$fields = $fields ?? '';
$action = $action ?? '';
$method = $method ?? 'get';
?>
<form action="<?php echo sms_e($action); ?>" method="<?php echo sms_e($method); ?>" class="row g-3 align-items-end">
    <?php echo $fields; ?>
    <div class="col-md-2"><button type="submit" class="btn btn-success w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
    <div class="col-md-2"><button type="reset" class="btn btn-outline-secondary w-100">Reset</button></div>
</form>
