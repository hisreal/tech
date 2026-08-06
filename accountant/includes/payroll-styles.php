<?php /** Shared Payroll Management page styles (mirrors fee-structure-styles.php for visual consistency). */ ?>
<style>
	.payroll-page { --pr-primary:#0f766e; --pr-primary-dark:#115e59; --pr-primary-soft:rgba(15,118,110,.1); --pr-success:#16a34a; --pr-success-soft:rgba(22,163,74,.12); --pr-warning:#f59e0b; --pr-warning-soft:rgba(245,158,11,.14); --pr-danger:#dc2626; --pr-danger-soft:rgba(220,38,38,.1); --pr-blue:#2563eb; --pr-blue-soft:rgba(37,99,235,.1); --pr-ink:#10201d; --pr-muted:#64748b; --pr-border:rgba(15,118,110,.18); --pr-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.payroll-page .pr-hero,.payroll-page .pr-card,.payroll-page .summary-card,.payroll-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--pr-border); box-shadow:var(--pr-shadow); }
	.payroll-page .pr-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.payroll-page .breadcrumb-line { color:var(--pr-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.payroll-page .breadcrumb-line a { color:var(--pr-primary-dark); text-decoration:none; }
	.payroll-page .pr-kicker,.payroll-page .field-icon,.payroll-page .summary-icon,.payroll-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.payroll-page .pr-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--pr-primary-soft); color:var(--pr-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.payroll-page h3,.payroll-page h4,.payroll-page h5 { color:var(--pr-ink); font-weight:900; }
	.payroll-page .pr-card,.payroll-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.payroll-page .pr-card { padding:24px; }
	.payroll-page .form-label { color:var(--pr-ink); font-size:13px; font-weight:900; }
	.payroll-page .field-wrap { position:relative; }
	.payroll-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--pr-primary); pointer-events:none; }
	.payroll-page .form-select,.payroll-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.payroll-page textarea.form-control { padding:14px; min-height:92px; }
	.payroll-page .form-select:focus,.payroll-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.payroll-page .pr-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--pr-primary),var(--pr-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.payroll-page .pr-btn:hover { color:#fff; transform:translateY(-2px); }
	.payroll-page .field-error { color:var(--pr-danger); font-size:12px; font-weight:800; margin-top:4px; display:block; }
	.payroll-page .notice { display:none; gap:8px; align-items:center; padding:12px 14px; border-radius:14px; font-weight:800; margin-bottom:16px; }
	.payroll-page .notice.is-visible{display:flex}.payroll-page .notice.success{color:var(--pr-success);background:var(--pr-success-soft)}.payroll-page .notice.error{color:var(--pr-danger);background:var(--pr-danger-soft)}
	.payroll-page .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
	.payroll-page .summary-card:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(15,23,42,.12); }
	.payroll-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--pr-primary-soft); color:var(--pr-primary); }
	.payroll-page .summary-icon.success{background:var(--pr-success-soft);color:var(--pr-success)}.payroll-page .summary-icon.warning{background:var(--pr-warning-soft);color:#b45309}.payroll-page .summary-icon.blue{background:var(--pr-blue-soft);color:var(--pr-blue)}.payroll-page .summary-icon.danger{background:var(--pr-danger-soft);color:var(--pr-danger)}
	.payroll-page .summary-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.payroll-page .toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.payroll-page .table-scroll { max-height:620px; overflow:auto; }
	.payroll-page .pr-table { min-width:1000px; margin-bottom:0; }
	.payroll-page .pr-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--pr-primary),var(--pr-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.payroll-page .pr-table td { padding:12px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.payroll-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.payroll-page .status-active,.payroll-page .status-paid,.payroll-page .status-processed{color:var(--pr-success);background:var(--pr-success-soft)}.payroll-page .status-inactive,.payroll-page .status-cancelled{color:var(--pr-danger);background:var(--pr-danger-soft)}.payroll-page .status-draft{color:#475569;background:#f1f5f9}.payroll-page .status-generated{color:var(--pr-blue);background:var(--pr-blue-soft)}
	.payroll-page .action-row,.payroll-page .bulk-actions { display:flex; gap:7px; flex-wrap:wrap; }
	.payroll-page .modal-content { border:0; border-radius:22px; overflow:hidden; }
	.payroll-page .mini-list{display:grid;gap:10px}.payroll-page .mini-item{display:flex;justify-content:space-between;gap:12px;padding:11px 13px;border-radius:14px;background:#f8fafc;border:1px solid rgba(148,163,184,.22);font-weight:800}
	.payroll-page .chart-wrap{height:230px;display:flex;align-items:end;gap:10px;padding-top:20px;border-bottom:1px solid rgba(148,163,184,.22)}.payroll-page .chart-bar{flex:1;min-width:18px;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;gap:8px;height:100%}.payroll-page .bar-fill{width:60%;max-width:34px;border-radius:8px 8px 3px 3px;background:linear-gradient(180deg,#16a34a,#0f766e)}.payroll-page .bar-label{color:var(--pr-muted);font-size:11px;font-weight:900}
	@media(max-width:767.98px){ .payroll-page .pr-hero,.payroll-page .pr-card{padding:20px;border-radius:20px}.payroll-page .action-row,.payroll-page .action-row .btn,.payroll-page .bulk-actions .btn,.payroll-page .pr-btn{width:100%} }
	@media print{.sidebar,.header,.navbar,.payroll-page .pr-btn,.payroll-page .action-row{display:none!important}.payroll-page .pr-card,.payroll-page .table-card{box-shadow:none;border-color:#d7e5df}.payroll-page .pr-table{min-width:100%}}
</style>
