<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\StudentService;

$studentService = new StudentService();
require_once('includes/header.php');

$sessions = $studentService->sessionsForSelect();
$classes = $studentService->classesForSelect();

$sessionId = (int) ($_GET['session_id'] ?? $studentService->currentSessionId() ?? 0);
$classId = (int) ($_GET['class_id'] ?? 0);

$summary = $studentService->reportsSummary(['session_id' => $sessionId, 'class_id' => $classId]);

$cards = [
    ['title' => 'Total Enrolled', 'value' => number_format($summary['total_enrolled']), 'icon' => 'fa-user-graduate', 'color' => 'success'],
    ['title' => 'With Guardian on File', 'value' => number_format($summary['with_guardian']), 'icon' => 'fa-people-roof', 'color' => 'blue'],
    ['title' => 'Missing Guardian', 'value' => number_format($summary['without_guardian']), 'icon' => 'fa-triangle-exclamation', 'color' => 'warning'],
    ['title' => 'Classes Reporting', 'value' => number_format(count($summary['by_class'])), 'icon' => 'fa-school', 'color' => 'success'],
];

$genderLabels = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'unspecified' => 'Unspecified'];
$maxByClass = $summary['by_class'] ? max(array_column($summary['by_class'], 'total')) : 1;

function srMoneyless($n) { return number_format((int) $n); }
?>

<style>
    .admin-student-module { --asm-primary:#0f766e; --asm-primary-dark:#115e59; --asm-soft:rgba(15,118,110,.1); --asm-border:rgba(15,118,110,.16); --asm-ink:#10201d; --asm-muted:#64748b; --asm-danger:#dc2626; --asm-warning:#d97706; --asm-blue:#2563eb; --asm-shadow:0 22px 56px rgba(15,23,42,.08); padding-bottom:34px; }
    .admin-student-module .module-hero,.admin-student-module .module-card,.admin-student-module .summary-card { background:rgba(255,255,255,.98); border:1px solid var(--asm-border); box-shadow:var(--asm-shadow); }
    .admin-student-module .module-hero { padding:26px; border-radius:24px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),#fff); }
    .admin-student-module .breadcrumb-line { color:var(--asm-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
    .admin-student-module .module-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--asm-soft); color:var(--asm-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
    .admin-student-module h3,.admin-student-module h4,.admin-student-module h5 { color:var(--asm-ink); font-weight:900; }
    .admin-student-module .summary-card { height:100%; padding:18px; border-radius:20px; }
    .admin-student-module .summary-icon { width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; background:var(--asm-soft); color:var(--asm-primary); }
    .admin-student-module .summary-icon.blue { background:rgba(37,99,235,.1); color:var(--asm-blue); }
    .admin-student-module .summary-icon.warning { background:rgba(245,158,11,.13); color:var(--asm-warning); }
    .admin-student-module .summary-card h4 { margin:12px 0 2px; font-size:25px; }
    .admin-student-module .module-card { border-radius:22px; padding:22px; margin-bottom:22px; }
    .admin-student-module .filter-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
    .admin-student-module label { color:var(--asm-ink); font-size:13px; font-weight:900; margin-bottom:7px; }
    .admin-student-module .form-control,.admin-student-module .form-select { min-height:46px; border-radius:14px; border:1px solid rgba(148,163,184,.35); font-weight:700; }
    .admin-student-module .module-btn { border:0; min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:14px; padding:10px 15px; font-weight:900; text-decoration:none; }
    .admin-student-module .module-btn:hover { transform:translateY(-2px); color:#fff; }
    .admin-student-module .btn-primary-soft { background:var(--asm-primary); color:#fff; box-shadow:0 12px 24px rgba(15,118,110,.22); }
    .admin-student-module .btn-muted-soft { background:#f1f5f9; color:var(--asm-ink); }
    .admin-student-module .btn-outline-soft { background:#fff; color:var(--asm-primary-dark); border:1px solid var(--asm-border); }
    .admin-student-module .btn-outline-soft:hover { color:var(--asm-primary-dark); }
    .admin-student-module .table-shell { overflow:auto; border:1px solid rgba(148,163,184,.2); border-radius:18px; }
    .admin-student-module table { min-width:800px; margin-bottom:0; }
    .admin-student-module thead th { position:sticky; top:0; z-index:2; background:#f0fdf4; color:var(--asm-primary-dark); font-size:12px; text-transform:uppercase; letter-spacing:.03em; border-bottom:1px solid var(--asm-border); }
    .admin-student-module tbody td { vertical-align:middle; color:#1f2937; font-weight:700; }
    .admin-student-module .mini-list{display:grid;gap:10px}.admin-student-module .mini-item{display:flex;justify-content:space-between;gap:12px;padding:11px 13px;border-radius:14px;background:#f8fafc;border:1px solid rgba(148,163,184,.22);font-weight:800}
    .admin-student-module .chart-wrap{height:220px;display:flex;align-items:end;gap:10px;padding-top:20px;border-bottom:1px solid rgba(148,163,184,.22)}.admin-student-module .chart-bar{flex:1;min-width:24px;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;gap:8px;height:100%}.admin-student-module .bar-fill{width:60%;max-width:34px;border-radius:8px 8px 3px 3px;background:linear-gradient(180deg,#10b981,#0f766e)}.admin-student-module .bar-label{color:var(--asm-muted);font-size:11px;font-weight:900;text-align:center}
    @media(max-width:991.98px){ .admin-student-module .filter-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
    @media(max-width:575.98px){ .admin-student-module .module-hero,.admin-student-module .module-card{padding:18px;border-radius:18px}.admin-student-module .filter-grid{grid-template-columns:1fr}.admin-student-module .module-btn{width:100%} }
    @media print{.sidebar,.header,.navbar,.admin-student-module .module-btn{display:none!important}}
</style>

<div class="admin-student-module">
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Student Management <i class="fa-solid fa-angle-right mx-1"></i> Reports</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-chart-pie"></i> Student Reports</span>
                <h3 class="mt-3 mb-2">Student Reports</h3>
                <p class="text-muted mb-0">Real enrollment, demographics, and guardian-coverage analysis for the selected session and class.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="module-btn btn-outline-soft" href="student-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'pdf']))); ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                <a class="module-btn btn-outline-soft" href="student-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'excel']))); ?>"><i class="fa-solid fa-file-excel"></i> Excel</a>
                <a class="module-btn btn-outline-soft" href="student-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'csv']))); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                <button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4"><?php foreach ($cards as $card): ?><div class="col-sm-6 col-xl-3"><?php sms_render_component('statistics-card', $card); ?></div><?php endforeach; ?></section>

    <section class="module-card">
        <h4>Filters</h4>
        <form method="get">
            <div class="filter-grid">
                <div><label>Academic Session</label><select class="form-select" name="session_id"><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $sessionId === (int) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Class</label><select class="form-select" name="class_id"><option value="">All Classes</option><?php foreach ($classes as $c): ?><option value="<?php echo (int) $c['id']; ?>" <?php echo $classId === (int) $c['id'] ? 'selected' : ''; ?>><?php echo sms_e($c['name']); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate</button><a class="module-btn btn-muted-soft" href="student-reports.php">Reset</a></div>
            </div>
        </form>
    </section>

    <section class="row g-4">
        <div class="col-xl-6"><div class="module-card"><h4 class="mb-3">Gender Breakdown</h4><div class="mini-list">
            <?php foreach ($summary['by_gender'] as $g): ?><div class="mini-item"><span><?php echo sms_e($genderLabels[$g['gender']] ?? ucfirst($g['gender'])); ?></span><strong><?php echo srMoneyless($g['total']); ?></strong></div><?php endforeach; ?>
            <?php if (!$summary['by_gender']): ?><p class="text-muted mb-0">No enrollment data for this selection.</p><?php endif; ?>
        </div></div></div>
        <div class="col-xl-6"><div class="module-card"><h4 class="mb-3">Status Breakdown (All Sessions)</h4><div class="mini-list">
            <?php foreach ($summary['by_status'] as $s): ?><div class="mini-item"><span><?php echo sms_e(ucfirst($s['status'])); ?></span><strong><?php echo srMoneyless($s['total']); ?></strong></div><?php endforeach; ?>
        </div></div></div>
    </section>

    <section class="module-card">
        <h4 class="mb-1">Enrollment by Class Chart</h4>
        <p class="text-muted mb-0">Number of students per class/section for the selected session.</p>
        <div class="chart-wrap">
            <?php foreach ($summary['by_class'] as $row): ?>
                <div class="chart-bar"><div class="bar-fill" style="height:<?php echo round(($row['total'] / $maxByClass) * 100); ?>%" title="<?php echo (int) $row['total']; ?>"></div><span class="bar-label"><?php echo sms_e($row['class_name'] . ($row['section_name'] ? ' - ' . $row['section_name'] : '')); ?></span></div>
            <?php endforeach; ?>
        </div>
        <?php if (!$summary['by_class']): ?><p class="text-muted mt-3 mb-0">No enrollment data for this selection.</p><?php endif; ?>
    </section>

    <section class="module-card">
        <h4 class="mb-3">Enrollment by Class Table</h4>
        <div class="table-shell"><table class="table">
            <thead><tr><th>Class</th><th>Section</th><th>Total</th><th>Male</th><th>Female</th></tr></thead>
            <tbody>
                <?php foreach ($summary['by_class'] as $row): ?>
                    <tr><td><?php echo sms_e($row['class_name']); ?></td><td><?php echo sms_e($row['section_name'] ?? 'Whole Class'); ?></td><td><?php echo (int) $row['total']; ?></td><td><?php echo (int) $row['male_count']; ?></td><td><?php echo (int) $row['female_count']; ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$summary['by_class']): ?><tr><td colspan="5" class="text-center text-muted py-4">No records found.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
</div>
</div>
</div>
<?php require_once('includes/footer.php'); ?>
