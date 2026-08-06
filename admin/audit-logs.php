<?php require_once('includes/header.php'); ?>
<?php require_once('includes/attendance-module-styles.php'); ?>
<?php
use App\Services\AuditLogService;

$auditService = new AuditLogService();

$filters = [
    'date_from' => trim((string) ($_GET['date_from'] ?? '')),
    'date_to' => trim((string) ($_GET['date_to'] ?? '')),
    'role' => trim((string) ($_GET['role'] ?? '')),
    'module' => trim((string) ($_GET['module'] ?? '')),
    'action' => trim((string) ($_GET['action'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
    'search' => trim((string) ($_GET['search'] ?? '')),
];

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = (int) ($_GET['per_page'] ?? 20);
$perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 20;

$result = $auditService->list($filters, $page, $perPage);
$auditLogs = $result['data'];
$meta = $result['meta'];
$stats = $auditService->stats();

$roles = $auditService->rolesForSelect();
$modules = $auditService->modulesForSelect();
$actions = $auditService->actionsForSelect($filters['module'] !== '' ? $filters['module'] : null);
$statuses = ['success' => 'Success', 'failed' => 'Failed', 'warning' => 'Warning'];

function sms_audit_query(array $overrides = []): string
{
    return http_build_query(array_merge($_GET, $overrides));
}

function sms_audit_status_class(string $status): string { return 'status-' . strtolower($status); }
function sms_audit_badge(string $status): string { return '<span class="status-badge ' . sms_e(sms_audit_status_class($status)) . '"><i class="fa-solid fa-circle"></i> ' . sms_e(ucfirst($status)) . '</span>'; }
function sms_audit_cards(array $cards): void { echo '<section class="row g-3 mb-4">'; foreach ($cards as $card) { echo '<div class="col-sm-6 col-xl-3">'; sms_render_component('statistics-card', $card); echo '</div>'; } echo '</section>'; }

$cards = [
    ['title' => 'Total Activities', 'value' => number_format($stats['total']), 'description' => 'All tracked activities', 'icon' => 'fa-list-check', 'color' => 'success'],
    ['title' => "Today's Activities", 'value' => number_format($stats['today']), 'description' => 'Activities recorded today', 'icon' => 'fa-calendar-day', 'color' => 'blue'],
    ['title' => 'Failed Login Attempts', 'value' => number_format($stats['failed_logins']), 'description' => 'Security events to review', 'icon' => 'fa-triangle-exclamation', 'color' => 'danger'],
    ['title' => 'Active Users (30d)', 'value' => number_format($stats['active_users']), 'description' => 'Distinct users seen in logs', 'icon' => 'fa-users', 'color' => 'warning'],
];
?>
<style>
.admin-audit-module .status-success{background:rgba(22,163,74,.12);color:#15803d}.admin-audit-module .status-failed{background:rgba(220,38,38,.1);color:#b91c1c}.admin-audit-module .status-warning{background:rgba(245,158,11,.14);color:#b45309}.admin-audit-module .audit-toggle{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px}.admin-audit-module .audit-toggle button{border:1px solid var(--at-border);background:#fff;color:var(--at-dark);border-radius:999px;padding:10px 15px;font-weight:900}.admin-audit-module .audit-toggle button.active{background:var(--at-primary);color:#fff}.admin-audit-module .timeline{position:relative;padding-left:24px}.admin-audit-module .timeline:before{content:"";position:absolute;left:9px;top:0;bottom:0;width:2px;background:rgba(15,118,110,.22)}.admin-audit-module .timeline-item{position:relative;padding:0 0 18px 20px}.admin-audit-module .timeline-item:before{content:"";position:absolute;left:-20px;top:4px;width:18px;height:18px;border-radius:50%;background:#0f766e;border:4px solid #d1fae5}.admin-audit-module .timeline-card{border:1px solid rgba(15,118,110,.16);border-radius:18px;padding:16px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.06)}.admin-audit-module .empty-state{text-align:center;padding:42px;border:1px dashed rgba(15,118,110,.25);border-radius:22px;background:#f8fafc}.admin-audit-module .empty-state i{font-size:44px;color:#0f766e;margin-bottom:12px}
</style>
<div class="admin-attendance-module admin-audit-module">
    <section class="module-hero"><div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Audit Logs</div><div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><span class="module-kicker"><i class="fa-solid fa-shield-halved"></i> Audit Logs</span><h3 class="mt-3 mb-2">Audit Logs</h3><p class="text-muted mb-0">Real, automatic activity trail across the School Management System - every login, logout, CRUD action, profile update, password change, settings change, result, payment, and attendance record.</p></div><button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button></div></section>
    <?php sms_audit_cards($cards); ?>
    <section class="module-card">
        <h4>Search & Filter</h4>
        <form method="get" id="auditFilterForm">
            <div class="filter-grid">
                <div><label>Date From</label><input class="form-control" type="date" name="date_from" value="<?php echo sms_e($filters['date_from']); ?>"></div>
                <div><label>Date To</label><input class="form-control" type="date" name="date_to" value="<?php echo sms_e($filters['date_to']); ?>"></div>
                <div><label>User Role</label><select class="form-select" name="role"><option value="">All Roles</option><?php foreach ($roles as $slug => $label): ?><option value="<?php echo sms_e($slug); ?>" <?php echo $filters['role'] === $slug ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                <div><label>User / Description</label><input class="form-control" name="search" value="<?php echo sms_e($filters['search']); ?>" placeholder="Search user or activity"></div>
                <div><label>Module</label><select class="form-select" name="module"><option value="">All Modules</option><?php foreach ($modules as $module): ?><option value="<?php echo sms_e($module); ?>" <?php echo $filters['module'] === $module ? 'selected' : ''; ?>><?php echo sms_e(ucfirst($module)); ?></option><?php endforeach; ?></select></div>
                <div><label>Action Type</label><select class="form-select" name="action"><option value="">All Actions</option><?php foreach ($actions as $action): ?><option value="<?php echo sms_e($action); ?>" <?php echo $filters['action'] === $action ? 'selected' : ''; ?>><?php echo sms_e(ucwords(str_replace(['.', '_'], ' ', $action))); ?></option><?php endforeach; ?></select></div>
                <div><label>Status</label><select class="form-select" name="status"><option value="">All Statuses</option><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $filters['status'] === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-search"></i> Search</button><a class="module-btn btn-muted-soft" href="audit-logs.php">Reset</a></div>
            </div>
        </form>
    </section>
    <section class="module-card"><div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4 class="mb-1">Audit Trail</h4><p class="text-muted mb-0">Audit logs are read-only. No edit or delete actions are available. <?php echo number_format($meta['total']); ?> record(s) match your filters.</p></div><div class="d-flex flex-wrap gap-2"><a class="module-btn btn-outline-soft" href="audit-log-export.php?<?php echo sms_e(sms_audit_query(['format' => 'pdf'])); ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a><a class="module-btn btn-outline-soft" href="audit-log-export.php?<?php echo sms_e(sms_audit_query(['format' => 'excel'])); ?>"><i class="fa-solid fa-file-excel"></i> Excel</a><a class="module-btn btn-outline-soft" href="audit-log-export.php?<?php echo sms_e(sms_audit_query(['format' => 'csv'])); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a><button class="module-btn btn-muted-soft" onclick="window.print()" type="button"><i class="fa-solid fa-print"></i> Print</button></div></div><div class="audit-toggle"><button class="active" data-view="table" type="button"><i class="fa-solid fa-table-list"></i> Table View</button><button data-view="timeline" type="button"><i class="fa-solid fa-timeline"></i> Timeline View</button></div>
        <?php if (!$auditLogs): ?>
        <div class="empty-state"><i class="fa-solid fa-folder-open"></i><h4>No audit logs found for the selected filters.</h4><p class="text-muted mb-0">Try adjusting your search or clearing filters.</p></div>
        <?php else: ?>
        <div id="auditTableView"><div class="table-shell"><table class="table" id="auditTable"><thead><tr><th>Date & Time</th><th>User</th><th>Role</th><th>Module</th><th>Action</th><th>Description</th><th>IP Address</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($auditLogs as $log): ?><tr><td><?php echo sms_e(date('Y-m-d h:i A', strtotime((string) $log['created_at']))); ?></td><td><?php echo sms_e($log['actor_name']); ?></td><td><?php echo sms_e($log['actor_role_label']); ?></td><td><?php echo sms_e(ucfirst((string) $log['module'])); ?></td><td><?php echo sms_e(ucwords(str_replace(['.', '_'], ' ', (string) $log['action']))); ?></td><td><?php echo sms_e($log['description']); ?></td><td><?php echo sms_e((string) ($log['ip_address'] ?? '-')); ?></td><td><?php echo sms_audit_badge((string) $log['status']); ?></td><td><button class="action-btn audit-details" data-bs-toggle="modal" data-bs-target="#auditDetailsModal" data-log='<?php echo sms_e(json_encode([
                'user' => $log['actor_name'], 'role' => $log['actor_role_label'], 'created_at' => date('Y-m-d h:i A', strtotime((string) $log['created_at'])),
                'module' => ucfirst((string) $log['module']), 'action' => ucwords(str_replace(['.', '_'], ' ', (string) $log['action'])),
                'ip' => (string) ($log['ip_address'] ?? '-'), 'browser' => $log['browser'], 'os' => $log['os'], 'device' => $log['device'],
                'status' => ucfirst((string) $log['status']), 'description' => $log['description'],
                'old_values' => $log['old_values'] ? json_decode((string) $log['old_values'], true) : null,
                'new_values' => $log['new_values'] ? json_decode((string) $log['new_values'], true) : null,
            ])); ?>' title="View Details"><i class="fa-solid fa-eye"></i></button></td></tr><?php endforeach; ?></tbody></table></div></div>
        <div id="auditTimelineView" style="display:none"><div class="timeline"><?php foreach ($auditLogs as $log): ?><div class="timeline-item"><div class="timeline-card"><div class="d-flex justify-content-between flex-wrap gap-2"><strong><?php echo sms_e(ucwords(str_replace(['.', '_'], ' ', (string) $log['action']))); ?> - <?php echo sms_e(ucfirst((string) $log['module'])); ?></strong><?php echo sms_audit_badge((string) $log['status']); ?></div><p class="mb-1 mt-2"><?php echo sms_e($log['description']); ?></p><small class="text-muted"><?php echo sms_e(date('Y-m-d h:i A', strtotime((string) $log['created_at']))); ?> by <?php echo sms_e($log['actor_name']); ?> (<?php echo sms_e($log['actor_role_label']); ?>)</small></div></div><?php endforeach; ?></div></div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
            <div class="d-flex align-items-center gap-2"><span class="text-muted fw-bold">Records per page</span><select class="form-select" style="width:90px" data-base="audit-logs.php?<?php echo sms_e(sms_audit_query(['page' => 1])); ?>" onchange="window.location.href=this.dataset.base+'&per_page='+this.value"><?php foreach ([10, 25, 50, 100] as $opt): ?><option value="<?php echo $opt; ?>" <?php echo $perPage === $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option><?php endforeach; ?></select></div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="module-btn <?php echo $meta['page'] <= 1 ? 'btn-muted-soft disabled' : 'btn-muted-soft'; ?>" href="audit-logs.php?<?php echo sms_e(sms_audit_query(['page' => max(1, $meta['page'] - 1)])); ?>">Previous</a>
                <span class="module-btn btn-primary-soft"><?php echo (int) $meta['page']; ?> of <?php echo (int) $meta['last_page']; ?></span>
                <a class="module-btn btn-muted-soft" href="audit-logs.php?<?php echo sms_e(sms_audit_query(['page' => min((int) $meta['last_page'], $meta['page'] + 1)])); ?>">Next</a>
            </div>
        </div>
        <?php endif; ?>
    </section>
    <div class="modal fade" id="auditDetailsModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Audit Log Details</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="form-grid"><div><label>User Name</label><input class="form-control" id="auditUser" readonly></div><div><label>User Role</label><input class="form-control" id="auditRole" readonly></div><div><label>Date & Time</label><input class="form-control" id="auditDate" readonly></div><div><label>Module</label><input class="form-control" id="auditModule" readonly></div><div><label>Action</label><input class="form-control" id="auditAction" readonly></div><div><label>IP Address</label><input class="form-control" id="auditIp" readonly></div><div><label>Browser</label><input class="form-control" id="auditBrowser" readonly></div><div><label>Operating System</label><input class="form-control" id="auditOs" readonly></div><div><label>Device Type</label><input class="form-control" id="auditDevice" readonly></div><div><label>Status</label><input class="form-control" id="auditStatus" readonly></div><div class="full"><label>Detailed Description</label><textarea class="form-control" id="auditDescription" readonly rows="2"></textarea></div><div class="full"><label>Before / After Values</label><textarea class="form-control" id="auditValues" readonly rows="6"></textarea></div></div></div><div class="modal-footer"><button class="module-btn btn-muted-soft" type="button" data-bs-dismiss="modal">Close</button></div></div></div></div>
</div></div></div>
<script>
(function(){
    var tableView=document.getElementById('auditTableView'), timelineView=document.getElementById('auditTimelineView');
    document.querySelectorAll('.audit-toggle button').forEach(function(button){button.addEventListener('click',function(){document.querySelectorAll('.audit-toggle button').forEach(function(b){b.classList.remove('active')});button.classList.add('active');if(tableView){tableView.style.display=button.dataset.view==='table'?'':'none';}if(timelineView){timelineView.style.display=button.dataset.view==='timeline'?'':'none';}});});
    document.querySelectorAll('.audit-details').forEach(function(button){button.addEventListener('click',function(){
        var log=JSON.parse(button.dataset.log);
        auditUser.value=log.user;auditRole.value=log.role;auditDate.value=log.created_at;auditModule.value=log.module;auditAction.value=log.action;auditIp.value=log.ip;auditBrowser.value=log.browser;auditOs.value=log.os;auditDevice.value=log.device;auditStatus.value=log.status;auditDescription.value=log.description;
        var values={};
        if(log.old_values){values.before=log.old_values;}
        if(log.new_values){values.after=log.new_values;}
        auditValues.value=Object.keys(values).length?JSON.stringify(values,null,2):'No field-level changes recorded for this activity.';
    });});
})();
</script>
<?php require_once('includes/footer.php'); ?>
