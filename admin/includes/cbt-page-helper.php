<?php
/** Reusable CBT Management helpers. */
function sms_cbt_render_cards(array $cards): void
{
    echo '<section class="row g-3 mb-4">';
    foreach ($cards as $card) {
        echo '<div class="col-sm-6 col-xl-3">';
        sms_render_component('statistics-card', $card);
        echo '</div>';
    }
    echo '</section>';
}

function sms_cbt_render_badge(string $status): string
{
    return '<span class="status-badge ' . sms_e(sms_cbt_status_class($status)) . '"><i class="fa-solid fa-circle"></i> ' . sms_e($status) . '</span>';
}

function sms_cbt_render_exports(): void
{
    echo '<div class="d-flex flex-wrap gap-2"><button class="module-btn btn-outline-soft cbt-export" data-format="PDF" type="button"><i class="fa-solid fa-file-pdf"></i> PDF</button><button class="module-btn btn-outline-soft cbt-export" data-format="Excel" type="button"><i class="fa-solid fa-file-excel"></i> Excel</button><button class="module-btn btn-outline-soft cbt-export" data-format="CSV" type="button"><i class="fa-solid fa-file-csv"></i> CSV</button><button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button></div>';
}

function sms_cbt_render_pagination(): void
{
    echo '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3"><div class="d-flex align-items-center gap-2"><span class="text-muted fw-bold">Records per page</span><select class="form-select" style="width:90px"><option>10</option><option>25</option><option>50</option><option>100</option></select></div><div class="d-flex gap-2"><a class="module-btn btn-muted-soft" href="#">Previous</a><a class="module-btn btn-primary-soft" href="#">1</a><a class="module-btn btn-muted-soft" href="#">Next</a></div></div>';
}

function sms_cbt_action_icon(string $action): string
{
    return match ($action) {
        'View Exam', 'View Attempt', 'View Answers' => 'fa-eye', 'Edit Exam' => 'fa-pen', 'Publish Exam' => 'fa-bullhorn', 'Activate Exam' => 'fa-toggle-on', 'Deactivate Exam' => 'fa-toggle-off', 'Archive Exam' => 'fa-box-archive', 'Delete Exam' => 'fa-trash', 'Print Result' => 'fa-print', 'Export PDF' => 'fa-file-pdf', 'Export Excel' => 'fa-file-excel', default => 'fa-circle-info',
    };
}

function sms_cbt_render_common_script(): void
{
    ?>
    <script>
    /* Shared CBT Management frontend behavior for filters, exports, and workflow placeholders. */
    (function(){
        document.querySelectorAll('.cbt-export').forEach(function(button){button.addEventListener('click',function(){alert(button.dataset.format+' export placeholder ready for future backend integration.');});});
        document.querySelectorAll('.cbt-action').forEach(function(button){button.addEventListener('click',function(){alert(button.dataset.action+' placeholder. Future backend should validate permissions, update CBT records, and write audit logs.');});});
        document.querySelectorAll('.cbt-filter-form').forEach(function(form){
            form.addEventListener('submit',function(event){event.preventDefault();var search=(form.querySelector('[data-filter="search"]')||{}).value||'';var status=(form.querySelector('[data-filter="status"]')||{}).value||'';search=search.toLowerCase();document.querySelectorAll('.cbt-table tbody tr').forEach(function(row){var okSearch=!search||row.textContent.toLowerCase().indexOf(search)!==-1;var okStatus=!status||row.dataset.status===status;row.style.display=okSearch&&okStatus?'':'none';});});
            form.addEventListener('reset',function(){setTimeout(function(){document.querySelectorAll('.cbt-table tbody tr').forEach(function(row){row.style.display='';});},0);});
        });
        document.querySelectorAll('form[data-placeholder-form]').forEach(function(form){form.addEventListener('submit',function(e){e.preventDefault();alert('Saved in placeholder mode. Future endpoint should persist this with validation and audit logs.');});});
    })();
    </script>
    <?php
}
?>