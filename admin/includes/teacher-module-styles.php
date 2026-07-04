<?php
/** Shared Teacher Management styling. Keep page-specific CSS light and scoped. */
?>
<style>
    .admin-teacher-module { --atm-primary:#0f766e; --atm-primary-dark:#115e59; --atm-soft:rgba(15,118,110,.1); --atm-border:rgba(15,118,110,.16); --atm-ink:#10201d; --atm-muted:#64748b; --atm-danger:#dc2626; --atm-warning:#d97706; --atm-shadow:0 22px 56px rgba(15,23,42,.08); padding-bottom:34px; }
    .admin-teacher-module .module-hero,.admin-teacher-module .module-card,.admin-teacher-module .summary-card,.admin-teacher-module .kpi-card { background:rgba(255,255,255,.98); border:1px solid var(--atm-border); box-shadow:var(--atm-shadow); }
    .admin-teacher-module .module-hero { padding:26px; border-radius:24px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),#fff); }
    .admin-teacher-module .breadcrumb-line { color:var(--atm-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
    .admin-teacher-module .module-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--atm-soft); color:var(--atm-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
    .admin-teacher-module h3,.admin-teacher-module h4,.admin-teacher-module h5 { color:var(--atm-ink); font-weight:900; }
    .admin-teacher-module .module-card,.admin-teacher-module .kpi-card { border-radius:22px; padding:22px; margin-bottom:22px; }
    .admin-teacher-module .summary-card { height:100%; min-height:142px; padding:20px; border-radius:20px; }
    .admin-teacher-module .summary-card h4 { margin:12px 0 4px; font-size:25px; line-height:1.15; }
    .admin-teacher-module .summary-card p { font-size:13px; font-weight:800; line-height:1.35; }    .admin-teacher-module .summary-card,.admin-teacher-module .kpi-card,.admin-teacher-module .module-card { transition:transform .18s ease, box-shadow .18s ease; }
    .admin-teacher-module .summary-card:hover,.admin-teacher-module .module-card:hover,.admin-teacher-module .kpi-card:hover { transform:translateY(-2px); box-shadow:0 20px 42px rgba(15,23,42,.11); }
    .admin-teacher-module .summary-icon { width:46px; height:46px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; background:var(--atm-soft); color:var(--atm-primary); font-size:18px; }
    .admin-teacher-module .summary-icon.blue { background:rgba(37,99,235,.1); color:#2563eb; }
    .admin-teacher-module .summary-icon.warning { background:rgba(245,158,11,.13); color:var(--atm-warning); }
    .admin-teacher-module .form-grid,.admin-teacher-module .filter-grid,.admin-teacher-module .info-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
    .admin-teacher-module .form-grid.two,.admin-teacher-module .info-grid.two { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .admin-teacher-module .full { grid-column:1/-1; }
    .admin-teacher-module label { color:var(--atm-ink); font-size:13px; font-weight:900; margin-bottom:7px; }
    .admin-teacher-module .form-control,.admin-teacher-module .form-select { min-height:46px; border-radius:14px; border:1px solid rgba(148,163,184,.35); font-weight:700; }
    .admin-teacher-module textarea.form-control { min-height:98px; }
    .admin-teacher-module .form-control:focus,.admin-teacher-module .form-select:focus { border-color:var(--atm-primary); box-shadow:0 0 0 .18rem rgba(15,118,110,.14); }
    .admin-teacher-module .module-btn { border:0; min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:14px; padding:10px 15px; font-weight:900; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-teacher-module .module-btn:hover { transform:translateY(-2px); }
    .admin-teacher-module .btn-primary-soft { background:var(--atm-primary); color:#fff; box-shadow:0 12px 24px rgba(15,118,110,.22); }
    .admin-teacher-module .btn-muted-soft { background:#f1f5f9; color:var(--atm-ink); }
    .admin-teacher-module .btn-danger-soft { background:rgba(220,38,38,.1); color:var(--atm-danger); }
    .admin-teacher-module .btn-outline-soft { background:#fff; color:var(--atm-primary-dark); border:1px solid var(--atm-border); }
    .admin-teacher-module .table-shell { overflow:auto; border:1px solid rgba(148,163,184,.2); border-radius:18px; }
    .admin-teacher-module table { min-width:960px; margin-bottom:0; }
    .admin-teacher-module thead th { position:sticky; top:0; z-index:2; background:#f0fdf4; color:var(--atm-primary-dark); font-size:12px; text-transform:uppercase; letter-spacing:.03em; border-bottom:1px solid var(--atm-border); }
    .admin-teacher-module tbody td { vertical-align:middle; color:#1f2937; font-weight:700; }
    .admin-teacher-module tbody tr:hover { background:rgba(15,118,110,.045); }
    .admin-teacher-module .teacher-passport,.admin-teacher-module .profile-photo { border-radius:50%; object-fit:cover; border:4px solid #dcfce7; }
    .admin-teacher-module .teacher-passport { width:44px; height:44px; }
    .admin-teacher-module .profile-photo { width:96px; height:96px; }
    .admin-teacher-module .chip-list { display:flex; flex-wrap:wrap; gap:6px; }
    .admin-teacher-module .chip { display:inline-flex; padding:6px 9px; border-radius:999px; background:var(--atm-soft); color:var(--atm-primary-dark); font-size:12px; font-weight:900; }
    .admin-teacher-module .status-badge { display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; background:rgba(22,163,74,.12); color:#15803d; }
    .admin-teacher-module .multi-select-box { border:1px solid rgba(148,163,184,.35); border-radius:16px; padding:12px; background:#fff; }
    .admin-teacher-module .multi-select-options { max-height:220px; overflow:auto; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; margin-top:10px; }
    .admin-teacher-module .multi-option { display:flex; align-items:center; gap:8px; padding:9px 10px; border-radius:12px; background:#f8fafc; font-weight:800; color:var(--atm-ink); }
    .admin-teacher-module .chart-bars { display:flex; align-items:end; gap:14px; height:230px; padding-top:18px; border-bottom:1px solid rgba(148,163,184,.24); }
    .admin-teacher-module .chart-bar { flex:1; display:flex; align-items:center; justify-content:flex-end; flex-direction:column; gap:8px; height:100%; }
    .admin-teacher-module .bar-fill { width:100%; max-width:52px; border-radius:12px 12px 4px 4px; background:linear-gradient(180deg,var(--atm-primary),var(--atm-primary-dark)); }
    .admin-teacher-module .bar-label { color:var(--atm-muted); font-size:12px; font-weight:900; }
    .admin-teacher-module .alert-success-soft { display:none; padding:14px 16px; border-radius:16px; background:rgba(22,163,74,.12); color:#166534; font-weight:900; margin-bottom:18px; }
    @media(max-width:991.98px){ .admin-teacher-module .form-grid,.admin-teacher-module .filter-grid,.admin-teacher-module .info-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.admin-teacher-module .multi-select-options{grid-template-columns:1fr} }
    @media(max-width:575.98px){ .admin-teacher-module .module-hero,.admin-teacher-module .module-card,.admin-teacher-module .kpi-card{padding:18px;border-radius:18px}.admin-teacher-module .form-grid,.admin-teacher-module .filter-grid,.admin-teacher-module .info-grid{grid-template-columns:1fr}.admin-teacher-module .module-btn{width:100%} }
</style>