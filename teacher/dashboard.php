<?php require_once('includes/header.php'); ?>

<?php
// Dashboard placeholder data. Replace these arrays with database queries when backend integration is ready.
$teacher = [
	'profile_picture' => '../assets/img/avatar/avatar-21.jpg',
	'full_name' => 'Mr. Adewale Olumide Johnson',
	'staff_id' => 'TCH001',
	'department' => 'Science',
	'academic_session' => '2025/2026',
	'term' => 'First Term'
];

$assignedClasses = ['JSS 1A', 'JSS 2B', 'SS 1 Science', 'SS 2 Science'];
$subjects = ['Mathematics', 'Physics', 'Computer Science'];
$totalStudents = 120;
$pendingTasks = [
	['label' => 'Results to Submit', 'count' => 2, 'icon' => 'fa-file-pen'],
	['label' => 'Attendance Pending', 'count' => 5, 'icon' => 'fa-calendar-xmark']
];
$recentActivities = [
	'Submitted Mathematics results',
	'Marked SS2 attendance',
	'Created Physics CBT questions'
];
$upcomingTasks = [
	'Submit JSS2 results',
	'Complete attendance for SS1',
	'Prepare CBT questions'
];
?>

<style>
	/* Teacher dashboard: scoped premium overview styles for academic activity summaries. */
	.teacher-dashboard-page {
		--dash-primary: #0f766e;
		--dash-primary-dark: #115e59;
		--dash-primary-soft: rgba(15, 118, 110, .11);
		--dash-blue: #2563eb;
		--dash-blue-soft: rgba(37, 99, 235, .1);
		--dash-warning: #f59e0b;
		--dash-warning-soft: rgba(245, 158, 11, .13);
		--dash-danger: #dc2626;
		--dash-danger-soft: rgba(220, 38, 38, .1);
		--dash-ink: #10201d;
		--dash-muted: #64748b;
		--dash-border: rgba(15, 118, 110, .18);
		--dash-shadow: 0 22px 60px rgba(15, 23, 42, .09);
		padding-bottom: 34px;
	}

	.teacher-dashboard-page .dashboard-welcome,
	.teacher-dashboard-page .summary-card,
	.teacher-dashboard-page .activity-panel {
		background: rgba(255, 255, 255, .97);
		border: 1px solid var(--dash-border);
		box-shadow: var(--dash-shadow);
	}

	.teacher-dashboard-page .dashboard-welcome {
		position: relative;
		overflow: hidden;
		padding: 28px;
		border-radius: 26px;
		margin-bottom: 24px;
	}

	.teacher-dashboard-page .dashboard-welcome::after {
		content: "";
		position: absolute;
		inset: 0;
		background: radial-gradient(circle at top right, rgba(20, 184, 166, .16), transparent 35%), radial-gradient(circle at bottom left, rgba(37, 99, 235, .1), transparent 32%);
		pointer-events: none;
	}

	.teacher-dashboard-page .dashboard-welcome > * {
		position: relative;
		z-index: 1;
	}

	.teacher-dashboard-page .teacher-avatar,
	.teacher-dashboard-page .welcome-chip,
	.teacher-dashboard-page .summary-icon,
	.teacher-dashboard-page .dashboard-tag,
	.teacher-dashboard-page .timeline-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.teacher-dashboard-page .teacher-avatar {
		width: 118px;
		height: 118px;
		border-radius: 30px;
		background: #fff;
		box-shadow: 0 18px 40px rgba(15, 23, 42, .16);
		flex: 0 0 auto;
	}

	.teacher-dashboard-page .teacher-avatar img {
		width: 104px;
		height: 104px;
		border-radius: 24px;
		object-fit: cover;
	}

	.teacher-dashboard-page .welcome-chip {
		gap: 8px;
		padding: 8px 12px;
		border-radius: 999px;
		background: var(--dash-primary-soft);
		color: var(--dash-primary-dark);
		font-size: 12px;
		font-weight: 800;
	}

	.teacher-dashboard-page .dashboard-welcome h3 {
		margin: 10px 0 6px;
		color: var(--dash-ink);
		font-size: 28px;
		font-weight: 900;
	}

	.teacher-dashboard-page .welcome-meta {
		color: var(--dash-muted);
		font-weight: 700;
	}

	.teacher-dashboard-page .summary-card,
	.teacher-dashboard-page .activity-panel {
		height: 100%;
		border-radius: 22px;
		padding: 22px;
		transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
	}

	.teacher-dashboard-page .summary-card:hover,
	.teacher-dashboard-page .activity-panel:hover {
		transform: translateY(-3px);
		box-shadow: 0 28px 70px rgba(15, 23, 42, .12);
		border-color: rgba(15, 118, 110, .35);
	}

	.teacher-dashboard-page .summary-icon {
		width: 50px;
		height: 50px;
		border-radius: 16px;
		background: var(--dash-primary-soft);
		color: var(--dash-primary);
		font-size: 20px;
	}

	.teacher-dashboard-page .summary-icon.blue { background: var(--dash-blue-soft); color: var(--dash-blue); }
	.teacher-dashboard-page .summary-icon.warning { background: var(--dash-warning-soft); color: var(--dash-warning); }
	.teacher-dashboard-page .summary-icon.danger { background: var(--dash-danger-soft); color: var(--dash-danger); }

	.teacher-dashboard-page .summary-card h6,
	.teacher-dashboard-page .activity-panel h5 {
		margin: 0;
		color: var(--dash-ink);
		font-weight: 900;
	}

	.teacher-dashboard-page .summary-count {
		margin: 16px 0 12px;
		color: var(--dash-ink);
		font-size: 28px;
		font-weight: 900;
		line-height: 1;
	}

	.teacher-dashboard-page .summary-count span {
		font-size: 14px;
		color: var(--dash-muted);
		font-weight: 800;
	}

	.teacher-dashboard-page .dashboard-tag-list {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}

	.teacher-dashboard-page .dashboard-tag {
		padding: 7px 10px;
		border-radius: 999px;
		background: #f8fafc;
		border: 1px solid rgba(148, 163, 184, .25);
		color: var(--dash-muted);
		font-size: 12px;
		font-weight: 800;
	}

	.teacher-dashboard-page .pending-line {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		padding: 10px 0;
		border-bottom: 1px solid rgba(148, 163, 184, .18);
	}

	.teacher-dashboard-page .pending-line:last-child {
		border-bottom: 0;
	}

	.teacher-dashboard-page .activity-list {
		margin: 18px 0 0;
		padding: 0;
		list-style: none;
	}

	.teacher-dashboard-page .activity-list li {
		display: flex;
		align-items: flex-start;
		gap: 12px;
		padding: 12px 0;
		border-bottom: 1px solid rgba(148, 163, 184, .18);
		color: var(--dash-ink);
		font-weight: 700;
	}

	.teacher-dashboard-page .activity-list li:last-child {
		border-bottom: 0;
	}

	.teacher-dashboard-page .timeline-icon {
		width: 28px;
		height: 28px;
		border-radius: 10px;
		background: var(--dash-primary-soft);
		color: var(--dash-primary);
		font-size: 12px;
		flex: 0 0 auto;
	}

	.teacher-dashboard-page .timeline-icon.upcoming {
		background: var(--dash-blue-soft);
		color: var(--dash-blue);
	}

	@media (max-width: 767.98px) {
		.teacher-dashboard-page .dashboard-welcome,
		.teacher-dashboard-page .summary-card,
		.teacher-dashboard-page .activity-panel {
			padding: 20px;
			border-radius: 20px;
		}

		.teacher-dashboard-page .teacher-avatar {
			width: 94px;
			height: 94px;
			border-radius: 24px;
		}

		.teacher-dashboard-page .teacher-avatar img {
			width: 82px;
			height: 82px;
			border-radius: 20px;
		}

		.teacher-dashboard-page .dashboard-welcome h3 {
			font-size: 23px;
		}
	}
</style>

<div class="teacher-dashboard-page">
	<!-- Dashboard welcome section: summarizes teacher identity and academic period. -->
	<section class="dashboard-welcome">
		<div class="row align-items-center row-gap-4">
			<div class="col-lg-8">
				<div class="d-flex align-items-center flex-wrap flex-sm-nowrap gap-3">
					<span class="teacher-avatar"><img src="<?php echo $teacher['profile_picture']; ?>" alt="Teacher profile picture"></span>
					<div>
						<span class="welcome-chip"><i class="fa-solid fa-chalkboard-user"></i> Teacher Dashboard</span>
						<h3>Welcome back, <?php echo $teacher['full_name']; ?></h3>
						<p class="welcome-meta mb-1">Staff ID: <?php echo $teacher['staff_id']; ?></p>
						<p class="welcome-meta mb-0">Department: <?php echo $teacher['department']; ?> | Session: <?php echo $teacher['academic_session']; ?> | <?php echo $teacher['term']; ?></p>
					</div>
				</div>
			</div>
			<div class="col-lg-4 text-lg-end">
				<a href="profile.php" class="btn btn-success rounded-pill"><i class="fa-solid fa-user me-2"></i>View Profile</a>
			</div>
		</div>
	</section>

	<!-- Summary cards: quick academic activity metrics for the teacher. -->
	<section class="row g-3 mb-4" aria-label="Teacher dashboard summary">
		<div class="col-md-6 col-xl-3">
			<div class="summary-card">
				<div class="d-flex align-items-center justify-content-between"><h6>Assigned Classes</h6><span class="summary-icon"><i class="fa-solid fa-school"></i></span></div>
				<div class="summary-count"><?php echo count($assignedClasses); ?> <span>Classes</span></div>
				<div class="dashboard-tag-list"><?php foreach ($assignedClasses as $class): ?><span class="dashboard-tag"><?php echo $class; ?></span><?php endforeach; ?></div>
			</div>
		</div>
		<div class="col-md-6 col-xl-3">
			<div class="summary-card">
				<div class="d-flex align-items-center justify-content-between"><h6>Subjects</h6><span class="summary-icon blue"><i class="fa-solid fa-book-open-reader"></i></span></div>
				<div class="summary-count"><?php echo count($subjects); ?> <span>Subjects</span></div>
				<div class="dashboard-tag-list"><?php foreach ($subjects as $subject): ?><span class="dashboard-tag"><?php echo $subject; ?></span><?php endforeach; ?></div>
			</div>
		</div>
		<div class="col-md-6 col-xl-3">
			<div class="summary-card">
				<div class="d-flex align-items-center justify-content-between"><h6>Students</h6><span class="summary-icon warning"><i class="fa-solid fa-users"></i></span></div>
				<div class="summary-count"><?php echo $totalStudents; ?> <span>Students</span></div>
				<p class="text-muted mb-0">Total learners across assigned classes.</p>
			</div>
		</div>
		<div class="col-md-6 col-xl-3">
			<div class="summary-card">
				<div class="d-flex align-items-center justify-content-between"><h6>Pending Tasks</h6><span class="summary-icon danger"><i class="fa-solid fa-list-check"></i></span></div>
				<?php foreach ($pendingTasks as $task): ?>
					<div class="pending-line"><span><i class="fa-solid <?php echo $task['icon']; ?> me-2"></i><?php echo $task['label']; ?></span><strong><?php echo $task['count']; ?></strong></div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Activity panels: recent completed actions and upcoming academic duties. -->
	<section class="row g-3">
		<div class="col-lg-6">
			<div class="activity-panel">
				<h5><i class="fa-solid fa-clock-rotate-left me-2 text-success"></i>Recent Activities</h5>
				<ul class="activity-list">
					<?php foreach ($recentActivities as $activity): ?><li><span class="timeline-icon"><i class="fa-solid fa-check"></i></span><?php echo $activity; ?></li><?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="activity-panel">
				<h5><i class="fa-solid fa-calendar-check me-2 text-primary"></i>Upcoming Tasks</h5>
				<ul class="activity-list">
					<?php foreach ($upcomingTasks as $task): ?><li><span class="timeline-icon upcoming"><i class="fa-solid fa-arrow-right"></i></span><?php echo $task; ?></li><?php endforeach; ?>
				</ul>
			</div>
		</div>
	</section>
</div>

</div>
</div>

<?php require_once('includes/footer.php'); ?>
