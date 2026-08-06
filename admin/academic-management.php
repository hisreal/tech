<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AcademicService;

$academicService = new AcademicService();
$academicActiveTab = (string) ($_POST['tab'] ?? $_GET['tab'] ?? 'sessions');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $entity = (string) ($_POST['entity'] ?? '');
    $formAction = (string) ($_POST['form_action'] ?? 'save');
    $id = (int) ($_POST['id'] ?? 0);
    $actor = sms_current_user();

    if ($entity === 'calendar' && !isset($_POST['title']) && isset($_POST['name'])) {
        $_POST['title'] = $_POST['name'];
    }

    if (!sms_verify_csrf($_POST['_token'] ?? null)) {
        sms_flash_set('error', 'Your session expired. Please try again.');
    } else {
        $result = ['success' => false, 'message' => 'Unknown academic action.'];

        if ($formAction === 'delete') {
            $result = match ($entity) {
                'session' => $academicService->deleteSession($id, $actor),
                'term' => $academicService->deleteTerm($id, $actor),
                'class' => $academicService->deleteClass($id, $actor),
                'section' => $academicService->deleteSection($id, $actor),
                'department' => $academicService->deleteDepartment($id, $actor),
                'subject' => $academicService->deleteSubject($id, $actor),
                'calendar' => $academicService->deleteCalendarEvent($id, $actor),
                default => $result,
            };
        } elseif ($formAction === 'activate') {
            $result = match ($entity) {
                'session' => $academicService->activateSession($id, $actor),
                'term' => $academicService->activateTerm($id, $actor),
                default => $result,
            };
        } else {
            $result = match ($entity) {
                'session' => $academicService->saveSession($_POST, $id ?: null, $actor),
                'term' => $academicService->saveTerm($_POST, $id ?: null, $actor),
                'class' => $academicService->saveClass($_POST, $id ?: null, $actor),
                'section' => $academicService->saveSection($_POST, $id ?: null, $actor),
                'department' => $academicService->saveDepartment($_POST, $id ?: null, $actor),
                'subject' => $academicService->saveSubject($_POST, $id ?: null, array_map('intval', (array) ($_POST['classes'] ?? [])), $actor),
                'calendar' => $academicService->saveCalendarEvent($_POST, $id ?: null, $actor),
                default => $result,
            };
        }

        sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
        if (!empty($result['errors'])) {
            $_SESSION['_academic_errors'] = $result['errors'];
        }
    }

    header('Location: academic-management.php?tab=' . urlencode($academicActiveTab));
    exit;
}

$academicErrors = $_SESSION['_academic_errors'] ?? [];
unset($_SESSION['_academic_errors']);
$academicFlashMessages = sms_flash();

require_once('includes/header.php');
require_once('includes/academic-data.php');
require_once('includes/academic-module-styles.php');
?>
<?php
function sms_academic_cards_local(array $cards): void { echo '<section class="row g-3 mb-4">'; foreach ($cards as $card) { echo '<div class="col-sm-6 col-xl-2">'; sms_render_component('statistics-card', $card); echo '</div>'; } echo '</section>'; }
function sms_academic_badge_local(string $status): string { return '<span class="status-badge">' . sms_e($status) . '</span>'; }
function sms_academic_query(string $tab, string $prefix, array $params, int $page): string
{
    $query = ['tab' => $tab, $prefix . '_page' => $page];
    foreach ($params as $key => $value) {
        if ($key === 'page' || $value === '') { continue; }
        $query[$prefix . '_' . $key] = $value;
    }
    return 'academic-management.php?' . http_build_query($query) . '#' . $tab;
}
function sms_academic_pagination(string $tab, string $prefix, array $params, array $meta): void
{
    $lastPage = (int) $meta['last_page'];
    $page = (int) $meta['page'];
    echo '<div class="pagination-strip"><span class="text-muted fw-bold">' . (int) $meta['total'] . ' record(s) &middot; page ' . $page . ' of ' . $lastPage . '</span>';
    if ($lastPage > 1) {
        echo '<div class="d-flex gap-2 flex-wrap">';
        $prev = max(1, $page - 1);
        $next = min($lastPage, $page + 1);
        echo '<a class="page-link-soft' . ($page <= 1 ? ' disabled' : '') . '" href="' . sms_e(sms_academic_query($tab, $prefix, $params, $prev)) . '">Previous</a>';
        for ($p = 1; $p <= $lastPage; $p++) {
            echo '<a class="page-link-soft' . ($p === $page ? ' active' : '') . '" href="' . sms_e(sms_academic_query($tab, $prefix, $params, $p)) . '">' . $p . '</a>';
        }
        echo '<a class="page-link-soft' . ($page >= $lastPage ? ' disabled' : '') . '" href="' . sms_e(sms_academic_query($tab, $prefix, $params, $next)) . '">Next</a>';
        echo '</div>';
    }
    echo '</div>';
}
function sms_opt(string $value, string $current, ?string $label = null): string
{
    return '<option value="' . sms_e($value) . '" ' . ($value === $current ? 'selected' : '') . '>' . sms_e($label ?? ucfirst(str_replace('_', ' ', $value))) . '</option>';
}
$sections = [
    ['id' => 'sessions', 'label' => 'Academic Sessions', 'icon' => 'fa-calendar-days'],
    ['id' => 'terms', 'label' => 'Terms', 'icon' => 'fa-calendar-week'],
    ['id' => 'classes', 'label' => 'Classes', 'icon' => 'fa-school'],
    ['id' => 'sections', 'label' => 'Sections / Arms', 'icon' => 'fa-layer-group'],
    ['id' => 'departments', 'label' => 'Departments', 'icon' => 'fa-building-columns'],
    ['id' => 'subjects', 'label' => 'Subjects', 'icon' => 'fa-book-open'],
    ['id' => 'calendar', 'label' => 'School Calendar', 'icon' => 'fa-calendar-check'],
];
$activeSessionRow = current(array_filter($academicSessions, fn($item) => $item['status'] === 'Active')) ?: ($academicSessions[0] ?? ['name' => 'None']);
$activeTermRow = current(array_filter($academicTerms, fn($item) => $item['status'] === 'Active')) ?: ($academicTerms[0] ?? ['name' => 'None']);
$cards = [
    ['title' => 'Active Session', 'value' => $activeSessionRow['name'], 'description' => 'Current academic session', 'icon' => 'fa-calendar-days', 'color' => 'success'],
    ['title' => 'Current Term', 'value' => $activeTermRow['name'], 'description' => 'Active term', 'icon' => 'fa-calendar-week', 'color' => 'blue'],
    ['title' => 'Total Classes', 'value' => $academicClassesMeta['total'], 'description' => 'Configured classes', 'icon' => 'fa-school', 'color' => 'success'],
    ['title' => 'Total Subjects', 'value' => $academicSubjectsMeta['total'], 'description' => 'Academic subjects', 'icon' => 'fa-book-open', 'color' => 'warning'],
    ['title' => 'Departments', 'value' => $academicDepartmentsMeta['total'], 'description' => 'Academic units', 'icon' => 'fa-building-columns', 'color' => 'success'],
    ['title' => 'Calendar Events', 'value' => $schoolEventsMeta['total'], 'description' => 'Scheduled events', 'icon' => 'fa-calendar-check', 'color' => 'blue'],
];
?>
<style>
.admin-academic-module .academic-shell{display:grid;grid-template-columns:280px minmax(0,1fr);gap:22px}.admin-academic-module .academic-nav{position:sticky;top:90px;align-self:start}.admin-academic-module .academic-nav button{width:100%;border:0;border-radius:16px;background:#f8fafc;color:var(--am-ink);padding:14px 15px;margin-bottom:10px;display:flex;align-items:center;gap:10px;font-weight:900;text-align:left;transition:.18s}.admin-academic-module .academic-nav button.active,.admin-academic-module .academic-nav button:hover{background:var(--am-primary);color:#fff;box-shadow:0 12px 24px rgba(15,118,110,.2)}.admin-academic-module .academic-panel{display:none}.admin-academic-module .academic-panel.active{display:block}.admin-academic-module .mobile-academic-select{display:none;margin-bottom:16px}.admin-academic-module .bulk-row{display:flex;flex-wrap:wrap;gap:8px}.admin-academic-module .class-chip{display:inline-flex;padding:6px 10px;border-radius:999px;background:var(--am-soft);color:var(--am-dark);font-size:12px;font-weight:900;margin:2px}.admin-academic-module .academic-field{display:none}.admin-academic-module .academic-field.field-visible{display:block}.admin-academic-module .field-error{color:#dc2626;font-size:12px;font-weight:800;margin-top:4px}.admin-academic-module .checkbox-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;max-height:160px;overflow:auto;padding:10px;border:1px solid rgba(148,163,184,.25);border-radius:12px}.admin-academic-module .filter-form{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;align-items:end;padding:16px;border-radius:16px;background:#f8fafc;margin-bottom:16px}.admin-academic-module .filter-form label{font-size:12px;font-weight:900;color:var(--am-muted);margin-bottom:5px;display:block}.admin-academic-module .filter-actions{display:flex;gap:8px}.admin-academic-module .pagination-strip{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding-top:14px}.admin-academic-module .page-link-soft{min-width:36px;height:36px;padding:0 10px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;background:#fff;border:1px solid var(--am-border);color:var(--am-dark);font-weight:900;text-decoration:none;font-size:13px}.admin-academic-module .page-link-soft.active{background:var(--am-primary);color:#fff}.admin-academic-module .page-link-soft.disabled{opacity:.4;pointer-events:none}@media(max-width:991.98px){.admin-academic-module .academic-shell{grid-template-columns:1fr}.admin-academic-module .academic-nav{display:none}.admin-academic-module .mobile-academic-select{display:block}}
</style>
<div class="admin-academic-module">
    <?php foreach ($academicFlashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <section class="module-hero"><div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Academic Management</div><div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><span class="module-kicker"><i class="fa-solid fa-school-flag"></i> Academic Management</span><h3 class="mt-3 mb-2">Academic Management</h3><p class="text-muted mb-0">Manage sessions, terms, classes, sections, departments, subjects, and school calendar from one centralized page.</p></div><button class="module-btn btn-primary-soft" data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button"><i class="fa-solid fa-plus"></i> Add Record</button></div></section>
    <?php sms_academic_cards_local($cards); ?>
    <div class="academic-shell">
        <aside class="module-card academic-nav" aria-label="Academic Management sections"><?php foreach ($sections as $section): ?><button class="academic-tab <?php echo $section['id'] === $academicActiveTab ? 'active' : ''; ?>" data-target="<?php echo sms_e($section['id']); ?>" type="button"><i class="fa-solid <?php echo sms_e($section['icon']); ?>"></i> <?php echo sms_e($section['label']); ?></button><?php endforeach; ?></aside>
        <main>
            <select class="form-select mobile-academic-select" id="mobileAcademicSelect"><?php foreach ($sections as $section): ?><option value="<?php echo sms_e($section['id']); ?>"><?php echo sms_e($section['label']); ?></option><?php endforeach; ?></select>

            <section class="academic-panel <?php echo $academicActiveTab === 'sessions' ? 'active' : ''; ?>" id="sessions"><div class="module-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4>Academic Sessions</h4><p class="text-muted mb-0">Only one session can be active at a time.</p></div><div class="bulk-row"><button class="module-btn btn-primary-soft add-record" data-entity="session" data-entity-label="Academic Session" data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button">Add Session</button><button class="module-btn btn-muted-soft" onclick="window.print()" type="button">Print</button></div></div>
                <form class="filter-form" method="get"><input type="hidden" name="tab" value="sessions"><div><label>Search</label><input class="form-control" name="sessions_search" value="<?php echo sms_e($sessionParams['search']); ?>" placeholder="Search by session name"></div><div><label>Status</label><select class="form-select" name="sessions_status"><option value="">All Statuses</option><?php foreach (['active','inactive','completed','upcoming'] as $s): ?><?php echo sms_opt($s, $sessionParams['status']); ?><?php endforeach; ?></select></div><div class="filter-actions"><button class="module-btn btn-primary-soft" type="submit">Search</button><a class="module-btn btn-muted-soft" href="academic-management.php?tab=sessions#sessions">Clear</a></div></form>
                <div class="table-shell"><table class="table academic-table"><thead><tr><th>Session Name</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($academicSessions as $item): ?><tr><td><?php echo sms_e($item['name']); ?></td><td><?php echo sms_e($item['start_date']); ?></td><td><?php echo sms_e($item['end_date']); ?></td><td><?php echo sms_academic_badge_local($item['status']); ?></td><td><div class="d-flex gap-1"><button class="action-btn edit-record" data-entity="session" data-entity-label="Academic Session" data-record='<?php echo sms_e(json_encode($item)); ?>' data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button"><i class="fa-solid fa-pen"></i></button><?php if ($item['status'] !== 'Active'): ?><form method="post" class="d-inline"><input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>"><input type="hidden" name="entity" value="session"><input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>"><input type="hidden" name="form_action" value="activate"><input type="hidden" name="tab" value="sessions"><button class="action-btn" title="Activate" type="submit"><i class="fa-solid fa-circle-check"></i></button></form><?php endif; ?><button class="action-btn delete-record" data-entity="session" data-id="<?php echo (int) $item['id']; ?>" data-name="<?php echo sms_e($item['name']); ?>" data-tab="sessions" data-bs-toggle="modal" data-bs-target="#deleteAcademicModal" type="button"><i class="fa-solid fa-trash"></i></button></div></td></tr><?php endforeach; ?><?php if (!$academicSessions): ?><tr><td colspan="5" class="text-center text-muted py-4">No sessions match your search.</td></tr><?php endif; ?></tbody></table></div>
                <?php sms_academic_pagination('sessions', 'sessions', $sessionParams, $academicSessionsMeta); ?>
            </div></section>

            <section class="academic-panel <?php echo $academicActiveTab === 'terms' ? 'active' : ''; ?>" id="terms"><div class="module-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4>Terms</h4><p class="text-muted mb-0">Only one term can be active within the active session.</p></div><div class="bulk-row"><button class="module-btn btn-primary-soft add-record" data-entity="term" data-entity-label="Term" data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button">Add Term</button><button class="module-btn btn-muted-soft" onclick="window.print()" type="button">Print</button></div></div>
                <form class="filter-form" method="get"><input type="hidden" name="tab" value="terms"><div><label>Search</label><input class="form-control" name="terms_search" value="<?php echo sms_e($termParams['search']); ?>" placeholder="Search by term name"></div><div><label>Session</label><select class="form-select" name="terms_session"><option value="">All Sessions</option><?php foreach ($academicSessionOptions as $option): ?><?php echo sms_opt((string) $option['id'], $termParams['session'], (string) $option['name']); ?><?php endforeach; ?></select></div><div><label>Status</label><select class="form-select" name="terms_status"><option value="">All Statuses</option><?php foreach (['active','inactive','completed'] as $s): ?><?php echo sms_opt($s, $termParams['status']); ?><?php endforeach; ?></select></div><div class="filter-actions"><button class="module-btn btn-primary-soft" type="submit">Search</button><a class="module-btn btn-muted-soft" href="academic-management.php?tab=terms#terms">Clear</a></div></form>
                <div class="table-shell"><table class="table academic-table"><thead><tr><th>Academic Session</th><th>Term</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($academicTerms as $item): ?><tr><td><?php echo sms_e($item['session']); ?></td><td><?php echo sms_e($item['name']); ?></td><td><?php echo sms_e($item['start_date']); ?></td><td><?php echo sms_e($item['end_date']); ?></td><td><?php echo sms_academic_badge_local($item['status']); ?></td><td><div class="d-flex gap-1"><button class="action-btn edit-record" data-entity="term" data-entity-label="Term" data-record='<?php echo sms_e(json_encode($item)); ?>' data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button"><i class="fa-solid fa-pen"></i></button><?php if ($item['status'] !== 'Active'): ?><form method="post" class="d-inline"><input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>"><input type="hidden" name="entity" value="term"><input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>"><input type="hidden" name="form_action" value="activate"><input type="hidden" name="tab" value="terms"><button class="action-btn" title="Activate" type="submit"><i class="fa-solid fa-circle-check"></i></button></form><?php endif; ?><button class="action-btn delete-record" data-entity="term" data-id="<?php echo (int) $item['id']; ?>" data-name="<?php echo sms_e($item['name']); ?>" data-tab="terms" data-bs-toggle="modal" data-bs-target="#deleteAcademicModal" type="button"><i class="fa-solid fa-trash"></i></button></div></td></tr><?php endforeach; ?><?php if (!$academicTerms): ?><tr><td colspan="6" class="text-center text-muted py-4">No terms match your search.</td></tr><?php endif; ?></tbody></table></div>
                <?php sms_academic_pagination('terms', 'terms', $termParams, $academicTermsMeta); ?>
            </div></section>

            <section class="academic-panel <?php echo $academicActiveTab === 'classes' ? 'active' : ''; ?>" id="classes"><div class="module-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4>Classes</h4><p class="text-muted mb-0">Manage junior and senior school class levels.</p></div><div class="bulk-row"><button class="module-btn btn-primary-soft add-record" data-entity="class" data-entity-label="Class" data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button">Add Class</button><button class="module-btn btn-muted-soft" onclick="window.print()" type="button">Print</button></div></div>
                <form class="filter-form" method="get"><input type="hidden" name="tab" value="classes"><div><label>Search</label><input class="form-control" name="classes_search" value="<?php echo sms_e($classParams['search']); ?>" placeholder="Search by class name"></div><div><label>Level</label><select class="form-select" name="classes_level"><option value="">All Levels</option><?php foreach (['creche','nursery','primary','junior','senior'] as $lvl): ?><?php echo sms_opt($lvl, $classParams['level']); ?><?php endforeach; ?></select></div><div><label>Status</label><select class="form-select" name="classes_status"><option value="">All Statuses</option><?php foreach (['active','inactive'] as $s): ?><?php echo sms_opt($s, $classParams['status']); ?><?php endforeach; ?></select></div><div class="filter-actions"><button class="module-btn btn-primary-soft" type="submit">Search</button><a class="module-btn btn-muted-soft" href="academic-management.php?tab=classes#classes">Clear</a></div></form>
                <div class="table-shell"><table class="table academic-table"><thead><tr><th>Class Name</th><th>Level</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($academicClasses as $item): ?><tr><td><?php echo sms_e($item['name']); ?></td><td><?php echo sms_e($item['level']); ?></td><td><?php echo sms_academic_badge_local($item['status']); ?></td><td><div class="d-flex gap-1"><button class="action-btn edit-record" data-entity="class" data-entity-label="Class" data-record='<?php echo sms_e(json_encode($item)); ?>' data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button"><i class="fa-solid fa-pen"></i></button><button class="action-btn delete-record" data-entity="class" data-id="<?php echo (int) $item['id']; ?>" data-name="<?php echo sms_e($item['name']); ?>" data-tab="classes" data-bs-toggle="modal" data-bs-target="#deleteAcademicModal" type="button"><i class="fa-solid fa-trash"></i></button></div></td></tr><?php endforeach; ?><?php if (!$academicClasses): ?><tr><td colspan="4" class="text-center text-muted py-4">No classes match your search.</td></tr><?php endif; ?></tbody></table></div>
                <?php sms_academic_pagination('classes', 'classes', $classParams, $academicClassesMeta); ?>
            </div></section>

            <section class="academic-panel <?php echo $academicActiveTab === 'sections' ? 'active' : ''; ?>" id="sections"><div class="module-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4>Sections / Arms</h4><p class="text-muted mb-0">Manage class arms and capacities.</p></div><button class="module-btn btn-primary-soft add-record" data-entity="section" data-entity-label="Section" data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button">Add Section</button></div>
                <form class="filter-form" method="get"><input type="hidden" name="tab" value="sections"><div><label>Search</label><input class="form-control" name="sections_search" value="<?php echo sms_e($sectionParams['search']); ?>" placeholder="Search by section name"></div><div><label>Class</label><select class="form-select" name="sections_class"><option value="">All Classes</option><?php foreach ($academicClassOptions as $option): ?><?php echo sms_opt((string) $option['id'], $sectionParams['class'], (string) $option['name']); ?><?php endforeach; ?></select></div><div><label>Status</label><select class="form-select" name="sections_status"><option value="">All Statuses</option><?php foreach (['active','inactive'] as $s): ?><?php echo sms_opt($s, $sectionParams['status']); ?><?php endforeach; ?></select></div><div class="filter-actions"><button class="module-btn btn-primary-soft" type="submit">Search</button><a class="module-btn btn-muted-soft" href="academic-management.php?tab=sections#sections">Clear</a></div></form>
                <div class="table-shell"><table class="table academic-table"><thead><tr><th>Class</th><th>Section</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($academicSections as $item): ?><tr><td><?php echo sms_e($item['class']); ?></td><td><?php echo sms_e($item['name']); ?></td><td><?php echo sms_e($item['capacity']); ?></td><td><?php echo sms_academic_badge_local($item['status']); ?></td><td><div class="d-flex gap-1"><button class="action-btn edit-record" data-entity="section" data-entity-label="Section" data-record='<?php echo sms_e(json_encode($item)); ?>' data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button"><i class="fa-solid fa-pen"></i></button><button class="action-btn delete-record" data-entity="section" data-id="<?php echo (int) $item['id']; ?>" data-name="<?php echo sms_e($item['name']); ?>" data-tab="sections" data-bs-toggle="modal" data-bs-target="#deleteAcademicModal" type="button"><i class="fa-solid fa-trash"></i></button></div></td></tr><?php endforeach; ?><?php if (!$academicSections): ?><tr><td colspan="5" class="text-center text-muted py-4">No sections match your search.</td></tr><?php endif; ?></tbody></table></div>
                <?php sms_academic_pagination('sections', 'sections', $sectionParams, $academicSectionsMeta); ?>
            </div></section>

            <section class="academic-panel <?php echo $academicActiveTab === 'departments' ? 'active' : ''; ?>" id="departments"><div class="module-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4>Departments</h4><p class="text-muted mb-0">Manage academic departments and descriptions.</p></div><button class="module-btn btn-primary-soft add-record" data-entity="department" data-entity-label="Department" data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button">Add Department</button></div>
                <form class="filter-form" method="get"><input type="hidden" name="tab" value="departments"><div><label>Search</label><input class="form-control" name="departments_search" value="<?php echo sms_e($departmentParams['search']); ?>" placeholder="Search by department name"></div><div><label>Status</label><select class="form-select" name="departments_status"><option value="">All Statuses</option><?php foreach (['active','inactive'] as $s): ?><?php echo sms_opt($s, $departmentParams['status']); ?><?php endforeach; ?></select></div><div class="filter-actions"><button class="module-btn btn-primary-soft" type="submit">Search</button><a class="module-btn btn-muted-soft" href="academic-management.php?tab=departments#departments">Clear</a></div></form>
                <div class="table-shell"><table class="table academic-table"><thead><tr><th>Department</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($academicDepartments as $item): ?><tr><td><?php echo sms_e($item['name']); ?></td><td><?php echo sms_e($item['description']); ?></td><td><?php echo sms_academic_badge_local($item['status']); ?></td><td><div class="d-flex gap-1"><button class="action-btn edit-record" data-entity="department" data-entity-label="Department" data-record='<?php echo sms_e(json_encode($item)); ?>' data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button"><i class="fa-solid fa-pen"></i></button><button class="action-btn delete-record" data-entity="department" data-id="<?php echo (int) $item['id']; ?>" data-name="<?php echo sms_e($item['name']); ?>" data-tab="departments" data-bs-toggle="modal" data-bs-target="#deleteAcademicModal" type="button"><i class="fa-solid fa-trash"></i></button></div></td></tr><?php endforeach; ?><?php if (!$academicDepartments): ?><tr><td colspan="4" class="text-center text-muted py-4">No departments match your search.</td></tr><?php endif; ?></tbody></table></div>
                <?php sms_academic_pagination('departments', 'departments', $departmentParams, $academicDepartmentsMeta); ?>
            </div></section>

            <section class="academic-panel <?php echo $academicActiveTab === 'subjects' ? 'active' : ''; ?>" id="subjects"><div class="module-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4>Subjects</h4><p class="text-muted mb-0">Assign subjects to one or more classes.</p></div><button class="module-btn btn-primary-soft add-record" data-entity="subject" data-entity-label="Subject" data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button">Add Subject</button></div>
                <form class="filter-form" method="get"><input type="hidden" name="tab" value="subjects"><div><label>Search</label><input class="form-control" name="subjects_search" value="<?php echo sms_e($subjectParams['search']); ?>" placeholder="Search by name or code"></div><div><label>Department</label><select class="form-select" name="subjects_department"><option value="">All Departments</option><?php foreach ($academicDepartmentOptions as $option): ?><?php echo sms_opt((string) $option['id'], $subjectParams['department'], (string) $option['name']); ?><?php endforeach; ?></select></div><div><label>Type</label><select class="form-select" name="subjects_type"><option value="">All Types</option><?php foreach (['core','elective'] as $t): ?><?php echo sms_opt($t, $subjectParams['type']); ?><?php endforeach; ?></select></div><div><label>Status</label><select class="form-select" name="subjects_status"><option value="">All Statuses</option><?php foreach (['active','inactive'] as $s): ?><?php echo sms_opt($s, $subjectParams['status']); ?><?php endforeach; ?></select></div><div class="filter-actions"><button class="module-btn btn-primary-soft" type="submit">Search</button><a class="module-btn btn-muted-soft" href="academic-management.php?tab=subjects#subjects">Clear</a></div></form>
                <div class="table-shell"><table class="table academic-table"><thead><tr><th>Subject Code</th><th>Subject Name</th><th>Department</th><th>Applicable Classes</th><th>Subject Type</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($academicSubjects as $item): ?><tr><td><?php echo sms_e($item['code']); ?></td><td><?php echo sms_e($item['name']); ?></td><td><?php echo sms_e($item['department']); ?></td><td><?php foreach (array_filter(explode(',', $item['level'])) as $level): ?><span class="class-chip"><?php echo sms_e(trim($level)); ?></span><?php endforeach; ?></td><td><?php echo sms_e($item['type']); ?></td><td><?php echo sms_academic_badge_local($item['status']); ?></td><td><div class="d-flex gap-1"><button class="action-btn edit-record" data-entity="subject" data-entity-label="Subject" data-record='<?php echo sms_e(json_encode($item)); ?>' data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button"><i class="fa-solid fa-pen"></i></button><button class="action-btn delete-record" data-entity="subject" data-id="<?php echo (int) $item['id']; ?>" data-name="<?php echo sms_e($item['name']); ?>" data-tab="subjects" data-bs-toggle="modal" data-bs-target="#deleteAcademicModal" type="button"><i class="fa-solid fa-trash"></i></button></div></td></tr><?php endforeach; ?><?php if (!$academicSubjects): ?><tr><td colspan="7" class="text-center text-muted py-4">No subjects match your search.</td></tr><?php endif; ?></tbody></table></div>
                <?php sms_academic_pagination('subjects', 'subjects', $subjectParams, $academicSubjectsMeta); ?>
            </div></section>

            <section class="academic-panel <?php echo $academicActiveTab === 'calendar' ? 'active' : ''; ?>" id="calendar"><div class="module-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4>School Calendar</h4><p class="text-muted mb-0">Manage academic events, examinations, meetings, holidays, and activities.</p></div><button class="module-btn btn-primary-soft add-record" data-entity="calendar" data-entity-label="Calendar Event" data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button">Add Event</button></div>
                <form class="filter-form" method="get"><input type="hidden" name="tab" value="calendar"><div><label>Search</label><input class="form-control" name="calendar_search" value="<?php echo sms_e($calendarParams['search']); ?>" placeholder="Search by event title"></div><div><label>Event Type</label><select class="form-select" name="calendar_type"><option value="">All Types</option><?php foreach ($academicCalendarEventTypes as $t): ?><?php echo sms_opt($t, $calendarParams['type']); ?><?php endforeach; ?></select></div><div><label>Status</label><select class="form-select" name="calendar_status"><option value="">All Statuses</option><?php foreach ($academicCalendarStatuses as $s): ?><?php echo sms_opt($s, $calendarParams['status']); ?><?php endforeach; ?></select></div><div><label>Session</label><select class="form-select" name="calendar_session"><option value="">All Sessions</option><?php foreach ($academicSessionOptions as $option): ?><?php echo sms_opt((string) $option['id'], $calendarParams['session'], (string) $option['name']); ?><?php endforeach; ?></select></div><div class="filter-actions"><button class="module-btn btn-primary-soft" type="submit">Search</button><a class="module-btn btn-muted-soft" href="academic-management.php?tab=calendar#calendar">Clear</a></div></form>
                <div class="table-shell"><table class="table academic-table"><thead><tr><th>Event Title</th><th>Event Type</th><th>Start Date</th><th>End Date</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($schoolEvents as $item): ?><tr><td><?php echo sms_e($item['title']); ?></td><td><?php echo sms_e(ucfirst(str_replace('_', ' ', $item['type']))); ?></td><td><?php echo sms_e($item['start_date']); ?></td><td><?php echo sms_e($item['end_date']); ?></td><td><?php echo sms_e($item['location']); ?></td><td><?php echo sms_academic_badge_local(ucfirst($item['status'])); ?></td><td><div class="d-flex gap-1"><button class="action-btn edit-record" data-entity="calendar" data-entity-label="Calendar Event" data-record='<?php echo sms_e(json_encode($item)); ?>' data-bs-toggle="modal" data-bs-target="#academicFormModal" type="button"><i class="fa-solid fa-pen"></i></button><button class="action-btn delete-record" data-entity="calendar" data-id="<?php echo (int) $item['id']; ?>" data-name="<?php echo sms_e($item['title']); ?>" data-tab="calendar" data-bs-toggle="modal" data-bs-target="#deleteAcademicModal" type="button"><i class="fa-solid fa-trash"></i></button></div></td></tr><?php endforeach; ?><?php if (!$schoolEvents): ?><tr><td colspan="7" class="text-center text-muted py-4">No calendar events match your search.</td></tr><?php endif; ?></tbody></table></div>
                <?php sms_academic_pagination('calendar', 'calendar', $calendarParams, $schoolEventsMeta); ?>
            </div></section>
        </main>
    </div>

    <div class="modal fade" id="academicFormModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><form class="modal-content" id="academicEntityForm" method="post" action="academic-management.php">
        <div class="modal-header"><h5 class="modal-title" id="academicModalTitle">Add / Edit Academic Record</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <input type="hidden" name="entity" id="academicEntityType" value="">
            <input type="hidden" name="tab" id="academicEntityTab" value="">
            <input type="hidden" name="id" id="academicRecordId" value="">
            <?php if (!empty($academicErrors)): ?><div class="alert alert-danger"><?php foreach ($academicErrors as $field => $error): ?><div><?php echo sms_e($error); ?></div><?php endforeach; ?></div><?php endif; ?>
            <div class="form-grid">
                <div class="academic-field" data-entities="session,class,department,section,subject,calendar"><label id="fieldNameLabel">Name</label><input class="form-control" name="name" id="fieldNameText"></div>
                <div class="academic-field" data-entities="term"><label>Term</label><select class="form-select" name="name" id="fieldNameTerm"><option value="First Term">First Term</option><option value="Second Term">Second Term</option><option value="Third Term">Third Term</option></select></div>
                <div class="academic-field" data-entities="session,term,class,section,department,subject"><label>Status</label><select class="form-select" name="status" id="fieldStatus"><?php foreach ($academicStatuses as $status): ?><option><?php echo sms_e($status); ?></option><?php endforeach; ?></select></div>
                <div class="academic-field" data-entities="calendar"><label>Status</label><select class="form-select" name="status" id="fieldCalendarStatus"><option value="scheduled">Scheduled</option><option value="cancelled">Cancelled</option><option value="completed">Completed</option></select></div>
                <div class="academic-field" data-entities="session,term,calendar"><label>Start Date</label><input class="form-control" type="date" name="start_date" id="fieldStartDate"></div>
                <div class="academic-field" data-entities="session,term,calendar"><label>End Date</label><input class="form-control" type="date" name="end_date" id="fieldEndDate"></div>
                <div class="academic-field" data-entities="term,calendar"><label>Academic Session</label><select class="form-select" name="session_id" id="fieldSessionId"><option value="">None</option><?php foreach ($academicSessionOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>"><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div class="academic-field" data-entities="calendar"><label>Term (optional)</label><select class="form-select" name="term_id" id="fieldTermId"><option value="">None</option><?php foreach ($academicTermOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>" data-session="<?php echo (int) $option['session_id']; ?>"><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div class="academic-field" data-entities="calendar"><label>Event Type</label><select class="form-select" name="event_type" id="fieldEventType"><?php foreach ($academicCalendarEventTypes as $type): ?><option value="<?php echo sms_e($type); ?>"><?php echo sms_e(ucfirst(str_replace('_', ' ', $type))); ?></option><?php endforeach; ?></select></div>
                <div class="academic-field" data-entities="calendar"><label>Location</label><input class="form-control" name="location" id="fieldLocation" placeholder="School Hall"></div>
                <div class="academic-field" data-entities="class"><label>Level</label><select class="form-select" name="level" id="fieldLevel"><?php foreach (['Creche','Nursery','Primary','Junior','Senior'] as $level): ?><option><?php echo sms_e($level); ?></option><?php endforeach; ?></select></div>
                <div class="academic-field" data-entities="section"><label>Class</label><select class="form-select" name="class_id" id="fieldClassId"><?php foreach ($academicClassOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>"><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div class="academic-field" data-entities="section"><label>Capacity</label><input class="form-control" type="number" min="0" name="capacity" id="fieldCapacity"></div>
                <div class="academic-field" data-entities="subject"><label>Subject Code</label><input class="form-control" name="code" id="fieldCode" placeholder="MTH"></div>
                <div class="academic-field" data-entities="subject"><label>Department</label><select class="form-select" name="department_id" id="fieldDepartmentId"><option value="">Unassigned</option><?php foreach ($academicDepartmentOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>"><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div class="academic-field" data-entities="subject"><label>Subject Type</label><select class="form-select" name="subject_type" id="fieldSubjectType"><option value="core">Core</option><option value="elective">Elective</option></select></div>
                <div class="academic-field full" data-entities="department"><label>Description</label><textarea class="form-control" name="description" id="fieldDescription"></textarea></div>
                <div class="academic-field full" data-entities="subject"><label>Applicable Classes</label><div class="checkbox-grid" id="fieldClasses"><?php foreach ($academicClassOptions as $option): ?><label class="d-flex align-items-center gap-2"><input type="checkbox" name="classes[]" value="<?php echo (int) $option['id']; ?>"> <?php echo sms_e($option['name']); ?></label><?php endforeach; ?></div></div>
            </div>
        </div>
        <div class="modal-footer"><button class="module-btn btn-muted-soft" type="reset">Reset</button><button class="module-btn btn-muted-soft" type="button" data-bs-dismiss="modal">Cancel</button><button class="module-btn btn-primary-soft" type="submit">Save Record</button></div>
    </form></div></div>

    <div class="modal fade" id="deleteAcademicModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" id="deleteAcademicForm" method="post" action="academic-management.php">
        <div class="modal-header"><h5 class="modal-title">Delete Record</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <input type="hidden" name="form_action" value="delete">
            <input type="hidden" name="entity" id="deleteEntityType" value="">
            <input type="hidden" name="tab" id="deleteEntityTab" value="">
            <input type="hidden" name="id" id="deleteRecordId" value="">
            <p>Are you sure you want to delete this academic record?</p>
            <strong id="deleteRecordName">Selected record</strong>
        </div>
        <div class="modal-footer"><button class="module-btn btn-muted-soft" type="button" data-bs-dismiss="modal">Cancel</button><button class="module-btn btn-danger-soft" type="submit">Delete</button></div>
    </form></div></div>
</div></div></div>
<script>
/* Centralized Academic Management behavior: section navigation, modal field scoping, and record population. */
(function(){
    function activate(target){document.querySelectorAll('.academic-tab').forEach(function(tab){tab.classList.toggle('active',tab.dataset.target===target);});document.querySelectorAll('.academic-panel').forEach(function(panel){panel.classList.toggle('active',panel.id===target);});var mobile=document.getElementById('mobileAcademicSelect');if(mobile){mobile.value=target;}}
    document.querySelectorAll('.academic-tab').forEach(function(tab){tab.addEventListener('click',function(){activate(tab.dataset.target);});});
    var mobile=document.getElementById('mobileAcademicSelect');if(mobile){mobile.addEventListener('change',function(){activate(mobile.value);});}
    function currentTab(){var panel=document.querySelector('.academic-panel.active');return panel?panel.id:'sessions';}

    var form = document.getElementById('academicEntityForm');
    var fieldEls = form.querySelectorAll('.academic-field');
    var entityLabels = {session:'Name',term:'Term',class:'Name',section:'Name',department:'Name',subject:'Name',calendar:'Event Title'};

    function toggleFieldsFor(entity){
        fieldEls.forEach(function(wrap){
            var entities = (wrap.dataset.entities||'').split(',');
            var visible = entities.indexOf(entity) > -1;
            wrap.classList.toggle('field-visible', visible);
            wrap.querySelectorAll('input,select,textarea').forEach(function(el){ el.disabled = !visible; });
        });
        var nameLabel = document.getElementById('fieldNameLabel');
        if (nameLabel && entityLabels[entity]) { nameLabel.textContent = entityLabels[entity]; }
    }

    function resetForm(){
        form.reset();
        document.getElementById('academicRecordId').value = '';
    }

    function fillForm(entity, record){
        document.getElementById('academicRecordId').value = record.id || '';
        if (document.getElementById('fieldNameText')) document.getElementById('fieldNameText').value = entity === 'calendar' ? (record.title || '') : (record.name || '');
        if (document.getElementById('fieldNameTerm')) document.getElementById('fieldNameTerm').value = record.name || 'First Term';
        if (document.getElementById('fieldStatus')) document.getElementById('fieldStatus').value = record.status || 'Active';
        if (document.getElementById('fieldCalendarStatus')) document.getElementById('fieldCalendarStatus').value = (record.status || 'scheduled').toLowerCase();
        if (document.getElementById('fieldStartDate')) document.getElementById('fieldStartDate').value = record.start_date || '';
        if (document.getElementById('fieldEndDate')) document.getElementById('fieldEndDate').value = record.end_date || '';
        if (document.getElementById('fieldSessionId')) document.getElementById('fieldSessionId').value = record.session_id || '';
        if (document.getElementById('fieldTermId')) document.getElementById('fieldTermId').value = record.term_id || '';
        if (document.getElementById('fieldEventType')) document.getElementById('fieldEventType').value = record.type || 'other';
        if (document.getElementById('fieldLocation')) document.getElementById('fieldLocation').value = record.location || '';
        if (document.getElementById('fieldLevel')) document.getElementById('fieldLevel').value = record.level || 'Junior';
        if (document.getElementById('fieldClassId')) document.getElementById('fieldClassId').value = record.class_id || '';
        if (document.getElementById('fieldCapacity')) document.getElementById('fieldCapacity').value = record.capacity || '';
        if (document.getElementById('fieldCode')) document.getElementById('fieldCode').value = record.code || '';
        if (document.getElementById('fieldDepartmentId')) document.getElementById('fieldDepartmentId').value = record.department_id || '';
        if (document.getElementById('fieldSubjectType')) document.getElementById('fieldSubjectType').value = (record.type||'core').toLowerCase();
        if (document.getElementById('fieldDescription')) document.getElementById('fieldDescription').value = record.description || '';
        if (entity === 'subject') {
            var ids = record.class_ids || [];
            document.querySelectorAll('#fieldClasses input[type=checkbox]').forEach(function(box){ box.checked = ids.indexOf(parseInt(box.value,10)) > -1; });
        }
    }

    document.querySelectorAll('.add-record').forEach(function(button){
        button.addEventListener('click',function(){
            var entity = button.dataset.entity;
            var label = button.dataset.entityLabel || 'Academic Record';
            resetForm();
            document.getElementById('academicModalTitle').textContent = 'Add ' + label;
            document.getElementById('academicEntityType').value = entity;
            document.getElementById('academicEntityTab').value = currentTab();
            toggleFieldsFor(entity);
        });
    });

    document.querySelectorAll('.edit-record').forEach(function(button){
        button.addEventListener('click',function(){
            var entity = button.dataset.entity;
            var label = button.dataset.entityLabel || 'Academic Record';
            var record = JSON.parse(button.dataset.record || '{}');
            resetForm();
            document.getElementById('academicModalTitle').textContent = 'Edit ' + label;
            document.getElementById('academicEntityType').value = entity;
            document.getElementById('academicEntityTab').value = currentTab();
            toggleFieldsFor(entity);
            fillForm(entity, record);
        });
    });

    document.querySelectorAll('.delete-record').forEach(function(button){
        button.addEventListener('click',function(){
            document.getElementById('deleteRecordName').textContent = button.dataset.name || 'Selected record';
            document.getElementById('deleteEntityType').value = button.dataset.entity || '';
            document.getElementById('deleteEntityTab').value = button.dataset.tab || currentTab();
            document.getElementById('deleteRecordId').value = button.dataset.id || '';
        });
    });

    form.addEventListener('submit', function(e){
        if (!this.checkValidity()) { this.reportValidity(); e.preventDefault(); }
    });

    activate('<?php echo sms_e($academicActiveTab); ?>');
})();
</script>
<?php require_once('includes/footer.php'); ?>
