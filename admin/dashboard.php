<?php require_once('includes/header.php'); ?>

<?php
// Admin dashboard placeholder data. Replace these arrays with database queries during backend integration.
$adminName = 'Administrator';
$currentSession = sms_config('academic_session', '2025/2026');
$currentTerm = sms_config('term', 'First Term');
$currentDate = date('l, j F Y');
$summaryCards = [
    ['title' => 'Total Students', 'value' => '1,250', 'description' => 'Registered Students', 'icon' => 'fa-user-graduate', 'tone' => 'success'],
    ['title' => 'Total Teachers', 'value' => '64', 'description' => 'Active Teachers', 'icon' => 'fa-chalkboard-user', 'tone' => 'blue'],
    ['title' => 'Total Classes', 'value' => '36', 'description' => 'Configured Classes', 'icon' => 'fa-school', 'tone' => 'warning'],
    ['title' => 'Total Subjects', 'value' => '48', 'description' => 'Approved Subjects', 'icon' => 'fa-book-open', 'tone' => 'success'],
    ['title' => 'Fee Collected', 'value' => sms_money(8450000), 'description' => 'This Month', 'icon' => 'fa-sack-dollar', 'tone' => 'blue'],
    ['title' => 'Outstanding Fees', 'value' => sms_money(2350000), 'description' => 'Unpaid Balances', 'icon' => 'fa-scale-unbalanced', 'tone' => 'danger'],
];
$quickActions = [
    ['label' => 'Add Student', 'icon' => 'fa-user-plus', 'href' => 'students.php'],
    ['label' => 'Add Teacher', 'icon' => 'fa-person-chalkboard', 'href' => 'teachers.php'],
    ['label' => 'Collect School Fees', 'icon' => 'fa-cash-register', 'href' => '../accountant/fee-collection.php'],
    ['label' => 'Publish Results', 'icon' => 'fa-square-poll-vertical', 'href' => 'result-management.php'],
    ['label' => 'Create CBT Exam', 'icon' => 'fa-laptop-code', 'href' => 'cbt-management.php'],
    ['label' => 'View Reports', 'icon' => 'fa-chart-pie', 'href' => '../accountant/financial-reports.php'],
];
$enrollment = [
    ['label' => 'JSS 1', 'value' => 220],
    ['label' => 'JSS 2', 'value' => 205],
    ['label' => 'JSS 3', 'value' => 198],
    ['label' => 'SS 1', 'value' => 230],
    ['label' => 'SS 2', 'value' => 210],
    ['label' => 'SS 3', 'value' => 187],
];
$maxEnrollment = max(array_column($enrollment, 'value'));
?>

<style>
    /* Admin dashboard: lightweight landing page styles that preserve the shared green theme. */
    .admin-dashboard-page { --ad-primary:#0f766e; --ad-primary-dark:#115e59; --ad-primary-soft:rgba(15,118,110,.1); --ad-success:#16a34a; --ad-warning:#f59e0b; --ad-danger:#dc2626; --ad-blue:#2563eb; --ad-ink:#10201d; --ad-muted:#64748b; --ad-border:rgba(15,118,110,.18); --ad-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
    .admin-dashboard-page .admin-hero,.admin-dashboard-page .summary-card,.admin-dashboard-page .admin-card,.admin-dashboard-page .quick-action { background:rgba(255,255,255,.98); border:1px solid var(--ad-border); box-shadow:var(--ad-shadow); }
    .admin-dashboard-page .admin-hero { padding:28px; border-radius:26px; margin-bottom:24px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
    .admin-dashboard-page .breadcrumb-line { color:var(--ad-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
    .admin-dashboard-page .admin-kicker,.admin-dashboard-page .summary-icon,.admin-dashboard-page .quick-action-icon { display:inline-flex; align-items:center; justify-content:center; }
    .admin-dashboard-page .admin-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--ad-primary-soft); color:var(--ad-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
    .admin-dashboard-page h3,.admin-dashboard-page h4,.admin-dashboard-page h5 { color:var(--ad-ink); font-weight:900; }
    .admin-dashboard-page .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-dashboard-page .summary-card:hover,.admin-dashboard-page .quick-action:hover,.admin-dashboard-page .admin-card:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(15,23,42,.12); }
    .admin-dashboard-page .summary-icon { width:44px; height:44px; border-radius:14px; background:var(--ad-primary-soft); color:var(--ad-primary); }
    .admin-dashboard-page .summary-icon.success { background:rgba(22,163,74,.12); color:var(--ad-success); }
    .admin-dashboard-page .summary-icon.warning { background:rgba(245,158,11,.14); color:#b45309; }
    .admin-dashboard-page .summary-icon.danger { background:rgba(220,38,38,.1); color:var(--ad-danger); }
    .admin-dashboard-page .summary-icon.blue { background:rgba(37,99,235,.1); color:var(--ad-blue); }
    .admin-dashboard-page .summary-card h4 { margin:12px 0 2px; font-size:25px; }
    .admin-dashboard-page .admin-card { border-radius:24px; padding:24px; margin-bottom:22px; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-dashboard-page .quick-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; }
    .admin-dashboard-page .quick-action { min-height:58px; display:flex; align-items:center; gap:12px; padding:14px; border-radius:16px; color:var(--ad-primary-dark); text-decoration:none; font-weight:900; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-dashboard-page .quick-action-icon { width:38px; height:38px; border-radius:13px; background:var(--ad-primary-soft); color:var(--ad-primary); }
    .admin-dashboard-page .chart-wrap { height:260px; display:flex; align-items:end; gap:14px; padding-top:22px; border-bottom:1px solid rgba(148,163,184,.22); }
    .admin-dashboard-page .chart-bar { flex:1; min-width:28px; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; gap:9px; height:100%; }
    .admin-dashboard-page .bar-fill { width:100%; max-width:48px; border-radius:12px 12px 4px 4px; background:linear-gradient(180deg,var(--ad-primary),var(--ad-primary-dark)); }
    .admin-dashboard-page .bar-label { color:var(--ad-muted); font-size:12px; font-weight:900; white-space:nowrap; }
    @media(max-width:767.98px){ .admin-dashboard-page .admin-hero,.admin-dashboard-page .admin-card{padding:20px;border-radius:20px}.admin-dashboard-page .chart-wrap{gap:8px}.admin-dashboard-page .summary-card h4{font-size:21px} }
</style>

<div class="admin-dashboard-page">
    <!-- Page header: concise admin landing context. -->
    <section class="admin-hero">
        <div class="breadcrumb-line">Dashboard</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="admin-kicker"><i class="fa-solid fa-user-shield"></i> Admin Dashboard</span>
                <h3 class="mt-3 mb-2">Welcome back, <?php echo sms_e($adminName); ?>.</h3>
                <p class="text-muted mb-0">Session: <strong><?php echo sms_e($currentSession); ?></strong> | Term: <strong><?php echo sms_e($currentTerm); ?></strong> | Date: <strong><?php echo sms_e($currentDate); ?></strong></p>
            </div>
        </div>
    </section>

    <!-- Summary cards: high-level school activity overview for administrators. -->
    <section class="row g-3 mb-4" aria-label="Admin dashboard summary cards">
        <?php foreach ($summaryCards as $card): ?>
            <div class="col-sm-6 col-xl-4">
                <div class="summary-card">
                    <span class="summary-icon <?php echo sms_e($card['tone']); ?>"><i class="fa-solid <?php echo sms_e($card['icon']); ?>"></i></span>
                    <h4><?php echo sms_e($card['value']); ?></h4>
                    <p class="mb-1 fw-bold"><?php echo sms_e($card['title']); ?></p>
                    <p class="text-muted mb-0"><?php echo sms_e($card['description']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Quick actions: shortcuts to common administrative workflows. -->
    <section class="admin-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Quick Actions</h4>
                <p class="text-muted mb-0">Jump directly to common school management tasks.</p>
            </div>
        </div>
        <div class="quick-grid">
            <?php foreach ($quickActions as $action): ?>
                <a href="<?php echo sms_e($action['href']); ?>" class="quick-action">
                    <span class="quick-action-icon"><i class="fa-solid <?php echo sms_e($action['icon']); ?>"></i></span>
                    <span><?php echo sms_e($action['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Enrollment chart: simple CSS chart with placeholder data ready for database replacement. -->
    <section class="admin-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="mb-1">Student Enrollment by Class</h4>
                <p class="text-muted mb-0">Placeholder enrollment distribution for the current academic session.</p>
            </div>
            <span class="admin-kicker"><i class="fa-solid fa-chart-column"></i> Enrollment</span>
        </div>
        <div class="chart-wrap" aria-label="Student enrollment chart">
            <?php foreach ($enrollment as $item): ?>
                <div class="chart-bar" title="<?php echo sms_e($item['label'] . ': ' . $item['value'] . ' students'); ?>">
                    <div class="bar-fill" style="height:<?php echo round(($item['value'] / $maxEnrollment) * 100); ?>%"></div>
                    <span class="bar-label"><?php echo sms_e($item['label']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

</div>
</div>

<?php require_once('includes/footer.php'); ?>
