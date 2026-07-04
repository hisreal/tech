<?php
/**
 * Shared sidebar renderer.
 *
 * Each module supplies $smsNavItems and optional $smsProfileSummary. Active
 * states are calculated here so page links stay DRY across roles. Items may
 * also include a children array for collapsible module groups.
 */
function sms_sidebar_item_active(array $item, string $currentPage): bool
{
    if (sms_is_active_nav($item, $currentPage)) {
        return true;
    }

    foreach (($item['children'] ?? []) as $child) {
        if (sms_is_active_nav($child, $currentPage)) {
            return true;
        }
    }

    return false;
}
?>
<style>
    .student-sidebar-nav .sidebar-has-children > a { cursor: pointer; }
    .student-sidebar-nav .sidebar-submenu { display: none; margin: 8px 0 8px 18px; padding-left: 10px; border-left: 1px solid rgba(255,255,255,.14); }
    .student-sidebar-nav .sidebar-has-children.is-open .sidebar-submenu { display: block; }
    .student-sidebar-nav .sidebar-submenu a { min-height: 38px; padding: 9px 12px; font-size: 13px; }
    .student-sidebar-nav .sidebar-chevron { margin-left: auto; font-size: 11px; transition: transform .18s ease; }
    .student-sidebar-nav .sidebar-has-children.is-open .sidebar-chevron { transform: rotate(180deg); }
</style>
<aside class="student-sidebar" id="studentSidebar" aria-label="<?php echo sms_e(ucfirst($smsModule)); ?> dashboard navigation">
    <div class="student-sidebar-inner">
        <div class="student-sidebar-brand">
            <center><a href="<?php echo sms_e($smsDashboardLink ?? 'dashboard.php'); ?>">
                <img src="<?php echo sms_asset(sms_config('school_logo', 'assets/img/logo/school-logo.png'), $smsAssetPrefix); ?>" alt="Logo">
            </a></center>
            <label class="student-sidebar-close" for="studentSidebarControl" role="button" tabindex="0" aria-label="Close dashboard navigation">
                <i class="fa-solid fa-xmark"></i>
            </label>
        </div>

        <?php if (is_array($smsProfileSummary)): ?>
            <div class="student-info-card">
                <span class="student-avatar">
                    <img src="<?php echo sms_e($smsProfileSummary['image'] ?? ''); ?>" alt="<?php echo sms_e($smsProfileSummary['name'] ?? 'Profile'); ?>">
                    <span class="student-status"><i class="fa-solid fa-check"></i></span>
                </span>
                <h5><?php echo sms_e($smsProfileSummary['name'] ?? ''); ?></h5>
                <p><?php echo sms_e($smsProfileSummary['role'] ?? ucfirst($smsModule)); ?></p>
                <?php if (!empty($smsProfileSummary['meta'])): ?>
                   <p>
                        <?php foreach ($smsProfileSummary['meta'] as $label => $value): ?>
                            <span><strong style="color:white;"><?php echo sms_e($label); ?></strong>: <?php echo sms_e($value); ?></span><br>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <nav class="student-sidebar-nav">
            <ul>
                <?php foreach ($smsNavItems as $item): ?>
                    <?php $hasChildren = !empty($item['children']); $isActive = sms_sidebar_item_active($item, $smsCurrentPage); ?>
                    <li class="<?php echo $hasChildren ? 'sidebar-has-children ' : ''; ?><?php echo $isActive ? 'is-open' : ''; ?>">
                        <a href="<?php echo $hasChildren ? 'javascript:void(0);' : sms_e($item['href']); ?>" class="<?php echo $isActive ? 'active' : ''; ?>" <?php echo $hasChildren ? 'data-sidebar-submenu-toggle' : ''; ?>>
                            <span class="student-nav-icon"><i class="<?php echo sms_e($item['icon']); ?>"></i></span>
                            <span><?php echo sms_e($item['label']); ?></span>
                            <?php if ($hasChildren): ?><i class="fa-solid fa-chevron-down sidebar-chevron"></i><?php endif; ?>
                        </a>
                        <?php if ($hasChildren): ?>
                            <ul class="sidebar-submenu">
                                <?php foreach ($item['children'] as $child): ?>
                                    <?php $childActive = sms_is_active_nav($child, $smsCurrentPage); ?>
                                    <li>
                                        <a href="<?php echo sms_e($child['href']); ?>" class="<?php echo $childActive ? 'active' : ''; ?>">
                                            <span class="student-nav-icon"><i class="<?php echo sms_e($child['icon']); ?>"></i></span>
                                            <span><?php echo sms_e($child['label']); ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
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
<script data-cfasync="false" type="text/javascript">
(function(){
    document.addEventListener('click', function(event){
        var toggle = event.target.closest('[data-sidebar-submenu-toggle]');
        if (!toggle) { return; }
        var item = toggle.closest('.sidebar-has-children');
        if (item) { item.classList.toggle('is-open'); }
    });
}());
</script>
