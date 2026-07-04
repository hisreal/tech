<?php require_once('includes/header.php'); ?>
<?php require_once('includes/teacher-data.php'); ?>
<?php
$teacher = sms_admin_teacher_find($teacherRecords, sms_admin_teacher_selected_id());
$profileSummaryCards = [
    ['title' => 'Total Students', 'value' => $teacher['total_students'], 'icon' => 'fa-users', 'color' => 'success'],
    ['title' => 'Classes Assigned', 'value' => count($teacher['classes']), 'icon' => 'fa-school', 'color' => 'blue'],
    ['title' => 'Subjects Taught', 'value' => count($teacher['subjects']), 'icon' => 'fa-book-open', 'color' => 'warning'],
    ['title' => 'Attendance Percentage', 'value' => $teacher['attendance_rate'] . '%', 'icon' => 'fa-calendar-check', 'color' => 'success'],
];
?>
<?php require_once('includes/teacher-module-styles.php'); ?>

<div class="admin-teacher-module">
    <!-- Teacher profile header. -->
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Teacher Management <i class="fa-solid fa-angle-right mx-1"></i> Teacher Profile</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <img class="profile-photo" src="<?php echo sms_e($teacher['passport']); ?>" alt="Teacher passport">
                <div>
                    <span class="module-kicker"><i class="fa-solid fa-id-card"></i> Teacher Profile</span>
                    <h3 class="mt-3 mb-1"><?php echo sms_e($teacher['full_name']); ?></h3>
                    <p class="text-muted fw-bold mb-0"><?php echo sms_e($teacher['staff_id']); ?> | <?php echo sms_e($teacher['department']); ?></p>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="module-btn btn-primary-soft" href="edit-teacher.php?teacher_id=<?php echo sms_e($teacher['teacher_id']); ?>"><i class="fa-solid fa-pen"></i> Edit Profile</a>
                <a class="module-btn btn-outline-soft" href="../teacher/change-password.php"><i class="fa-solid fa-key"></i> Change Password</a>
            </div>
        </div>
    </section>

    <!-- Quick teacher statistics rendered with the shared statistics-card component. -->
    <section class="row g-3 mb-4" aria-label="Teacher profile summary cards">
        <?php foreach ($profileSummaryCards as $card): ?>
            <div class="col-sm-6 col-xl-3">
                <?php sms_render_component('statistics-card', $card); ?>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Personal information. -->
    <section class="module-card">
        <h4>Personal Information</h4>
        <div class="info-grid">
            <div><label>Full Name</label><p><?php echo sms_e($teacher['full_name']); ?></p></div>
            <div><label>Staff ID</label><p><?php echo sms_e($teacher['staff_id']); ?></p></div>
            <div><label>Gender</label><p><?php echo sms_e($teacher['gender']); ?></p></div>
            <div><label>Date of Birth</label><p><?php echo sms_e($teacher['date_of_birth']); ?></p></div>
            <div><label>Phone</label><p><?php echo sms_e($teacher['phone']); ?></p></div>
            <div><label>Email</label><p><?php echo sms_e($teacher['email']); ?></p></div>
            <div class="full"><label>Address</label><p><?php echo sms_e($teacher['address']); ?></p></div>
        </div>
    </section>

    <!-- Professional information. -->
    <section class="module-card">
        <h4>Professional Information</h4>
        <div class="info-grid">
            <div><label>Department</label><p><?php echo sms_e($teacher['department']); ?></p></div>
            <div><label>Designation</label><p><?php echo sms_e($teacher['designation']); ?></p></div>
            <div><label>Qualification</label><p><?php echo sms_e($teacher['qualification']); ?></p></div>
            <div><label>Employment Date</label><p><?php echo sms_e($teacher['employment_date']); ?></p></div>
            <div><label>Employment Status</label><p><span class="status-badge"><?php echo sms_e($teacher['status']); ?></span></p></div>
            <div><label>Experience</label><p><?php echo sms_e($teacher['experience']); ?> Years</p></div>
        </div>
    </section>

    <!-- Assigned subjects and classes moved from Teachers table actions. -->
    <section class="row g-3">
        <div class="col-lg-6">
            <div class="module-card h-100">
                <h4>Assigned Subjects</h4>
                <p class="text-muted fw-bold">Subjects Taught: <?php echo count($teacher['subjects']); ?></p>
                <div class="chip-list"><?php foreach ($teacher['subjects'] as $subject): ?><span class="chip"><?php echo sms_e($subject); ?></span><?php endforeach; ?></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="module-card h-100">
                <h4>Assigned Classes</h4>
                <p class="text-muted fw-bold">Classes Assigned: <?php echo count($teacher['classes']); ?></p>
                <div class="chip-list"><?php foreach ($teacher['classes'] as $class): ?><span class="chip"><?php echo sms_e($class); ?></span><?php endforeach; ?></div>
            </div>
        </div>
    </section>

    <!-- Attendance and timetable summary. -->
    <section class="row g-3">
        <div class="col-lg-4">
            <div class="module-card h-100">
                <h4>Attendance Summary</h4>
                <div class="info-grid" style="grid-template-columns:1fr;">
                    <div><label>Attendance Percentage</label><p><?php echo sms_e($teacher['attendance_rate']); ?>%</p></div>
                    <div><label>Days Present</label><p>48</p></div>
                    <div><label>Days Absent</label><p>2</p></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="module-card h-100">
                <h4>Timetable Summary</h4>
                <div class="table-shell">
                    <table class="table">
                        <thead><tr><th>Day</th><th>Time</th><th>Class</th><th>Subject</th><th>Venue</th></tr></thead>
                        <tbody>
                            <?php foreach (array_slice($teacherTimetable, 0, 3) as $row): ?>
                                <tr><td><?php echo sms_e($row['day']); ?></td><td><?php echo sms_e($row['time']); ?></td><td><?php echo sms_e($row['class']); ?></td><td><?php echo sms_e($row['subject']); ?></td><td><?php echo sms_e($row['venue']); ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>