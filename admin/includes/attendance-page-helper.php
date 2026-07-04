<?php
/** Reusable helpers for Admin Attendance Management pages. */
function sms_attendance_render_cards(array $cards): void
{
    echo '<section class="row g-3 mb-4">';
    foreach ($cards as $card) {
        echo '<div class="col-sm-6 col-xl-3">';
        sms_render_component('statistics-card', $card);
        echo '</div>';
    }
    echo '</section>';
}

function sms_attendance_render_hero(string $title, string $description, string $icon, string $trail): void
{
    ?>
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Attendance Management <i class="fa-solid fa-angle-right mx-1"></i> <?php echo sms_e($trail); ?></div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="<?php echo sms_e($icon); ?>"></i> <?php echo sms_e($trail); ?></span>
                <h3 class="mt-3 mb-2"><?php echo sms_e($title); ?></h3>
                <p class="text-muted mb-0"><?php echo sms_e($description); ?></p>
            </div>
            <button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
        </div>
    </section>
    <?php
}

function sms_attendance_render_exports(): void
{
    ?>
    <div class="d-flex flex-wrap gap-2">
        <button class="module-btn btn-outline-soft export-btn" data-format="CSV" type="button"><i class="fa-solid fa-file-csv"></i> CSV</button>
        <button class="module-btn btn-outline-soft export-btn" data-format="Excel" type="button"><i class="fa-solid fa-file-excel"></i> Excel</button>
        <button class="module-btn btn-outline-soft export-btn" data-format="PDF" type="button"><i class="fa-solid fa-file-pdf"></i> PDF</button>
        <button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
    </div>
    <?php
}

function sms_attendance_badge(string $status): string
{
    $icon = match (strtolower($status)) {
        'present' => 'fa-check',
        'absent' => 'fa-times',
        'late' => 'fa-clock',
        'leave' => 'fa-calendar-minus',
        default => 'fa-circle-info',
    };

    return '<span class="status-badge ' . sms_e(sms_attendance_status_class($status)) . '"><i class="fa-solid ' . $icon . '"></i> ' . sms_e($status) . '</span>';
}

function sms_attendance_render_pagination(): void
{
    ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
        <div class="d-flex align-items-center gap-2"><span class="text-muted fw-bold">Records per page</span><select class="form-select" style="width:90px"><option>10</option><option>25</option><option>50</option><option>100</option></select></div>
        <div class="d-flex gap-2"><a class="module-btn btn-muted-soft" href="#">Previous</a><a class="module-btn btn-primary-soft" href="#">1</a><a class="module-btn btn-muted-soft" href="#">Next</a></div>
    </div>
    <?php
}

function sms_attendance_render_edit_modal(): void
{
    ?>
    <div class="modal fade" id="editAttendanceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="attendanceEditForm" method="post" action="attendance-update.php">
                <div class="modal-header"><h5 class="modal-title">Edit Attendance Record</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="attendance_id" id="attendanceRecordId">
                    <input type="hidden" name="attendance_type" id="attendanceRecordType">
                    <p class="text-muted fw-bold">Future backend logic should verify admin permissions, update the selected record, and write an audit log entry.</p>
                    <label>Status</label>
                    <select class="form-select mb-3" name="status" id="attendanceRecordStatus" required>
                        <option>Present</option><option>Absent</option><option>Late</option><option>Excused</option><option>Leave</option>
                    </select>
                    <label>Remarks</label>
                    <textarea class="form-control" name="remarks" id="attendanceRecordRemarks" placeholder="Enter update remarks"></textarea>
                </div>
                <div class="modal-footer"><button class="module-btn btn-muted-soft" type="button" data-bs-dismiss="modal">Cancel</button><button class="module-btn btn-primary-soft" type="submit">Update Attendance</button></div>
            </form>
        </div>
    </div>
    <?php
}

function sms_attendance_render_common_script(): void
{
    ?>
    <script>
    /* Shared Attendance Management frontend behavior for filters, exports, and edit modal placeholders. */
    (function(){
        document.querySelectorAll('.export-btn').forEach(function(button){
            button.addEventListener('click', function(){ alert(button.dataset.format + ' export placeholder ready for future backend integration.'); });
        });
        document.querySelectorAll('.attendance-filter-form').forEach(function(form){
            form.addEventListener('submit', function(event){
                event.preventDefault();
                var search = (form.querySelector('[data-filter="search"]') || {}).value || '';
                var status = (form.querySelector('[data-filter="status"]') || {}).value || '';
                search = search.toLowerCase();
                document.querySelectorAll('.attendance-table tbody tr').forEach(function(row){
                    var matchesSearch = !search || row.textContent.toLowerCase().indexOf(search) !== -1;
                    var matchesStatus = !status || row.dataset.status === status;
                    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                });
            });
            form.addEventListener('reset', function(){ setTimeout(function(){ document.querySelectorAll('.attendance-table tbody tr').forEach(function(row){ row.style.display = ''; }); }, 0); });
        });
        document.querySelectorAll('.edit-attendance-btn').forEach(function(button){
            button.addEventListener('click', function(){
                var id = document.getElementById('attendanceRecordId');
                var type = document.getElementById('attendanceRecordType');
                var status = document.getElementById('attendanceRecordStatus');
                var remarks = document.getElementById('attendanceRecordRemarks');
                if(id){ id.value = button.dataset.id || ''; }
                if(type){ type.value = button.dataset.type || ''; }
                if(status){ status.value = button.dataset.status || 'Present'; }
                if(remarks){ remarks.value = button.dataset.remarks || ''; }
            });
        });
        var editForm = document.getElementById('attendanceEditForm');
        if(editForm){
            editForm.addEventListener('submit', function(event){
                event.preventDefault();
                if(!confirm('Save this attendance update?')){ return; }
                alert('Attendance update placeholder saved. Future endpoint: ' + editForm.action);
            });
        }
    })();
    </script>
    <?php
}
?>