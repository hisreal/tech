<?php /** Shared Fee Structure page styles (adapted from the original placeholder). */ ?>
<style>
	.fee-structure-page { --fs-primary:#0f766e; --fs-primary-dark:#115e59; --fs-primary-soft:rgba(15,118,110,.1); --fs-success:#16a34a; --fs-success-soft:rgba(22,163,74,.12); --fs-warning:#f59e0b; --fs-warning-soft:rgba(245,158,11,.14); --fs-danger:#dc2626; --fs-danger-soft:rgba(220,38,38,.1); --fs-blue:#2563eb; --fs-blue-soft:rgba(37,99,235,.1); --fs-ink:#10201d; --fs-muted:#64748b; --fs-border:rgba(15,118,110,.18); --fs-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.fee-structure-page .fs-hero,.fee-structure-page .fs-card,.fee-structure-page .summary-card,.fee-structure-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--fs-border); box-shadow:var(--fs-shadow); }
	.fee-structure-page .fs-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.fee-structure-page .breadcrumb-line { color:var(--fs-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.fee-structure-page .breadcrumb-line a { color:var(--fs-primary-dark); text-decoration:none; }
	.fee-structure-page .fs-kicker,.fee-structure-page .field-icon,.fee-structure-page .summary-icon,.fee-structure-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.fee-structure-page .fs-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--fs-primary-soft); color:var(--fs-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.fee-structure-page h3,.fee-structure-page h4,.fee-structure-page h5 { color:var(--fs-ink); font-weight:900; }
	.fee-structure-page .fs-card,.fee-structure-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.fee-structure-page .fs-card { padding:24px; }
	.fee-structure-page .form-label { color:var(--fs-ink); font-size:13px; font-weight:900; }
	.fee-structure-page .field-wrap { position:relative; }
	.fee-structure-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--fs-primary); pointer-events:none; }
	.fee-structure-page .form-select,.fee-structure-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.fee-structure-page textarea.form-control { padding:14px; min-height:92px; }
	.fee-structure-page .form-select:focus,.fee-structure-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.fee-structure-page .fs-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--fs-primary),var(--fs-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.fee-structure-page .fs-btn:hover { color:#fff; transform:translateY(-2px); }
	.fee-structure-page .field-error { color:var(--fs-danger); font-size:12px; font-weight:800; margin-top:4px; display:block; }
	.fee-structure-page .notice { display:none; gap:8px; align-items:center; padding:12px 14px; border-radius:14px; font-weight:800; margin-bottom:16px; }
	.fee-structure-page .notice.is-visible{display:flex}.fee-structure-page .notice.success{color:var(--fs-success);background:var(--fs-success-soft)}.fee-structure-page .notice.error{color:var(--fs-danger);background:var(--fs-danger-soft)}
	.fee-structure-page .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
	.fee-structure-page .summary-card:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(15,23,42,.12); }
	.fee-structure-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--fs-primary-soft); color:var(--fs-primary); }
	.fee-structure-page .summary-icon.success{background:var(--fs-success-soft);color:var(--fs-success)}.fee-structure-page .summary-icon.warning{background:var(--fs-warning-soft);color:#b45309}.fee-structure-page .summary-icon.blue{background:var(--fs-blue-soft);color:var(--fs-blue)}
	.fee-structure-page .summary-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.fee-structure-page .toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.fee-structure-page .table-scroll { max-height:620px; overflow:auto; }
	.fee-structure-page .structure-table { min-width:1000px; margin-bottom:0; }
	.fee-structure-page .structure-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--fs-primary),var(--fs-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.fee-structure-page .structure-table td { padding:12px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.fee-structure-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.fee-structure-page .status-active{color:var(--fs-success);background:var(--fs-success-soft)}.fee-structure-page .status-inactive{color:var(--fs-danger);background:var(--fs-danger-soft)}.fee-structure-page .status-draft{color:#475569;background:#f1f5f9}.fee-structure-page .status-archived{color:#b45309;background:var(--fs-warning-soft)}
	.fee-structure-page .action-row,.fee-structure-page .bulk-actions { display:flex; gap:7px; flex-wrap:wrap; }
	.fee-structure-page .modal-content { border:0; border-radius:22px; overflow:hidden; }
	@media(max-width:767.98px){ .fee-structure-page .fs-hero,.fee-structure-page .fs-card{padding:20px;border-radius:20px}.fee-structure-page .action-row,.fee-structure-page .action-row .btn,.fee-structure-page .bulk-actions .btn,.fee-structure-page .fs-btn{width:100%} }
</style>
