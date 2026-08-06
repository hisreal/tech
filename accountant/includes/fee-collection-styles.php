<?php /** Shared Fee Collection page styles (adapted from the original placeholder). */ ?>
<style>
	.fee-collection-page { --fc-primary:#0f766e; --fc-primary-dark:#115e59; --fc-primary-soft:rgba(15,118,110,.1); --fc-success:#16a34a; --fc-success-soft:rgba(22,163,74,.12); --fc-warning:#f59e0b; --fc-warning-soft:rgba(245,158,11,.14); --fc-danger:#dc2626; --fc-danger-soft:rgba(220,38,38,.1); --fc-blue:#2563eb; --fc-blue-soft:rgba(37,99,235,.1); --fc-ink:#10201d; --fc-muted:#64748b; --fc-border:rgba(15,118,110,.18); --fc-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.fee-collection-page .fc-hero,.fee-collection-page .fc-card,.fee-collection-page .stat-card,.fee-collection-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--fc-border); box-shadow:var(--fc-shadow); }
	.fee-collection-page .fc-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.fee-collection-page .breadcrumb-line { color:var(--fc-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.fee-collection-page .breadcrumb-line a { color:var(--fc-primary-dark); text-decoration:none; }
	.fee-collection-page .fc-kicker,.fee-collection-page .field-icon,.fee-collection-page .stat-icon,.fee-collection-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.fee-collection-page .fc-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--fc-primary-soft); color:var(--fc-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.fee-collection-page h3,.fee-collection-page h4,.fee-collection-page h5 { color:var(--fc-ink); font-weight:900; }
	.fee-collection-page .fc-card,.fee-collection-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.fee-collection-page .fc-card { padding:24px; }
	.fee-collection-page .form-label { color:var(--fc-ink); font-size:13px; font-weight:900; }
	.fee-collection-page .field-wrap { position:relative; }
	.fee-collection-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--fc-primary); pointer-events:none; }
	.fee-collection-page .form-select,.fee-collection-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.fee-collection-page textarea.form-control { padding:14px; min-height:96px; }
	.fee-collection-page .form-select:focus,.fee-collection-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.fee-collection-page .fc-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--fc-primary),var(--fc-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.fee-collection-page .fc-btn:hover { color:#fff; transform:translateY(-2px); }
	.fee-collection-page .field-error { color:var(--fc-danger); font-size:12px; font-weight:800; margin-top:4px; display:block; }
	.fee-collection-page .stat-card { height:100%; padding:18px; border-radius:20px; }
	.fee-collection-page .stat-icon { width:42px; height:42px; border-radius:14px; background:var(--fc-primary-soft); color:var(--fc-primary); }
	.fee-collection-page .stat-icon.success{background:var(--fc-success-soft);color:var(--fc-success)}.fee-collection-page .stat-icon.warning{background:var(--fc-warning-soft);color:#b45309}.fee-collection-page .stat-icon.danger{background:var(--fc-danger-soft);color:var(--fc-danger)}.fee-collection-page .stat-icon.blue{background:var(--fc-blue-soft);color:var(--fc-blue)}
	.fee-collection-page .stat-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.fee-collection-page .student-photo { width:92px; height:92px; border-radius:24px; object-fit:cover; border:4px solid #fff; box-shadow:0 14px 28px rgba(15,23,42,.14); }
	.fee-collection-page .balance-highlight { padding:18px; border-radius:20px; background:var(--fc-danger-soft); color:var(--fc-danger); font-weight:900; }
	.fee-collection-page .balance-highlight.paid { background:var(--fc-success-soft); color:var(--fc-success); }
	.fee-collection-page .table-scroll { overflow:auto; }
	.fee-collection-page .fc-table { min-width:760px; margin-bottom:0; }
	.fee-collection-page .fc-table thead th { padding:14px 12px; background:linear-gradient(135deg,var(--fc-primary),var(--fc-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.fee-collection-page .fc-table td { padding:13px 12px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.fee-collection-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.fee-collection-page .status-paid{color:var(--fc-success);background:var(--fc-success-soft)}.fee-collection-page .status-unpaid{color:var(--fc-danger);background:var(--fc-danger-soft)}.fee-collection-page .status-partial{color:#b45309;background:var(--fc-warning-soft)}
	.fee-collection-page .action-row { display:flex; gap:10px; flex-wrap:wrap; }
	@media(max-width:767.98px){ .fee-collection-page .fc-hero,.fee-collection-page .fc-card{padding:20px;border-radius:20px}.fee-collection-page .action-row,.fee-collection-page .action-row .btn{width:100%}.fee-collection-page .student-photo{width:78px;height:78px} }
</style>
