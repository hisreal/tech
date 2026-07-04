<?php
/**
 * Reusable responsive table component.
 */

$headers = $headers ?? [];
$rows = $rows ?? [];
$empty = $empty ?? 'No records found.';
?>
<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <?php foreach ($headers as $header): ?>
                    <th><?php echo sms_e($header); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows): ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?php echo sms_e($cell); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="<?php echo max(1, count($headers)); ?>" class="text-center text-muted py-4"><?php echo sms_e($empty); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
