<!DOCTYPE html> 
<html lang="en">
	
<head>
	
		<!-- Meta Tags -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Dreams LMS is a powerful Learning Management System template designed for educators, training institutions, and businesses. Manage courses, track student progress, conduct virtual classes, and enhance e-learning experiences with an intuitive and feature-rich platform.">
		<meta name="keywords" content="LMS template, Learning Management System, e-learning software, online course platform, student management, education portal, virtual classroom, training management system, course tracking, online education">
		<meta name="author" content="Dreams Technologies">
		<meta name="robots" content="index, follow">
		
		<title>Dreams LMS | Advanced Learning Management System Template</title>

		<!-- Favicon -->
		<link rel="shortcut icon" href="../assets/img/favicon.png"> 
		<link rel="apple-touch-icon" href="../assets/img/apple-icon.png">

		<!-- Theme Settings Js -->
		<script src="../assets/js/theme-script.js" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
		
		<!-- Fontawesome CSS -->
		<link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
		<link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">

		<!-- Feather CSS -->
		<link rel="stylesheet" href="../assets/css/feather.css?v=<?php echo filemtime('../assets/css/feather.css');?>">

		<!-- Iconsax CSS -->
		<link rel="stylesheet" href="../assets/css/iconsax.css?v=<?php echo filemtime('../assets/css/iconsax.css');?>">

		<!-- Main CSS -->
		<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo filemtime('../assets/css/style.css');?>">
		<link rel="stylesheet" href="../assets/css/student-sidebar.css?v=<?php echo filemtime('../assets/css/student-sidebar.css');?>">
		<link rel="stylesheet" href="../assets/css/student-quiz.css?v=<?php echo filemtime('../assets/css/student-quiz.css');?>">
		<link rel="stylesheet" href="../assets/css/student-portal.css?v=<?php echo filemtime('../assets/css/student-portal.css');?>">
	
	</head>
	<body class="student-dashboard-layout">
		<?php
			$currentPage = basename($_SERVER['PHP_SELF']);
		
			$studentNavItems = [
				['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'fa-solid fa-house', 'pages' => ['dashboard.php']],
				['label' => 'My Profile', 'href' => 'profile.php', 'icon' => 'fa-solid fa-user-graduate', 'pages' => ['profile.php', 'edit-profile.php']],
				['label' => 'Attendance Management', 'href' => 'attendance.php', 'icon' => 'fa-solid fa-calendar-check', 'pages' => ['attendance.php']],
				['label' => 'Results Management', 'href' => 'result-management.php', 'icon' => 'fa-solid fa-chart-line', 'pages' => ['result-management.php', 'check-result.php', 'report-card.html']],
				['label' => 'CBT Management', 'href' => 'cbt-management.php', 'icon' => 'fa-solid fa-laptop-code', 'pages' => ['cbt-management.php']],
				['label' => 'Timetable', 'href' => 'timetable.php', 'icon' => 'fa-solid fa-calendar-days', 'pages' => ['timetable.php']],
				['label' => 'Change Password', 'href' => 'change-password.php', 'icon' => 'fa-solid fa-key', 'pages' => ['change-password.php']],
			];
		?>
		<!-- Main Wrapper -->
		<div class="main-wrapper">
			<input type="checkbox" id="studentSidebarControl" class="student-sidebar-control" aria-hidden="true">

			<!-- Student mobile header: keeps menu controls accessible on tablets and phones. -->
			<header class="student-mobile-header">
				<a class="student-mobile-brand" href="dashboard.php" aria-label="Teacher dashboard">
					<img src="../assets/img/logo/school-logo.png" alt="Logo">
				</a>
				<label class="student-sidebar-toggle" for="studentSidebarControl" role="button" tabindex="0" aria-label="Open student navigation" aria-expanded="false">
					<i class="fa-solid fa-bars"></i>
				</label>
			</header>

			<!-- Student sidebar: fixed desktop navigation with profile summary and green school theme. -->
			<aside class="student-sidebar" id="studentSidebar" aria-label="Student dashboard navigation">
				<div class="student-sidebar-inner">
					<div class="student-sidebar-brand">
						<center><a href="dashboard.php">
							<img src="../assets/img/logo/school-logo.png" alt="Logo">
						</a></center>
						<label class="student-sidebar-close" for="studentSidebarControl" role="button" tabindex="0" aria-label="Close student navigation">
							<i class="fa-solid fa-xmark"></i>
						</label>
					</div>

					
					<nav class="student-sidebar-nav">
						<ul>
							<?php foreach ($studentNavItems as $item): ?>
								<?php $isActive = in_array($currentPage, $item['pages'], true); ?>
								<li>
									<a href="<?php echo $item['href']; ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
										<span class="student-nav-icon"><i class="<?php echo $item['icon']; ?>"></i></span>
										<span><?php echo $item['label']; ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</nav>

					<div class="student-sidebar-actions">
						<a href="javascript:void(0);" id="dark-mode-toggle" class="theme-toggle activate" aria-label="Enable dark mode">
							<i class="isax isax-sun-15"></i>
						</a>
						<a href="javascript:void(0);" id="light-mode-toggle" class="theme-toggle" aria-label="Enable light mode">
							<i class="isax isax-moon"></i>
						</a>
					</div>
				</div>
			</aside>
			<label class="student-sidebar-backdrop" for="studentSidebarControl" data-student-sidebar-close aria-label="Close student navigation"></label>

			<div class="content">
				<div class="container">





