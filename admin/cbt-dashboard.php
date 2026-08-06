<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\CBTService;

$cbtService = new CBTService();
require_once('includes/header.php');
require_once('includes/cbt-page-helper.php');
require_once('includes/cbt-module-styles.php');

$stats = $cbtService->dashboardStats();
$participation = $cbtService->participationByClass();
$monthly = $cbtService->monthlyExamsChart((int) date('Y'));
$reports = $cbtService->reportsSummary();
$subjectPerf = $cbtService->subjectPerformance();
$activities = $cbtService->recentActivity(8);

$cards = [
    ['title' => 'Total Exams', 'value' => number_format($stats['total_exams']), 'description' => 'CBT exams created', 'icon' => 'fa-laptop-file', 'color' => 'success'],
    ['title' => 'Active Exams', 'value' => number_format($stats['active_exams']), 'description' => 'Currently available', 'icon' => 'fa-toggle-on', 'color' => 'success'],
    ['title' => 'Completed Exams', 'value' => number_format($stats['completed_exams']), 'description' => 'Closed CBT exams', 'icon' => 'fa-circle-check', 'color' => 'blue'],
    ['title' => 'Total Questions', 'value' => number_format($stats['total_questions']), 'description' => 'Across all exams', 'icon' => 'fa-circle-question', 'color' => 'warning'],
    ['title' => 'Student Attempts', 'value' => number_format($stats['total_attempts']), 'description' => 'Submitted attempts', 'icon' => 'fa-users', 'color' => 'success'],
    ['title' => 'Average Score', 'value' => $stats['average_score'] . '%', 'description' => 'All CBT attempts', 'icon' => 'fa-chart-line', 'color' => 'blue'],
];

$maxParticipation = $participation ? max(array_column($participation, 'attempts')) : 1;
$maxMonthly = max(1, max($monthly));
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

function sms_cbtd_activity_icon(string $action): string
{
    return match (true) {
        str_contains($action, 'created') => 'fa-plus',
        str_contains($action, 'published') => 'fa-bullhorn',
        str_contains($action, 'active') => 'fa-toggle-on',
        str_contains($action, 'archived') => 'fa-box-archive',
        str_contains($action, 'deleted') => 'fa-trash',
        str_contains($action, 'question') => 'fa-circle-question',
        default => 'fa-circle-info',
    };
}
?>
<div class="admin-cbt-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> CBT Management <i class="fa-solid fa-angle-right mx-1"></i> Dashboard</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-gauge-high"></i> CBT Dashboard</span>
                <h3 class="mt-3 mb-2">CBT Dashboard</h3>
                <p class="text-muted mb-0">Overview of CBT exams, questions, attempts, performance, and recent activity.</p>
            </div>
            <a class="module-btn btn-primary-soft" href="cbt-exams.php"><i class="fa-solid fa-plus"></i> Manage Exams</a>
        </div>
    </section>

    <?php sms_cbt_render_cards($cards); ?>

    <div class="two-grid">
        <section class="module-card">
            <h4>Exam Participation by Class</h4>
            <?php if ($participation): ?>
                <div class="chart-bars">
                    <?php foreach ($participation as $row): ?>
                        <div class="chart-bar" style="height:<?php echo max(6, round(((int) $row['attempts'] / $maxParticipation) * 100)); ?>%"><span><?php echo sms_e($row['name']); ?> (<?php echo (int) $row['attempts']; ?>)</span></div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted fw-bold mb-0 mt-3">No attempts submitted yet.</p>
            <?php endif; ?>
        </section>
        <section class="module-card">
            <h4>Monthly Exams (<?php echo date('Y'); ?>)</h4>
            <div class="chart-bars">
                <?php foreach ($months as $i => $m): ?>
                    <div class="chart-bar" style="height:<?php echo max(6, round(($monthly[$i + 1] / $maxMonthly) * 100)); ?>%"><span><?php echo $m; ?> (<?php echo $monthly[$i + 1]; ?>)</span></div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <div class="two-grid">
        <section class="module-card">
            <h4>Pass vs Fail</h4>
            <div class="metric-row"><span>Total Attempts</span><span><?php echo number_format($reports['total_attempts']); ?></span></div>
            <div class="metric-row"><span>Pass Rate</span><span><?php echo sms_e((string) $reports['pass_rate']); ?>%</span></div>
            <div class="metric-row"><span>Highest Score</span><span><?php echo sms_e((string) $reports['highest']); ?>%</span></div>
            <div class="metric-row"><span>Lowest Score</span><span><?php echo sms_e((string) $reports['lowest']); ?>%</span></div>
        </section>
        <section class="module-card">
            <h4>Average Scores</h4>
            <div class="metric-row"><span>Best Subject</span><span><?php echo $subjectPerf['best'] ? sms_e($subjectPerf['best']['name']) . ' - ' . round((float) $subjectPerf['best']['avg_pct'], 1) . '%' : 'No data yet'; ?></span></div>
            <div class="metric-row"><span>Lowest Subject</span><span><?php echo $subjectPerf['lowest'] ? sms_e($subjectPerf['lowest']['name']) . ' - ' . round((float) $subjectPerf['lowest']['avg_pct'], 1) . '%' : 'No data yet'; ?></span></div>
            <div class="metric-row"><span>Overall Average</span><span><?php echo sms_e((string) $reports['average']); ?>%</span></div>
            <div class="metric-row"><span>Best Performing Class</span><span><?php echo sms_e($reports['best_class']); ?></span></div>
        </section>
    </div>

    <section class="module-card">
        <h4>Recent Activities</h4>
        <div class="row g-3 mt-1">
            <?php foreach ($activities as $activity): ?>
                <div class="col-md-6 col-xl-3">
                    <div class="metric-row"><span><i class="fa-solid <?php echo sms_cbtd_activity_icon($activity['action']); ?> me-2"></i><?php echo sms_e(ucwords(str_replace(['cbt.', '.', '_'], ['', ' ', ' '], $activity['action']))); ?><br><small class="text-muted"><?php echo sms_e($activity['actor_username'] ?? 'System'); ?></small></span><span><?php echo sms_e(date('M d, H:i', strtotime($activity['created_at']))); ?></span></div>
                </div>
            <?php endforeach; ?>
            <?php if (!$activities): ?><p class="text-muted fw-bold mb-0">No recent CBT activity yet.</p><?php endif; ?>
        </div>
    </section>
</div></div></div>
<?php require_once('includes/footer.php'); ?>
