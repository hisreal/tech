<?php /** Shared Outstanding Fees page styles (adapted from the original placeholder). */ ?>
<style>
	.outstanding-page { --out-primary:#0f766e; --out-primary-dark:#115e59; --out-primary-soft:rgba(15,118,110,.1); --out-success:#16a34a; --out-success-soft:rgba(22,163,74,.12); --out-warning:#f59e0b; --out-warning-soft:rgba(245,158,11,.14); --out-danger:#dc2626; --out-danger-soft:rgba(220,38,38,.1); --out-blue:#2563eb; --out-blue-soft:rgba(37,99,235,.1); --out-ink:#10201d; --out-muted:#64748b; --out-border:rgba(15,118,110,.18); --out-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.outstanding-page .out-hero,.outstanding-page .out-card,.outstanding-page .summary-card,.outstanding-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--out-border); box-shadow:var(--out-shadow); }
	.outstanding-page .out-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.outstanding-page .breadcrumb-line { color:var(--out-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.outstanding-page .breadcrumb-line a { color:var(--out-primary-dark); text-decoration:none; }
	.outstanding-page .out-kicker,.outstanding-page .field-icon,.outstanding-page .summary-icon,.outstanding-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.outstanding-page .out-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--out-primary-soft); color:var(--out-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.outstanding-page h3,.outstanding-page h4,.outstanding-page h5 { color:var(--out-ink); font-weight:900; }
	.outstanding-page .out-card,.outstanding-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.outstanding-page .out-card { padding:24px; }
	.outstanding-page .form-label { color:var(--out-ink); font-size:13px; font-weight:900; }
	.outstanding-page .field-wrap { position:relative; }
	.outstanding-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--out-primary); pointer-events:none; }
	.outstanding-page .form-select,.outstanding-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.outstanding-page .form-select:focus,.outstanding-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.outstanding-page .out-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--out-primary),var(--out-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.outstanding-page .out-btn:hover { color:#fff; transform:translateY(-2px); }
	.outstanding-page .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
	.outstanding-page .summary-card:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(15,23,42,.12); }
	.outstanding-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--out-primary-soft); color:var(--out-primary); }
	.outstanding-page .summary-icon.success{background:var(--out-success-soft);color:var(--out-success)}.outstanding-page .summary-icon.warning{background:var(--out-warning-soft);color:#b45309}.outstanding-page .summary-icon.danger{background:var(--out-danger-soft);color:var(--out-danger)}.outstanding-page .summary-icon.blue{background:var(--out-blue-soft);color:var(--out-blue)}
	.outstanding-page .summary-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.outstanding-page .chart-wrap { height:230px; display:flex; align-items:end; gap:14px; padding-top:20px; border-bottom:1px solid rgba(148,163,184,.22); }
	.outstanding-page .chart-bar { flex:1; min-width:24px; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; gap:8px; height:100%; }
	.outstanding-page .bar-fill { width:100%; max-width:44px; border-radius:10px 10px 4px 4px; background:linear-gradient(180deg,#f97316,#dc2626); }
	.outstanding-page .bar-label { color:var(--out-muted); font-size:12px; font-weight:900; }
	.outstanding-page .toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.outstanding-page .table-scroll { max-height:640px; overflow:auto; }
	.outstanding-page .out-table { min-width:1080px; margin-bottom:0; }
	.outstanding-page .out-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--out-primary),var(--out-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.outstanding-page .out-table td { padding:12px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.outstanding-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.outstanding-page .status-unpaid{color:var(--out-danger);background:var(--out-danger-soft)}.outstanding-page .status-partial{color:#b45309;background:var(--out-warning-soft)}
	.outstanding-page .row-actions { display:flex; gap:7px; flex-wrap:wrap; }
	@media(max-width:767.98px){ .outstanding-page .out-hero,.outstanding-page .out-card{padding:20px;border-radius:20px}.outstanding-page .row-actions,.outstanding-page .row-actions .btn,.outstanding-page .out-btn{width:100%}.outstanding-page .chart-wrap{gap:6px} }
</style>
