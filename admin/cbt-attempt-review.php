<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\CBTService;

$cbtService = new CBTService();
require_once('includes/header.php');
require_once('includes/cbt-page-helper.php');
require_once('includes/cbt-module-styles.php');

$attemptId = (int) ($_GET['attempt_id'] ?? 0);
$attempt = $cbtService->attemptResult($attemptId);
$passed = $attempt && (float) $attempt['percentage'] >= (float) $attempt['pass_mark'];
?>
<div class="admin-cbt-module">
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> CBT Management <i class="fa-solid fa-angle-right mx-1"></i> Attempt Review</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-eye"></i> Attempt Review</span>
                <h3 class="mt-3 mb-2">Attempt Review</h3>
                <p class="text-muted mb-0">Full answer breakdown for this student's CBT attempt.</p>
            </div>
            <div class="d-flex gap-2">
                <a class="module-btn btn-outline-soft" href="cbt-attempts-results.php"><i class="fa-solid fa-arrow-left"></i> Back</a>
                <button class="module-btn btn-primary-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
            </div>
        </div>
    </section>

    <?php if (!$attempt): ?>
        <section class="module-card"><p class="text-muted fw-bold mb-0">Attempt not found.</p></section>
    <?php else: ?>
        <section class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-user"></i></span><h4><?php echo sms_e($attempt['first_name'] . ' ' . $attempt['last_name']); ?></h4><p class="text-muted mb-0">Student &middot; <?php echo sms_e($attempt['registration_no']); ?></p></div></div>
            <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-book"></i></span><h4><?php echo sms_e($attempt['exam_title']); ?></h4><p class="text-muted mb-0"><?php echo sms_e($attempt['subject_name']); ?></p></div></div>
            <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-percent"></i></span><h4><?php echo sms_e($attempt['score']); ?> pts (<?php echo sms_e($attempt['percentage']); ?>%)</h4><p class="text-muted mb-0">Grade: <?php echo sms_e($attempt['grade'] ?? '-'); ?></p></div></div>
            <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon <?php echo $passed ? 'success' : 'danger'; ?>"><i class="fa-solid <?php echo $passed ? 'fa-circle-check' : 'fa-circle-xmark'; ?>"></i></span><h4><?php echo $passed ? 'Passed' : 'Failed'; ?></h4><p class="text-muted mb-0">Pass mark: <?php echo sms_e($attempt['pass_mark']); ?>%</p></div></div>
        </section>

        <section class="module-card">
            <h4 class="mb-3">Answer Breakdown</h4>
            <?php foreach ($attempt['review'] as $i => $row): ?>
                <div class="module-card mb-3">
                    <h5>Question <?php echo $i + 1; ?> <span class="status-badge <?php echo $row['is_correct'] ? 'status-active' : 'status-locked'; ?>"><?php echo $row['is_correct'] ? 'Correct' : 'Incorrect'; ?> (<?php echo sms_e($row['mark_awarded']); ?>/<?php echo sms_e($row['mark']); ?>)</span></h5>
                    <p class="fw-bold"><?php echo sms_e($row['question_text']); ?></p>
                    <div class="row g-2">
                        <?php foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $col): ?>
                            <?php
                            $marker = '';
                            if ($letter === $row['correct_option']) { $marker = ' <span class="status-badge status-active">Correct</span>'; }
                            if ($letter === $row['selected_option'] && $letter !== $row['correct_option']) { $marker = ' <span class="status-badge status-locked">Selected</span>'; }
                            ?>
                            <div class="col-sm-6"><div class="metric-row"><span><?php echo $letter; ?>. <?php echo sms_e($row[$col]); ?></span><span><?php echo $marker; ?></span></div></div>
                        <?php endforeach; ?>
                    </div>
                    <p class="mb-0 mt-2 text-muted fw-bold">Student's Answer: <?php echo sms_e($row['selected_option'] ?? 'Not answered'); ?> &middot; Correct Answer: <?php echo sms_e($row['correct_option']); ?></p>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</div></div></div>
<?php require_once('includes/footer.php'); ?>
