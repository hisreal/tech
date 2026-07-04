<?php
/** Reusable Timetable Management helpers. */
function sms_timetable_render_cards(array $cards): void
{
    echo '<section class="row g-3 mb-4">';
    foreach ($cards as $card) {
        echo '<div class="col-sm-6 col-xl-3">';
        sms_render_component('statistics-card', $card);
        echo '</div>';
    }
    echo '</section>';
}

function sms_timetable_badge(string $status): string
{
    return '<span class="status-badge ' . sms_e(sms_timetable_status_class($status)) . '"><i class="fa-solid fa-circle"></i> ' . sms_e($status) . '</span>';
}

function sms_timetable_exports(): void
{
    echo '<div class="d-flex flex-wrap gap-2"><button class="module-btn btn-outline-soft tt-export" data-format="PDF" type="button"><i class="fa-solid fa-file-pdf"></i> PDF</button><button class="module-btn btn-outline-soft tt-export" data-format="Excel" type="button"><i class="fa-solid fa-file-excel"></i> Excel</button><button class="module-btn btn-muted-soft" onclick="window.print()" type="button"><i class="fa-solid fa-print"></i> Print</button></div>';
}

function sms_timetable_pagination(): void
{
    echo '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3"><div class="d-flex align-items-center gap-2"><span class="text-muted fw-bold">Records per page</span><select class="form-select" style="width:90px"><option>10</option><option>25</option><option>50</option></select></div><div class="d-flex gap-2"><a class="module-btn btn-muted-soft" href="#">Previous</a><a class="module-btn btn-primary-soft" href="#">1</a><a class="module-btn btn-muted-soft" href="#">Next</a></div></div>';
}

function sms_timetable_common_script(): void
{
    ?>
    <script>
    /* Shared Timetable Management frontend behavior for tabs, placeholders, and confirmation workflows. */
    (function(){
        document.querySelectorAll('.view-tab').forEach(function(tab){tab.addEventListener('click',function(){document.querySelectorAll('.view-tab').forEach(function(t){t.classList.remove('active')});tab.classList.add('active');document.querySelectorAll('[data-tt-view]').forEach(function(panel){panel.style.display=panel.dataset.ttView===tab.dataset.target?'':'none';});});});
        document.querySelectorAll('.tt-export').forEach(function(button){button.addEventListener('click',function(){alert(button.dataset.format+' export placeholder ready for future backend integration.');});});
        document.querySelectorAll('.tt-action').forEach(function(button){button.addEventListener('click',function(){var action=button.dataset.action||'Action';if(['Delete','Publish','Unpublish'].indexOf(action)>-1&&!confirm(action+' this timetable record?')){return;}alert(action+' placeholder. Future backend should validate permissions, detect conflicts, update timetable_entries, and write audit logs.');});});
        document.querySelectorAll('form[data-placeholder-form]').forEach(function(form){form.addEventListener('submit',function(e){e.preventDefault();alert('Saved in placeholder mode. Future backend should validate IDs and prevent overlapping time slots.');});});
    })();
    </script>
    <?php
}
?>