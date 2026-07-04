<?php
/** Reusable Result Management page renderer. */
function sms_result_render_cards(array $cards): void
{
    echo '<section class="row g-3 mb-4">';
    foreach ($cards as $card) {
        echo '<div class="col-sm-6 col-xl-3">';
        sms_render_component('statistics-card', $card);
        echo '</div>';
    }
    echo '</section>';
}

function sms_result_render_badge(string $status): string
{
    return '<span class="status-badge ' . sms_e(sms_result_status_class($status)) . '"><i class="fa-solid fa-circle"></i> ' . sms_e($status) . '</span>';
}

function sms_result_render_exports(): void
{
    echo '<div class="d-flex flex-wrap gap-2"><button class="module-btn btn-outline-soft result-export" data-format="CSV" type="button"><i class="fa-solid fa-file-csv"></i> CSV</button><button class="module-btn btn-outline-soft result-export" data-format="Excel" type="button"><i class="fa-solid fa-file-excel"></i> Excel</button><button class="module-btn btn-outline-soft result-export" data-format="PDF" type="button"><i class="fa-solid fa-file-pdf"></i> PDF</button><button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button></div>';
}

function sms_result_render_filters(array $classes, array $subjects, array $statuses): void
{
    ?>
    <section class="module-card">
        <h4>Search & Filter</h4>
        <form class="result-filter-form">
            <div class="filter-grid">
                <div><label>Academic Session</label><select class="form-select"><option>2025/2026</option><option>2026/2027</option></select></div>
                <div><label>Term</label><select class="form-select"><option>First Term</option><option>Second Term</option><option>Third Term</option></select></div>
                <div><label>Class</label><select class="form-select"><option value="">All Classes</option><?php foreach ($classes as $class): ?><option><?php echo sms_e($class); ?></option><?php endforeach; ?></select></div>
                <div><label>Subject</label><select class="form-select"><option value="">All Subjects</option><?php foreach ($subjects as $subject): ?><option><?php echo sms_e($subject); ?></option><?php endforeach; ?></select></div>
                <div><label>Status</label><select class="form-select" data-filter="status"><option value="">All Statuses</option><?php foreach ($statuses as $status): ?><option><?php echo sms_e($status); ?></option><?php endforeach; ?></select></div>
                <div><label>Search</label><input class="form-control" data-filter="search" placeholder="Class, teacher, subject, student"></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-search"></i> Search</button><button class="module-btn btn-muted-soft" type="reset">Reset</button></div>
            </div>
        </form>
    </section>
    <?php
}

function sms_result_render_pagination(): void
{
    echo '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3"><div class="d-flex align-items-center gap-2"><span class="text-muted fw-bold">Records per page</span><select class="form-select" style="width:90px"><option>10</option><option>25</option><option>50</option><option>100</option></select></div><div class="d-flex gap-2"><a class="module-btn btn-muted-soft" href="#">Previous</a><a class="module-btn btn-primary-soft" href="#">1</a><a class="module-btn btn-muted-soft" href="#">Next</a></div></div>';
}

function sms_result_render_batch_table(array $items, string $title, string $description, array $actions): void
{
    ?>
    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4 class="mb-1"><?php echo sms_e($title); ?></h4><p class="text-muted mb-0"><?php echo sms_e($description); ?></p></div><?php sms_result_render_exports(); ?></div>
        <div class="table-shell"><table class="table result-table"><thead><tr><th>Session</th><th>Term</th><th>Class</th><th>Subject</th><th>Teacher</th><th>Students</th><th>Status</th><th>Average</th><th>Actions</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr data-status="<?php echo sms_e($item['status']); ?>"><td><?php echo sms_e($item['session']); ?></td><td><?php echo sms_e($item['term']); ?></td><td><?php echo sms_e($item['class']); ?></td><td><?php echo sms_e($item['subject']); ?></td><td><?php echo sms_e($item['teacher']); ?></td><td><?php echo sms_e($item['students']); ?></td><td><?php echo sms_result_render_badge($item['status']); ?></td><td><?php echo sms_e($item['average']); ?></td><td><div class="d-flex gap-1"><?php foreach ($actions as $action): ?><button class="action-btn result-action" data-action="<?php echo sms_e($action); ?>" title="<?php echo sms_e($action); ?>"><i class="fa-solid <?php echo sms_e(sms_result_action_icon($action)); ?>"></i></button><?php endforeach; ?></div></td></tr><?php endforeach; ?></tbody></table></div>
        <?php sms_result_render_pagination(); ?>
    </section>
    <?php
}

function sms_result_action_icon(string $action): string
{
    return match ($action) {
        'Approve' => 'fa-check-double', 'Publish' => 'fa-bullhorn', 'Lock' => 'fa-lock', 'Unlock' => 'fa-lock-open', 'Generate' => 'fa-gears', 'Print' => 'fa-print', 'Download' => 'fa-download', 'Edit' => 'fa-pen', 'Delete' => 'fa-trash', default => 'fa-eye',
    };
}

function sms_result_render_settings_table(array $items, array $columns, string $title, string $description): void
{
    ?>
    <section class="module-card"><div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4 class="mb-1"><?php echo sms_e($title); ?></h4><p class="text-muted mb-0"><?php echo sms_e($description); ?></p></div><button class="module-btn btn-primary-soft" data-bs-toggle="modal" data-bs-target="#resultSettingsModal" type="button"><i class="fa-solid fa-plus"></i> Add New</button></div><div class="table-shell"><table class="table result-table"><thead><tr><?php foreach ($columns as $label => $key): ?><th><?php echo sms_e($label); ?></th><?php endforeach; ?><th>Actions</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><?php foreach ($columns as $label => $key): ?><td><?php echo $key === 'status' ? sms_result_render_badge($item[$key]) : sms_e($item[$key]); ?></td><?php endforeach; ?><td><div class="d-flex gap-1"><button class="action-btn" title="Edit"><i class="fa-solid fa-pen"></i></button><button class="action-btn" title="Delete"><i class="fa-solid fa-trash"></i></button></div></td></tr><?php endforeach; ?></tbody></table></div></section>
    <div class="modal fade" id="resultSettingsModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" id="resultSettingsForm" method="post" action="result-settings-save.php"><div class="modal-header"><h5 class="modal-title">Add / Edit Setting</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><p class="text-muted fw-bold">This form is prepared for future backend validation and database persistence.</p><div class="form-grid"><div><label>Range / Category</label><input class="form-control" name="range" required></div><div><label>Grade / Label</label><input class="form-control" name="label" required></div><div class="full"><label>Remark / Message</label><textarea class="form-control" name="message"></textarea></div></div></div><div class="modal-footer"><button class="module-btn btn-muted-soft" type="button" data-bs-dismiss="modal">Cancel</button><button class="module-btn btn-primary-soft" type="submit">Save Setting</button></div></form></div></div>
    <?php
}

function sms_result_render_common_script(): void
{
    ?>
    <script>
    /* Shared Result Management frontend behavior for filtering and placeholder workflow actions. */
    (function(){
        document.querySelectorAll('.result-export').forEach(function(button){button.addEventListener('click',function(){alert(button.dataset.format+' export placeholder ready for future backend integration.');});});
        document.querySelectorAll('.result-action').forEach(function(button){button.addEventListener('click',function(){alert(button.dataset.action+' workflow placeholder. Future backend should validate permissions, update result status, and log the action.');});});
        document.querySelectorAll('.result-filter-form').forEach(function(form){
            form.addEventListener('submit',function(event){event.preventDefault();var search=(form.querySelector('[data-filter="search"]')||{}).value||'';var status=(form.querySelector('[data-filter="status"]')||{}).value||'';search=search.toLowerCase();document.querySelectorAll('.result-table tbody tr').forEach(function(row){var okSearch=!search||row.textContent.toLowerCase().indexOf(search)!==-1;var okStatus=!status||row.dataset.status===status;row.style.display=okSearch&&okStatus?'':'none';});});
            form.addEventListener('reset',function(){setTimeout(function(){document.querySelectorAll('.result-table tbody tr').forEach(function(row){row.style.display='';});},0);});
        });
        var settingsForm=document.getElementById('resultSettingsForm');if(settingsForm){settingsForm.addEventListener('submit',function(e){e.preventDefault();if(!this.checkValidity()){this.reportValidity();return;}alert('Setting saved in placeholder mode. Future endpoint: '+this.action);});}
    })();
    </script>
    <?php
}
?>