<?php require_once('includes/header.php'); ?>

<?php
// Accountant dashboard placeholder data. Replace these arrays with finance database queries when backend integration is ready.
$accountant = [
	'profile_picture' => '../assets/img/avatar/avatar-19.jpg',
	'full_name' => 'John Ibrahim',
	'staff_id' => 'ACC001'
];

$todayRevenue = 450000;
$outstandingFees = 2350000;
$todayExpenses = 120000;
$netIncome = $todayRevenue - $todayExpenses;
$studentsPaid = 540;
$outstandingStudents = 132;
$monthlyRevenueTotal = 8450000;
$monthlyExpensesTotal = 2150000;
$footerStats = [
	['label' => 'Total Students', 'value' => '650', 'icon' => 'fa-users'],
	['label' => 'Teachers', 'value' => '45', 'icon' => 'fa-chalkboard-user'],
	['label' => 'Staff', 'value' => '22', 'icon' => 'fa-user-tie'],
	['label' => 'Transactions Today', 'value' => '65', 'icon' => 'fa-receipt']
];

$monthlyRevenue = [920000, 760000, 815000, 690000, 880000, 745000, 945000, 710000, 805000, 930000, 780000, 1010000];
$monthlyExpenses = [210000, 180000, 240000, 165000, 225000, 190000, 260000, 175000, 210000, 235000, 205000, 255000];
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

$recentPayments = [
	['receipt' => 'RCP001', 'student' => 'Musa Ibrahim', 'class' => 'SS2', 'amount' => 85000, 'method' => 'Bank Transfer', 'date' => '06/07/2026', 'status' => 'Paid'],
	['receipt' => 'RCP002', 'student' => 'Aisha Bello', 'class' => 'JSS 1A', 'amount' => 65000, 'method' => 'POS', 'date' => '06/07/2026', 'status' => 'Paid'],
	['receipt' => 'RCP003', 'student' => 'Daniel Okafor', 'class' => 'SS1 Science', 'amount' => 40000, 'method' => 'Cash', 'date' => '06/07/2026', 'status' => 'Pending'],
	['receipt' => 'RCP004', 'student' => 'Maryam Musa', 'class' => 'JSS 2B', 'amount' => 50000, 'method' => 'Bank Transfer', 'date' => '05/07/2026', 'status' => 'Failed']
];

$outstandingList = [
	['student' => 'Samuel Adeyemi', 'class' => 'SS2 Science', 'total' => 120000, 'paid' => 70000],
	['student' => 'Fatima Abdullahi', 'class' => 'JSS 3A', 'total' => 95000, 'paid' => 45000],
	['student' => 'Joshua Martins', 'class' => 'SS1 Science', 'total' => 110000, 'paid' => 65000]
];

$recentExpenses = [
	['date' => '06/07/2026', 'category' => 'Electricity', 'description' => 'PHCN Bill', 'amount' => 45000],
	['date' => '06/07/2026', 'category' => 'Maintenance', 'description' => 'Classroom furniture repair', 'amount' => 35000],
	['date' => '05/07/2026', 'category' => 'Stationery', 'description' => 'Receipt booklet printing', 'amount' => 18000]
];

$quickActions = [
	['label' => 'Collect School Fees', 'icon' => 'fa-money-bill-transfer'],
	['label' => 'Record Expense', 'icon' => 'fa-file-invoice-dollar'],
	['label' => 'Generate Receipt', 'icon' => 'fa-receipt'],
	['label' => 'View Outstanding Fees', 'icon' => 'fa-scale-unbalanced'],
	['label' => 'Generate Financial Report', 'icon' => 'fa-chart-pie'],
	['label' => 'Manage Fee Structure', 'icon' => 'fa-sliders'],
	['label' => 'View Payroll', 'icon' => 'fa-users-gear']
];

$notifications = ['New payment received', 'Payroll ready for July', '25 students still owe fees', 'Monthly financial report available'];
$calendarEvents = [6 => 'Fee Deadline', 15 => 'Salary Day', 28 => 'Report Due'];

function moneyFormat($amount) { return '₦' . number_format((float) $amount); }
function accountValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function statusClass($status) { return strtolower((string) $status); }
?>

<style>
	/* Accountant dashboard: scoped premium ERP-style finance overview with green school theme. */
	.accountant-dashboard-page { --acc-primary:#0f766e; --acc-primary-dark:#115e59; --acc-primary-soft:rgba(15,118,110,.1); --acc-success:#16a34a; --acc-success-soft:rgba(22,163,74,.12); --acc-warning:#f59e0b; --acc-warning-soft:rgba(245,158,11,.14); --acc-danger:#dc2626; --acc-danger-soft:rgba(220,38,38,.1); --acc-blue:#2563eb; --acc-blue-soft:rgba(37,99,235,.1); --acc-ink:#10201d; --acc-muted:#64748b; --acc-border:rgba(15,118,110,.18); --acc-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.accountant-dashboard-page .finance-hero,.accountant-dashboard-page .finance-card,.accountant-dashboard-page .summary-card,.accountant-dashboard-page .table-card,.accountant-dashboard-page .widget-card { background:rgba(255,255,255,.98); border:1px solid var(--acc-border); box-shadow:var(--acc-shadow); }
	.accountant-dashboard-page .finance-hero { position:relative; overflow:hidden; padding:28px; border-radius:26px; margin-bottom:24px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.accountant-dashboard-page .finance-hero:after { content:""; position:absolute; inset:0; background:radial-gradient(circle at top right,rgba(20,184,166,.16),transparent 36%),radial-gradient(circle at bottom left,rgba(37,99,235,.08),transparent 32%); pointer-events:none; }
	.accountant-dashboard-page .finance-hero>* { position:relative; z-index:1; }
	.accountant-dashboard-page .accountant-avatar { width:92px; height:92px; border-radius:24px; object-fit:cover; border:4px solid #fff; box-shadow:0 18px 38px rgba(15,118,110,.2); }
	.accountant-dashboard-page .finance-kicker,.accountant-dashboard-page .summary-icon,.accountant-dashboard-page .status-badge,.accountant-dashboard-page .quick-action,.accountant-dashboard-page .calendar-event { display:inline-flex; align-items:center; justify-content:center; }
	.accountant-dashboard-page .finance-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--acc-primary-soft); color:var(--acc-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.accountant-dashboard-page h3,.accountant-dashboard-page h4,.accountant-dashboard-page h5 { color:var(--acc-ink); font-weight:900; }
	.accountant-dashboard-page .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
	.accountant-dashboard-page .summary-card:hover,.accountant-dashboard-page .quick-action:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(15,23,42,.12); }
	.accountant-dashboard-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--acc-primary-soft); color:var(--acc-primary); }
	.accountant-dashboard-page .summary-icon.success{background:var(--acc-success-soft);color:var(--acc-success)} .accountant-dashboard-page .summary-icon.warning{background:var(--acc-warning-soft);color:#b45309} .accountant-dashboard-page .summary-icon.danger{background:var(--acc-danger-soft);color:var(--acc-danger)} .accountant-dashboard-page .summary-icon.blue{background:var(--acc-blue-soft);color:var(--acc-blue)}
	.accountant-dashboard-page .summary-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.accountant-dashboard-page .finance-card,.accountant-dashboard-page .table-card,.accountant-dashboard-page .widget-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.accountant-dashboard-page .finance-card,.accountant-dashboard-page .widget-card { padding:22px; }
	.accountant-dashboard-page .chart-wrap { height:230px; display:flex; align-items:end; gap:10px; padding-top:20px; border-bottom:1px solid rgba(148,163,184,.22); }
	.accountant-dashboard-page .chart-bar { flex:1; min-width:18px; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; gap:8px; height:100%; }
	.accountant-dashboard-page .bar-fill { width:100%; max-width:34px; border-radius:10px 10px 4px 4px; background:linear-gradient(180deg,var(--acc-primary),var(--acc-primary-dark)); transition:height .3s ease, transform .18s ease; }
	.accountant-dashboard-page .expense-chart .bar-fill { background:linear-gradient(180deg,#f97316,#dc2626); }
	.accountant-dashboard-page .bar-fill:hover { transform:scaleY(1.03); }
	.accountant-dashboard-page .bar-label { color:var(--acc-muted); font-size:12px; font-weight:900; }
	.accountant-dashboard-page .table-scroll { overflow:auto; }
	.accountant-dashboard-page .finance-table { min-width:780px; margin-bottom:0; }
	.accountant-dashboard-page .finance-table thead th { padding:14px 12px; background:linear-gradient(135deg,var(--acc-primary),var(--acc-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.accountant-dashboard-page .finance-table td { padding:13px 12px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.accountant-dashboard-page .finance-table tbody tr:hover { background:rgba(15,118,110,.04); }
	.accountant-dashboard-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; }
	.accountant-dashboard-page .status-paid { color:var(--acc-success); background:var(--acc-success-soft); } .accountant-dashboard-page .status-pending { color:#b45309; background:var(--acc-warning-soft); } .accountant-dashboard-page .status-failed { color:var(--acc-danger); background:var(--acc-danger-soft); }
	.accountant-dashboard-page .collect-btn { border-radius:999px; font-weight:900; }
	.accountant-dashboard-page .quick-actions-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:12px; }
	.accountant-dashboard-page .quick-action { min-height:54px; gap:10px; padding:12px 14px; border:1px solid rgba(15,118,110,.18); border-radius:16px; background:#fff; color:var(--acc-primary-dark); font-weight:900; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
	.accountant-dashboard-page .notification-list { display:grid; gap:11px; }
	.accountant-dashboard-page .notification-item { padding:12px 14px; border-radius:16px; background:#f8fafc; border:1px solid rgba(148,163,184,.22); font-weight:800; color:var(--acc-ink); }
	.accountant-dashboard-page .notification-item i { color:var(--acc-success); margin-right:8px; }
	.accountant-dashboard-page .calendar-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:7px; }
	.accountant-dashboard-page .calendar-day,.accountant-dashboard-page .calendar-head { min-height:38px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-weight:900; }
	.accountant-dashboard-page .calendar-head { color:var(--acc-muted); font-size:12px; }
	.accountant-dashboard-page .calendar-day { position:relative; background:#f8fafc; border:1px solid rgba(148,163,184,.22); color:var(--acc-ink); }
	.accountant-dashboard-page .calendar-day.has-event { background:var(--acc-primary-soft); color:var(--acc-primary-dark); border-color:rgba(15,118,110,.28); }
	.accountant-dashboard-page .calendar-event { position:absolute; right:4px; bottom:3px; width:7px; height:7px; border-radius:50%; background:var(--acc-warning); font-size:0; }
	@media(max-width:767.98px){ .accountant-dashboard-page .finance-hero,.accountant-dashboard-page .finance-card,.accountant-dashboard-page .widget-card{padding:20px;border-radius:20px}.accountant-dashboard-page .accountant-avatar{width:78px;height:78px}.accountant-dashboard-page .chart-wrap{gap:6px}.accountant-dashboard-page .summary-card h4{font-size:20px} }
</style>

<?php
$maxRevenue = max($monthlyRevenue);
$maxExpense = max($monthlyExpenses);
$firstDay = strtotime(date('Y-m-01'));
$daysInMonth = (int) date('t', $firstDay);
$startWeekday = (int) date('N', $firstDay);
?>

<div class="accountant-dashboard-page">
	<!-- Dashboard header: identifies the logged-in accountant and current work date. -->
	<section class="finance-hero">
		<div class="d-flex align-items-center gap-3 flex-wrap">
			<img src="<?php echo accountValue($accountant['profile_picture']); ?>" alt="Accountant profile picture" class="accountant-avatar">
			<div class="flex-grow-1">
				<span class="finance-kicker"><i class="fa-solid fa-calculator"></i> Accountant Dashboard</span>
				<h3 class="mt-3 mb-1">Welcome back, <?php echo accountValue($accountant['full_name']); ?></h3>
				<p class="text-muted mb-0">Staff ID: <?php echo accountValue($accountant['staff_id']); ?> | Today: <?php echo date('l, j F Y - h:i A'); ?></p>
			</div>
		</div>
	</section>

	<!-- Financial summary cards: school-wide finance KPIs for the accountant. -->
	<section class="row g-3 mb-4" aria-label="Financial summary cards">
		<?php $cards = [
			['label' => "Today's Revenue", 'value' => moneyFormat($todayRevenue), 'icon' => 'fa-sack-dollar', 'tone' => 'success'],
			['label' => 'Outstanding Fees', 'value' => moneyFormat($outstandingFees), 'icon' => 'fa-scale-unbalanced', 'tone' => 'warning'],
			['label' => "Today's Expenses", 'value' => moneyFormat($todayExpenses), 'icon' => 'fa-file-invoice-dollar', 'tone' => 'danger'],
			['label' => 'Net Income', 'value' => moneyFormat($netIncome), 'icon' => 'fa-chart-line', 'tone' => 'blue'],
			['label' => 'Students Paid', 'value' => number_format($studentsPaid), 'icon' => 'fa-user-check', 'tone' => 'success'],
			['label' => 'Outstanding Students', 'value' => number_format($outstandingStudents), 'icon' => 'fa-user-clock', 'tone' => 'warning'],
			['label' => 'Monthly Revenue', 'value' => moneyFormat($monthlyRevenueTotal), 'icon' => 'fa-calendar-check', 'tone' => 'success'],
			['label' => 'Monthly Expenses', 'value' => moneyFormat($monthlyExpensesTotal), 'icon' => 'fa-calendar-minus', 'tone' => 'danger']
		]; ?>
		<?php foreach ($cards as $card): ?>
			<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon <?php echo accountValue($card['tone']); ?>"><i class="fa-solid <?php echo accountValue($card['icon']); ?>"></i></span><h4><?php echo accountValue($card['value']); ?></h4><p class="text-muted mb-0"><?php echo accountValue($card['label']); ?></p></div></div>
		<?php endforeach; ?>
	</section>

	<!-- Revenue and expense charts: lightweight responsive bar charts using placeholder monthly values. -->
	<section class="row g-4">
		<div class="col-xl-6"><div class="finance-card"><h4 class="mb-1">Monthly Revenue</h4><p class="text-muted mb-0">Income collected from January to December.</p><div class="chart-wrap"><?php foreach ($monthlyRevenue as $index => $value): ?><div class="chart-bar" title="<?php echo accountValue($months[$index] . ': ' . moneyFormat($value)); ?>"><div class="bar-fill" style="height:<?php echo round(($value / $maxRevenue) * 100); ?>%"></div><span class="bar-label"><?php echo $months[$index]; ?></span></div><?php endforeach; ?></div></div></div>
		<div class="col-xl-6"><div class="finance-card expense-chart"><h4 class="mb-1">Monthly Expenses</h4><p class="text-muted mb-0">Operational expenses from January to December.</p><div class="chart-wrap"><?php foreach ($monthlyExpenses as $index => $value): ?><div class="chart-bar" title="<?php echo accountValue($months[$index] . ': ' . moneyFormat($value)); ?>"><div class="bar-fill" style="height:<?php echo round(($value / $maxExpense) * 100); ?>%"></div><span class="bar-label"><?php echo $months[$index]; ?></span></div><?php endforeach; ?></div></div></div>
	</section>

	<!-- Recent payments and outstanding balances: operational tables for daily finance work. -->
	<section class="row g-4">
		<div class="col-xl-7"><div class="table-card"><div class="p-3"><h4 class="mb-1">Recent Payments</h4><p class="text-muted mb-0">Latest student fee transactions.</p></div><div class="table-scroll"><table class="table finance-table align-middle"><thead><tr><th>Receipt No.</th><th>Student Name</th><th>Class</th><th>Amount</th><th>Payment Method</th><th>Date</th><th>Status</th></tr></thead><tbody><?php foreach ($recentPayments as $payment): ?><tr><td><?php echo accountValue($payment['receipt']); ?></td><td><?php echo accountValue($payment['student']); ?></td><td><?php echo accountValue($payment['class']); ?></td><td><?php echo moneyFormat($payment['amount']); ?></td><td><?php echo accountValue($payment['method']); ?></td><td><?php echo accountValue($payment['date']); ?></td><td><span class="status-badge status-<?php echo accountValue(statusClass($payment['status'])); ?>"><i class="fa-solid fa-circle"></i><?php echo accountValue($payment['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
		<div class="col-xl-5"><div class="table-card"><div class="p-3"><h4 class="mb-1">Outstanding Fees</h4><p class="text-muted mb-0">Students with unpaid balances.</p></div><div class="table-scroll"><table class="table finance-table align-middle"><thead><tr><th>Student</th><th>Class</th><th>Total Fees</th><th>Amount Paid</th><th>Balance</th><th>Action</th></tr></thead><tbody><?php foreach ($outstandingList as $item): ?><?php $balance = $item['total'] - $item['paid']; ?><tr><td><?php echo accountValue($item['student']); ?></td><td><?php echo accountValue($item['class']); ?></td><td><?php echo moneyFormat($item['total']); ?></td><td><?php echo moneyFormat($item['paid']); ?></td><td><?php echo moneyFormat($balance); ?></td><td><button type="button" class="btn btn-sm btn-outline-success collect-btn"><i class="fa-solid fa-money-bill-transfer me-1"></i>Collect Payment</button></td></tr><?php endforeach; ?></tbody></table></div></div></div>
	</section>

	<!-- Expense table, quick actions, notifications, and calendar widgets. -->
	<section class="row g-4">
		<div class="col-xl-6"><div class="table-card"><div class="p-3"><h4 class="mb-1">Recent Expenses</h4><p class="text-muted mb-0">Latest recorded school expenses.</p></div><div class="table-scroll"><table class="table finance-table align-middle"><thead><tr><th>Date</th><th>Expense Category</th><th>Description</th><th>Amount</th></tr></thead><tbody><?php foreach ($recentExpenses as $expense): ?><tr><td><?php echo accountValue($expense['date']); ?></td><td><?php echo accountValue($expense['category']); ?></td><td><?php echo accountValue($expense['description']); ?></td><td><?php echo moneyFormat($expense['amount']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
		<div class="col-xl-6"><div class="widget-card"><h4 class="mb-3">Quick Actions</h4><div class="quick-actions-grid"><?php foreach ($quickActions as $action): ?><a href="javascript:void(0);" class="quick-action"><i class="fa-solid <?php echo accountValue($action['icon']); ?>"></i><?php echo accountValue($action['label']); ?></a><?php endforeach; ?></div></div></div>
		<div class="col-xl-5"><div class="widget-card"><h4 class="mb-3">Notifications</h4><div class="notification-list"><?php foreach ($notifications as $note): ?><div class="notification-item"><i class="fa-solid fa-check-circle"></i><?php echo accountValue($note); ?></div><?php endforeach; ?></div></div></div>
		<div class="col-xl-7"><div class="widget-card"><div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3"><h4 class="mb-0"><?php echo date('F Y'); ?></h4><span class="finance-kicker"><i class="fa-solid fa-calendar-days"></i> Finance Calendar</span></div><div class="calendar-grid"><?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $head): ?><div class="calendar-head"><?php echo $head; ?></div><?php endforeach; ?><?php for ($blank = 1; $blank < $startWeekday; $blank++): ?><div></div><?php endfor; ?><?php for ($day = 1; $day <= $daysInMonth; $day++): ?><div class="calendar-day <?php echo isset($calendarEvents[$day]) ? 'has-event' : ''; ?>" title="<?php echo isset($calendarEvents[$day]) ? accountValue($calendarEvents[$day]) : ''; ?>"><?php echo $day; ?><?php if (isset($calendarEvents[$day])): ?><span class="calendar-event"><?php echo accountValue($calendarEvents[$day]); ?></span><?php endif; ?></div><?php endfor; ?></div></div></div>
	</section>

	<!-- Footer statistics: additional school-wide operational counters. -->
	<section class="row g-3 mt-1" aria-label="Footer statistics">
		<?php foreach ($footerStats as $stat): ?><div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid <?php echo accountValue($stat['icon']); ?>"></i></span><h4><?php echo accountValue($stat['value']); ?></h4><p class="text-muted mb-0"><?php echo accountValue($stat['label']); ?></p></div></div><?php endforeach; ?>
	</section>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Accountant dashboard behavior: update the visible clock without requiring a page reload.
(function () {
	var heroText = document.querySelector('.finance-hero .text-muted');
	if (!heroText) { return; }
	setInterval(function () {
		var now = new Date();
		var formatted = now.toLocaleString('en-NG', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: 'numeric', minute: '2-digit' });
		heroText.textContent = 'Staff ID: <?php echo accountValue($accountant['staff_id']); ?> | Today: ' + formatted;
	}, 60000);
}());
</script>

<?php require_once('includes/footer.php'); ?>
