<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Core\Database;
use App\Models\SettingsModel;
use App\Services\FinanceService;

$financeService = new FinanceService();
$db = Database::getInstance();

$receiptId = (int) ($_GET['id'] ?? 0);
$paymentId = (int) ($_GET['payment_id'] ?? 0);

$receipt = $receiptId ? $financeService->findReceipt($receiptId) : ($paymentId ? $financeService->findReceiptByPaymentId($paymentId) : null);

if ($receipt === null) {
    http_response_code(404);
    echo 'Receipt not found.';
    exit;
}

$settings = (new SettingsModel())->all();
$schoolName = $settings['school.name']['value'] ?? 'School Management System';
$schoolAddress = $settings['school.address']['value'] ?? '';
$schoolPhone = $settings['school.phone']['value'] ?? '';
$schoolEmail = $settings['school.email']['value'] ?? '';
$logoPath = $settings['school.logo']['value'] ?? '';
$logoUrl = $logoPath ? '../' . ltrim((string) $logoPath, '/') : '';
$accountant = sms_current_user();
$staffRow = $accountant ? $db->fetchOne('SELECT first_name, last_name FROM staff WHERE user_id = :id', ['id' => (int) $accountant['id']]) : null;
$accountantName = $staffRow ? trim($staffRow['first_name'] . ' ' . $staffRow['last_name']) : ($accountant['username'] ?? 'Accountant');

function rpMoney($amount) { return '₦' . number_format((float) $amount, 2); }
function rpValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$fullName = trim($receipt['first_name'] . ' ' . $receipt['last_name']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Receipt <?php echo rpValue($receipt['receipt_no']); ?></title>
<style>
@page { size: A4; margin: 16mm; }
body { font-family: Arial, sans-serif; color: #10201d; }
.head { text-align: center; border-bottom: 3px solid #0f766e; padding-bottom: 10px; margin-bottom: 18px; }
.head img { width: 76px; height: 76px; object-fit: contain; }
table { width: 100%; border-collapse: collapse; }
td { padding: 8px; border-bottom: 1px solid #ddd; }
.total { font-size: 20px; font-weight: 800; color: #0f766e; }
.status-cancelled { color: #dc2626; font-weight: 900; text-transform: uppercase; }
.stamp-box { border: 2px dashed #94a3b8; border-radius: 14px; min-height: 68px; display: flex; align-items: center; justify-content: center; color: #64748b; font-weight: 800; margin-top: 30px; }
.row { display: flex; gap: 20px; margin-top: 20px; }
.col { flex: 1; }
.thanks { text-align: center; font-weight: 800; margin-top: 16px; }
.print-bar { text-align: center; margin-bottom: 16px; }
@media print { .print-bar { display: none; } }
</style>
</head>
<body>
<div class="print-bar"><button onclick="window.print()">Print</button></div>
<div class="head">
    <?php if ($logoUrl): ?><img src="<?php echo rpValue($logoUrl); ?>" alt="Logo"><?php endif; ?>
    <h2 style="margin:8px 0 2px;"><?php echo rpValue($schoolName); ?></h2>
    <p style="margin:0;"><?php echo rpValue($schoolAddress); ?></p>
    <p style="margin:0;"><?php echo rpValue($schoolPhone); ?><?php echo $schoolEmail ? ' | ' . rpValue($schoolEmail) : ''; ?></p>
    <p style="margin:8px 0 0;font-weight:900;">Official School Fee Receipt<?php if ($receipt['status'] !== 'paid'): ?> <span class="status-cancelled">(<?php echo rpValue(ucfirst($receipt['status'])); ?>)</span><?php endif; ?></p>
</div>
<div class="row">
    <div class="col">
        <h5>Receipt Information</h5>
        <p><strong>Receipt No:</strong> <?php echo rpValue($receipt['receipt_no']); ?></p>
        <p><strong>Payment Date:</strong> <?php echo rpValue(substr((string) $receipt['issued_at'], 0, 16)); ?></p>
        <p><strong>Transaction No:</strong> <?php echo rpValue($receipt['transaction_no']); ?></p>
    </div>
    <div class="col">
        <h5>Student Information</h5>
        <p><strong><?php echo rpValue($fullName); ?></strong></p>
        <p><?php echo rpValue($receipt['registration_no']); ?></p>
        <p><?php echo rpValue($receipt['class_name'] ?? ''); ?></p>
    </div>
</div>
<h5 style="margin-top:20px;">Payment Details</h5>
<table>
<thead><tr><th style="text-align:left;padding:8px;background:#0f766e;color:#fff;">Fee Item</th><th style="text-align:left;padding:8px;background:#0f766e;color:#fff;">Amount</th></tr></thead>
<tbody>
<?php foreach ($receipt['items'] as $item): ?>
    <tr><td><?php echo rpValue($item['item_name']); ?></td><td><?php echo rpMoney($item['amount']); ?></td></tr>
<?php endforeach; ?>
<?php if (!$receipt['items']): ?><tr><td colspan="2"><?php echo rpValue($receipt['payment_type']); ?></td></tr><?php endif; ?>
</tbody>
</table>
<div class="row">
    <div class="col"><strong>Total Amount Paid</strong><div class="total"><?php echo rpMoney($receipt['amount']); ?></div></div>
    <div class="col"><strong>Payment Method</strong><p><?php echo rpValue(ucfirst(str_replace('_', ' ', $receipt['payment_method']))); ?></p><?php if ($receipt['transaction_reference']): ?><p>Ref: <?php echo rpValue($receipt['transaction_reference']); ?></p><?php endif; ?></div>
    <div class="col"><strong>Received By</strong><p><?php echo rpValue($accountantName); ?></p></div>
</div>
<div class="row">
    <div class="col"><p style="margin-bottom:50px;">Authorized Signature</p><hr></div>
    <div class="col"><div class="stamp-box">School Stamp</div></div>
</div>
<p class="thanks">Thank you for your payment.</p>
</body>
</html>
