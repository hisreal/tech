<?php require_once('includes/header.php'); ?>

<!-- Payment dashboard styles: scoped to this student index page only. -->
<style>
	.student-payment-page {
		--pay-primary: #2563eb;
		--pay-primary-soft: rgba(37, 99, 235, 0.1);
		--pay-success: #16a34a;
		--pay-success-soft: rgba(22, 163, 74, 0.12);
		--pay-warning: #f59e0b;
		--pay-warning-soft: rgba(245, 158, 11, 0.14);
		--pay-danger: #dc2626;
		--pay-danger-soft: rgba(220, 38, 38, 0.12);
		--pay-ink: #111827;
		--pay-muted: #64748b;
		--pay-border: rgba(148, 163, 184, 0.24);
	}

	.student-payment-page .payment-card,
	.student-payment-page .payment-summary-card {
		background: rgba(255, 255, 255, 0.96);
		border: 1px solid var(--pay-border);
		border-radius: 22px;
		box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
		backdrop-filter: blur(14px);
		animation: paymentFadeUp .5s ease both;
	}

	.student-payment-page .payment-summary-card {
		min-height: 126px;
		padding: 20px;
		transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
	}

	.student-payment-page .payment-summary-card:hover,
	.student-payment-page .payment-card:hover {
		transform: translateY(-3px);
		box-shadow: 0 20px 54px rgba(15, 23, 42, 0.11);
		border-color: rgba(37, 99, 235, 0.28);
	}

	.student-payment-page .payment-summary-icon,
	.student-payment-page .payment-method-icon,
	.student-payment-page .payment-action {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	.student-payment-page .payment-summary-icon {
		width: 48px;
		height: 48px;
		border-radius: 16px;
		font-size: 18px;
	}

	.student-payment-page .payment-summary-icon.primary { background: var(--pay-primary-soft); color: var(--pay-primary); }
	.student-payment-page .payment-summary-icon.success { background: var(--pay-success-soft); color: var(--pay-success); }
	.student-payment-page .payment-summary-icon.warning { background: var(--pay-warning-soft); color: var(--pay-warning); }

	.student-payment-page .payment-card {
		overflow: hidden;
	}

	.student-payment-page .payment-toolbar {
		gap: 14px;
		padding: 20px;
		border-bottom: 1px solid var(--pay-border);
		background: linear-gradient(180deg, rgba(248, 250, 252, 0.88), rgba(255, 255, 255, 0.96));
	}

	.student-payment-page .payment-filter {
		min-width: 150px;
	}

	.student-payment-page .payment-table-wrap {
		overflow: auto;
		max-height: 620px;
	}

	.student-payment-page .payment-table {
		min-width: 960px;
		margin-bottom: 0;
	}

	.student-payment-page .payment-table thead th {
		position: sticky;
		top: 0;
		z-index: 2;
		background: #f8fafc;
		border-bottom: 1px solid var(--pay-border);
		color: #475569;
		font-size: 12px;
		font-weight: 700;
		letter-spacing: 0;
		text-transform: uppercase;
		white-space: nowrap;
	}

	.student-payment-page .payment-table tbody tr {
		transition: background .2s ease, box-shadow .2s ease, transform .2s ease;
	}

	.student-payment-page .payment-table tbody tr:hover {
		background: #f8fbff;
		box-shadow: inset 4px 0 0 var(--pay-primary);
	}

	.student-payment-page .payment-table td {
		vertical-align: middle;
		color: var(--pay-ink);
		border-bottom-color: rgba(226, 232, 240, 0.9);
	}

	.student-payment-page .payment-method-icon {
		width: 38px;
		height: 38px;
		border-radius: 14px;
		background: var(--pay-primary-soft);
		color: var(--pay-primary);
	}

	.student-payment-page .payment-status {
		border-radius: 999px;
		padding: 7px 12px;
		font-size: 12px;
		font-weight: 700;
	}

	.student-payment-page .payment-status.paid {
		background: var(--pay-success-soft);
		color: var(--pay-success);
	}

	.student-payment-page .payment-status.partial {
		background: var(--pay-warning-soft);
		color: var(--pay-warning);
	}

	.student-payment-page .payment-status.pending {
		background: var(--pay-danger-soft);
		color: var(--pay-danger);
	}

	.student-payment-page .payment-action {
		width: 34px;
		height: 34px;
		border-radius: 12px;
		border: 1px solid var(--pay-border);
		background: #fff;
		color: var(--pay-muted);
		transition: all .2s ease;
	}

	.student-payment-page .payment-action:hover {
		background: var(--pay-primary);
		border-color: var(--pay-primary);
		color: #fff;
		box-shadow: 0 10px 22px rgba(37, 99, 235, 0.2);
	}

	.student-payment-page .empty-state {
		display: none;
		padding: 34px 16px;
		color: var(--pay-muted);
	}

	@keyframes paymentFadeUp {
		from { opacity: 0; transform: translateY(12px); }
		to { opacity: 1; transform: translateY(0); }
	}

	@media (max-width: 767.98px) {
		.student-payment-page .payment-toolbar { padding: 16px; }
		.student-payment-page .payment-filter,
		.student-payment-page .payment-toolbar .input-icon,
		.student-payment-page .payment-toolbar .form-control,
		.student-payment-page .payment-toolbar .form-select { width: 100%; }
	}
</style>

<div class="row student-payment-page">
	<div class="col-lg-12 mx-auto">
		<!-- Page title: introduces the dashboard payment module. -->
		<div class="page-title d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
			<div>
				<span class="badge bg-primary-transparent text-primary mb-2">Student Finance</span>
				<h5 class="mb-1">Payment History</h5>
				<p class="text-muted mb-0">Track school fees, payment methods, receipts, and balances.</p>
			</div>
		</div>

		<!-- Payment summary cards: totals are calculated automatically from table records. -->
		<section class="row g-3 mb-4" aria-label="Payment summary">
			<div class="col-sm-6 col-xl-4">
				<div class="payment-summary-card">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<span class="payment-summary-icon primary"><i class="fa-solid fa-file-invoice-dollar"></i></span>
						<span class="text-muted small">Total Bill</span>
					</div>
					<h3 class="mb-0" id="totalBill">N0</h3>
					<p class="text-muted mb-0">Total amount billed</p>
				</div>
			</div>
			<div class="col-sm-6 col-xl-4">
				<div class="payment-summary-card">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<span class="payment-summary-icon success"><i class="fa-solid fa-circle-check"></i></span>
						<span class="text-muted small">Paid</span>
					</div>
					<h3 class="mb-0" id="totalPaid">N0</h3>
					<p class="text-muted mb-0">Amount received</p>
				</div>
			</div>
			<div class="col-sm-6 col-xl-4">
				<div class="payment-summary-card">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<span class="payment-summary-icon warning"><i class="fa-solid fa-wallet"></i></span>
						<span class="text-muted small">Balance</span>
					</div>
					<h3 class="mb-0" id="totalBalance">N0</h3>
					<p class="text-muted mb-0">Outstanding amount</p>
				</div>
			</div>
		</section>

		<!-- Payment history table: searchable, filterable, sortable, and horizontally scrollable on mobile. -->
		<section class="payment-card mb-4">
			<div class="payment-toolbar d-flex align-items-center justify-content-between flex-wrap">
				<div>
					<h5 class="mb-1 fs-18">Recent Transactions</h5>
					<p class="text-muted mb-0">Search by date, description, method, or receipt number.</p>
				</div>
				<div class="d-flex align-items-center flex-wrap gap-2">
					<div class="input-icon">
						<span class="input-icon-addon"><i class="isax isax-search-normal-14"></i></span>
						<input type="text" class="form-control form-control-md" id="paymentSearch" placeholder="Search payments">
					</div>
					<select class="form-select form-control-md payment-filter" id="paymentStatusFilter" aria-label="Filter payment status">
						<option value="all">All Status</option>
						<option value="paid">Paid</option>
						<option value="partial">Partial</option>
						<option value="pending">Pending</option>
					</select>
					<select class="form-select form-control-md payment-filter" id="paymentDateSort" aria-label="Sort payments by date">
						<option value="desc">Newest first</option>
						<option value="asc">Oldest first</option>
					</select>
				</div>
			</div>

			<div class="payment-table-wrap">
				<table class="table payment-table align-middle" id="paymentTable">
					<thead>
						<tr>
							<th>Receipt ID</th>
							<th>Date</th>
							<th>Description</th>
							<th>Class</th>
							<th>Session</th>
							<th>Term</th>
						
							<th>Amount</th>
						
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<tr data-date="2026-06-30" data-status="paid" data-bill="15000" data-paid="15000">
							<td><span class="fw-semibold text-primary">#PAY-1032</span></td>
							<td>30/06/2026</td>
							<td>Third Term Tuition</td>
							<td>SS 2</td>
							<td>2025/2026</td>
							<td>Term 3</td>
							<td>N15,000</td>
							<td><div class="d-flex gap-1"><a href="javascript:void(0);" class="payment-action" title="View receipt"><i class="fa-solid fa-eye"></i></a><a href="javascript:void(0);" class="payment-action" title="Download receipt"><i class="fa-solid fa-download"></i></a></div></td>
						</tr>
					</tbody>
				</table>
				<div class="empty-state text-center" id="paymentEmptyState">
					<i class="fa-solid fa-magnifying-glass fs-24 d-block mb-2"></i>
					No payment records match your search.
				</div>
			</div>
		</section>
	</div>
</div>
</div>
</div>

<!-- Payment table behavior: search, status filtering, date sorting, and live summary totals. -->
<script>
	document.addEventListener('DOMContentLoaded', function () {
		const table = document.getElementById('paymentTable');
		const tbody = table.querySelector('tbody');
		const searchInput = document.getElementById('paymentSearch');
		const statusFilter = document.getElementById('paymentStatusFilter');
		const dateSort = document.getElementById('paymentDateSort');
		const emptyState = document.getElementById('paymentEmptyState');
		const rows = Array.from(tbody.querySelectorAll('tr'));
		const currencyFormatter = new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 });

		function formatCurrency(amount) {
			return currencyFormatter.format(amount).replace('NGN', 'N').trim();
		}

		function sortRows() {
			const sortedRows = Array.from(tbody.querySelectorAll('tr')).sort((a, b) => {
				const aDate = new Date(a.dataset.date);
				const bDate = new Date(b.dataset.date);
				return dateSort.value === 'asc' ? aDate - bDate : bDate - aDate;
			});

			sortedRows.forEach(row => tbody.appendChild(row));
		}

		function updateSummary(visibleRows) {
			const totalBill = visibleRows.reduce((sum, row) => sum + Number(row.dataset.bill || 0), 0);
			const totalPaid = visibleRows.reduce((sum, row) => sum + Number(row.dataset.paid || 0), 0);
			document.getElementById('totalBill').textContent = formatCurrency(totalBill);
			document.getElementById('totalPaid').textContent = formatCurrency(totalPaid);
			document.getElementById('totalBalance').textContent = formatCurrency(totalBill - totalPaid);
		}

		function applyControls() {
			const query = searchInput.value.trim().toLowerCase();
			const status = statusFilter.value;
			const visibleRows = [];

			rows.forEach(row => {
				const rowText = row.textContent.toLowerCase() + ' ' + row.dataset.date;
				const matchesSearch = !query || rowText.includes(query);
				const matchesStatus = status === 'all' || row.dataset.status === status;
				const shouldShow = matchesSearch && matchesStatus;

				row.style.display = shouldShow ? '' : 'none';
				if (shouldShow) {
					visibleRows.push(row);
				}
			});

			emptyState.style.display = visibleRows.length ? 'none' : 'block';
			updateSummary(visibleRows);
		}

		searchInput.addEventListener('input', applyControls);
		statusFilter.addEventListener('change', applyControls);
		dateSort.addEventListener('change', function () {
			sortRows();
			applyControls();
		});

		sortRows();
		applyControls();
	});
</script>

<?php require_once('includes/footer.php'); ?>