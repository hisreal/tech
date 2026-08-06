<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Paginator;
use App\Traits\Auditable;

/**
 * Backing service for Payroll Management: salary structures, allowance and
 * deduction types (with per-staff assignment), payroll run generation into
 * frozen payslips, payment recording, and reporting.
 */
final class PayrollService
{
    use Auditable;

    private const DEFAULT_PER_PAGE = 15;
    private const MONTHS = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Select helpers
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function staffForSelect(): array
    {
        return $this->db->fetchAll(
            "SELECT id, staff_no, first_name, last_name, staff_type, department_id FROM staff WHERE employment_status = 'active' ORDER BY last_name ASC, first_name ASC"
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function departmentsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM departments WHERE status = "active" ORDER BY name ASC');
    }

    public static function monthName(int $month): string
    {
        return self::MONTHS[$month] ?? '-';
    }

    /** @return array<int,string> */
    public static function months(): array
    {
        return self::MONTHS;
    }

    // ------------------------------------------------------------------
    // Salary structures
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listSalaryStructures(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $where = ['1=1'];
        $params = [];

        if (($staffId = $this->intFilter($filters['staff_id'] ?? 0)) !== null) { $where[] = 's.staff_id = :staff_id'; $params['staff_id'] = $staffId; }
        if (($status = trim((string) ($filters['status'] ?? ''))) !== '' && in_array($status, ['active', 'inactive'], true)) { $where[] = 's.status = :status'; $params['status'] = $status; }
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = "(st.first_name LIKE :search1 OR st.last_name LIKE :search2 OR st.staff_no LIKE :search3)";
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT s.*, st.staff_no, st.first_name, st.last_name, st.staff_type, d.name AS department_name
                 FROM salary_structures s
                 INNER JOIN staff st ON st.id = s.staff_id
                 LEFT JOIN departments d ON d.id = st.department_id
                 WHERE {$whereSql}
                 ORDER BY s.status = 'active' DESC, s.effective_date DESC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    /** @return array<string,mixed>|null */
    public function currentSalary(int $staffId): ?array
    {
        return $this->db->fetchOne('SELECT * FROM salary_structures WHERE staff_id = :sid AND status = "active" ORDER BY effective_date DESC LIMIT 1', ['sid' => $staffId]);
    }

    public function saveSalaryStructure(array $data, ?int $id, ?array $actor): array
    {
        $staffId = (int) ($data['staff_id'] ?? 0);
        $basicSalary = $data['basic_salary'] !== '' && $data['basic_salary'] !== null ? (float) $data['basic_salary'] : -1;
        $effectiveDate = trim((string) ($data['effective_date'] ?? ''));
        $status = in_array($data['status'] ?? '', ['active', 'inactive'], true) ? $data['status'] : 'active';

        $errors = [];
        if ($staffId < 1) { $errors['staff_id'] = 'Select a staff member.'; }
        if ($basicSalary < 0) { $errors['basic_salary'] = 'Enter a valid basic salary.'; }
        if ($effectiveDate === '') { $errors['effective_date'] = 'Effective date is required.'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = ['staff_id' => $staffId, 'basic_salary' => $basicSalary, 'effective_date' => $effectiveDate, 'status' => $status];

        $this->db->beginTransaction();
        try {
            if ($id) {
                $before = $this->db->fetchOne('SELECT * FROM salary_structures WHERE id = :id', ['id' => $id]);
                if ($before === null) {
                    $this->db->rollBack();

                    return ['success' => false, 'message' => 'Salary record not found.'];
                }
                if ($status === 'active') {
                    $this->db->execute('UPDATE salary_structures SET status = "inactive" WHERE staff_id = :sid AND id <> :id', ['sid' => $staffId, 'id' => $id]);
                }
                $this->db->execute('UPDATE salary_structures SET staff_id=:staff_id, basic_salary=:basic_salary, effective_date=:effective_date, status=:status WHERE id=:id', array_merge($payload, ['id' => $id]));
                $this->audit($actor, 'payroll', 'payroll.salary.updated', 'salary_structures', $id, $before, $payload);
            } else {
                if ($status === 'active') {
                    $this->db->execute('UPDATE salary_structures SET status = "inactive" WHERE staff_id = :sid', ['sid' => $staffId]);
                }
                $payload['created_by'] = isset($actor['id']) ? (int) $actor['id'] : null;
                $columns = implode(', ', array_keys($payload));
                $placeholders = implode(', ', array_map(static fn (string $k): string => ":{$k}", array_keys($payload)));
                $this->db->execute("INSERT INTO salary_structures ({$columns}) VALUES ({$placeholders})", $payload);
                $id = (int) $this->db->lastInsertId();
                $this->audit($actor, 'payroll', 'payroll.salary.created', 'salary_structures', $id, null, $payload);
            }
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);

            return ['success' => false, 'message' => 'Unable to save this salary record right now.'];
        }

        return ['success' => true, 'message' => 'Salary structure saved successfully.'];
    }

    public function deleteSalaryStructure(int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM salary_structures WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Salary record not found.'];
        }
        $used = $this->db->fetchOne('SELECT 1 FROM payslips WHERE staff_id = :sid LIMIT 1', ['sid' => $before['staff_id']]);
        if ($used) {
            return ['success' => false, 'message' => 'This staff member already has payslips on record. Set the salary to inactive instead of deleting.'];
        }
        $this->db->execute('DELETE FROM salary_structures WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'payroll', 'payroll.salary.deleted', 'salary_structures', $id, $before, null);

        return ['success' => true, 'message' => 'Salary record deleted successfully.'];
    }

    // ------------------------------------------------------------------
    // Allowance / deduction types
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function listAllowanceTypes(): array
    {
        return $this->db->fetchAll('SELECT * FROM allowance_types ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function listDeductionTypes(): array
    {
        return $this->db->fetchAll('SELECT * FROM deduction_types ORDER BY name ASC');
    }

    public function saveAllowanceType(array $data, ?int $id, ?array $actor): array
    {
        return $this->saveComponentType('allowance_types', 'payroll.allowance_type', $data, $id, $actor);
    }

    public function saveDeductionType(array $data, ?int $id, ?array $actor): array
    {
        return $this->saveComponentType('deduction_types', 'payroll.deduction_type', $data, $id, $actor);
    }

    private function saveComponentType(string $table, string $auditPrefix, array $data, ?int $id, ?array $actor): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $calculationType = in_array($data['calculation_type'] ?? '', ['fixed', 'percentage'], true) ? $data['calculation_type'] : 'fixed';
        $defaultAmount = $data['default_amount'] !== '' && $data['default_amount'] !== null ? (float) $data['default_amount'] : 0.0;
        $status = in_array($data['status'] ?? '', ['active', 'inactive'], true) ? $data['status'] : 'active';

        $errors = [];
        if ($name === '') { $errors['name'] = 'Name is required.'; }
        if ($defaultAmount < 0) { $errors['default_amount'] = 'Amount cannot be negative.'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = ['name' => $name, 'calculation_type' => $calculationType, 'default_amount' => $defaultAmount, 'status' => $status];

        if ($id) {
            $before = $this->db->fetchOne("SELECT * FROM {$table} WHERE id = :id", ['id' => $id]);
            if ($before === null) {
                return ['success' => false, 'message' => 'Record not found.'];
            }
            try {
                $sets = implode(', ', array_map(static fn (string $k): string => "{$k} = :{$k}", array_keys($payload)));
                $this->db->execute("UPDATE {$table} SET {$sets} WHERE id = :id", array_merge($payload, ['id' => $id]));
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => 'A record with this name already exists.'];
            }
            $this->audit($actor, 'payroll', "{$auditPrefix}.updated", $table, $id, $before, $payload);

            return ['success' => true, 'message' => 'Updated successfully.'];
        }

        try {
            $columns = implode(', ', array_keys($payload));
            $placeholders = implode(', ', array_map(static fn (string $k): string => ":{$k}", array_keys($payload)));
            $this->db->execute("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})", $payload);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'A record with this name already exists.'];
        }
        $newId = (int) $this->db->lastInsertId();
        $this->audit($actor, 'payroll', "{$auditPrefix}.created", $table, $newId, null, $payload);

        return ['success' => true, 'message' => 'Created successfully.'];
    }

    public function deleteAllowanceType(int $id, ?array $actor): array
    {
        return $this->deleteComponentType('allowance_types', 'staff_allowances', 'allowance_type_id', 'payroll.allowance_type.deleted', $id, $actor);
    }

    public function deleteDeductionType(int $id, ?array $actor): array
    {
        return $this->deleteComponentType('deduction_types', 'staff_deductions', 'deduction_type_id', 'payroll.deduction_type.deleted', $id, $actor);
    }

    private function deleteComponentType(string $table, string $usageTable, string $usageColumn, string $auditAction, int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne("SELECT * FROM {$table} WHERE id = :id", ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Record not found.'];
        }
        $used = $this->db->fetchOne("SELECT 1 FROM {$usageTable} WHERE {$usageColumn} = :id LIMIT 1", ['id' => $id]);
        if ($used) {
            return ['success' => false, 'message' => 'This is assigned to staff members and cannot be deleted. Deactivate it instead.'];
        }
        $this->db->execute("DELETE FROM {$table} WHERE id = :id", ['id' => $id]);
        $this->audit($actor, 'payroll', $auditAction, $table, $id, $before, null);

        return ['success' => true, 'message' => 'Deleted successfully.'];
    }

    // ------------------------------------------------------------------
    // Staff allowance / deduction assignment
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function staffAllowances(int $staffId): array
    {
        return $this->db->fetchAll(
            'SELECT sa.*, at.name, at.calculation_type FROM staff_allowances sa INNER JOIN allowance_types at ON at.id = sa.allowance_type_id WHERE sa.staff_id = :sid ORDER BY at.name ASC',
            ['sid' => $staffId]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function staffDeductions(int $staffId): array
    {
        return $this->db->fetchAll(
            'SELECT sd.*, dt.name, dt.calculation_type FROM staff_deductions sd INNER JOIN deduction_types dt ON dt.id = sd.deduction_type_id WHERE sd.staff_id = :sid ORDER BY dt.name ASC',
            ['sid' => $staffId]
        );
    }

    /** @return array<int,array<string,mixed>> All active staff allowance assignments, for listing/reports. */
    public function allStaffAllowances(): array
    {
        return $this->db->fetchAll(
            "SELECT sa.*, at.name, st.first_name, st.last_name, st.staff_no FROM staff_allowances sa
             INNER JOIN allowance_types at ON at.id = sa.allowance_type_id
             INNER JOIN staff st ON st.id = sa.staff_id
             ORDER BY st.last_name ASC, at.name ASC"
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function allStaffDeductions(): array
    {
        return $this->db->fetchAll(
            "SELECT sd.*, dt.name, st.first_name, st.last_name, st.staff_no FROM staff_deductions sd
             INNER JOIN deduction_types dt ON dt.id = sd.deduction_type_id
             INNER JOIN staff st ON st.id = sd.staff_id
             ORDER BY st.last_name ASC, dt.name ASC"
        );
    }

    public function saveStaffAllowance(array $data, ?int $id, ?array $actor): array
    {
        return $this->saveStaffComponent('staff_allowances', 'allowance_type_id', 'payroll.staff_allowance', $data, $id, $actor);
    }

    public function saveStaffDeduction(array $data, ?int $id, ?array $actor): array
    {
        return $this->saveStaffComponent('staff_deductions', 'deduction_type_id', 'payroll.staff_deduction', $data, $id, $actor);
    }

    private function saveStaffComponent(string $table, string $typeColumn, string $auditPrefix, array $data, ?int $id, ?array $actor): array
    {
        $staffId = (int) ($data['staff_id'] ?? 0);
        $typeId = (int) ($data[$typeColumn] ?? 0);
        $amount = $data['amount'] !== '' && $data['amount'] !== null ? (float) $data['amount'] : -1;
        $status = in_array($data['status'] ?? '', ['active', 'inactive'], true) ? $data['status'] : 'active';

        $errors = [];
        if ($staffId < 1) { $errors['staff_id'] = 'Select a staff member.'; }
        if ($typeId < 1) { $errors[$typeColumn] = 'Select a type.'; }
        if ($amount < 0) { $errors['amount'] = 'Enter a valid amount.'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = ['staff_id' => $staffId, $typeColumn => $typeId, 'amount' => $amount, 'status' => $status];

        if ($id) {
            $before = $this->db->fetchOne("SELECT * FROM {$table} WHERE id = :id", ['id' => $id]);
            if ($before === null) {
                return ['success' => false, 'message' => 'Record not found.'];
            }
            try {
                $sets = implode(', ', array_map(static fn (string $k): string => "{$k} = :{$k}", array_keys($payload)));
                $this->db->execute("UPDATE {$table} SET {$sets} WHERE id = :id", array_merge($payload, ['id' => $id]));
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => 'This staff member already has this assigned.'];
            }
            $this->audit($actor, 'payroll', "{$auditPrefix}.updated", $table, $id, $before, $payload);

            return ['success' => true, 'message' => 'Assignment updated successfully.'];
        }

        try {
            $columns = implode(', ', array_keys($payload));
            $placeholders = implode(', ', array_map(static fn (string $k): string => ":{$k}", array_keys($payload)));
            $this->db->execute("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})", $payload);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'This staff member already has this assigned.'];
        }
        $newId = (int) $this->db->lastInsertId();
        $this->audit($actor, 'payroll', "{$auditPrefix}.created", $table, $newId, null, $payload);

        return ['success' => true, 'message' => 'Assigned successfully.'];
    }

    public function deleteStaffAllowance(int $id, ?array $actor): array
    {
        return $this->deleteStaffComponent('staff_allowances', 'payroll.staff_allowance.deleted', $id, $actor);
    }

    public function deleteStaffDeduction(int $id, ?array $actor): array
    {
        return $this->deleteStaffComponent('staff_deductions', 'payroll.staff_deduction.deleted', $id, $actor);
    }

    private function deleteStaffComponent(string $table, string $auditAction, int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne("SELECT * FROM {$table} WHERE id = :id", ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Record not found.'];
        }
        $this->db->execute("DELETE FROM {$table} WHERE id = :id", ['id' => $id]);
        $this->audit($actor, 'payroll', $auditAction, $table, $id, $before, null);

        return ['success' => true, 'message' => 'Removed successfully.'];
    }

    // ------------------------------------------------------------------
    // Payroll runs & payslip generation
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function listPayrollRuns(): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, (SELECT COUNT(*) FROM payslips p WHERE p.payroll_run_id = r.id) AS payslip_count,
                (SELECT COALESCE(SUM(net_pay), 0) FROM payslips p WHERE p.payroll_run_id = r.id) AS total_net_pay
             FROM payroll_runs r ORDER BY period_year DESC, period_month DESC"
        );
    }

    /** @return array<string,mixed>|null */
    public function findPayrollRun(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM payroll_runs WHERE id = :id', ['id' => $id]);
    }

    public function findOrCreatePayrollRun(int $month, int $year, ?array $actor): array
    {
        $existing = $this->db->fetchOne('SELECT * FROM payroll_runs WHERE period_month = :m AND period_year = :y', ['m' => $month, 'y' => $year]);
        if ($existing) {
            return ['success' => true, 'run' => $existing];
        }

        $this->db->execute('INSERT INTO payroll_runs (period_month, period_year, status) VALUES (:m, :y, "draft")', ['m' => $month, 'y' => $year]);
        $id = (int) $this->db->lastInsertId();
        $this->audit($actor, 'payroll', 'payroll.run.created', 'payroll_runs', $id, null, ['period_month' => $month, 'period_year' => $year]);

        return ['success' => true, 'run' => $this->db->fetchOne('SELECT * FROM payroll_runs WHERE id = :id', ['id' => $id])];
    }

    /** Generates/refreshes payslips for every staff member with an active salary. */
    public function generatePayslips(int $runId, ?array $actor): array
    {
        $run = $this->db->fetchOne('SELECT * FROM payroll_runs WHERE id = :id', ['id' => $runId]);
        if ($run === null) {
            return ['success' => false, 'message' => 'Payroll run not found.'];
        }
        if ($run['status'] === 'paid') {
            return ['success' => false, 'message' => 'This payroll run has already been paid and cannot be regenerated.'];
        }

        $salaries = $this->db->fetchAll('SELECT * FROM salary_structures WHERE status = "active"');
        if (!$salaries) {
            return ['success' => false, 'message' => 'No staff have an active salary structure yet.'];
        }

        $count = 0;
        $this->db->beginTransaction();
        try {
            foreach ($salaries as $salary) {
                $staffId = (int) $salary['staff_id'];
                $basic = (float) $salary['basic_salary'];

                $allowances = $this->db->fetchAll(
                    'SELECT sa.amount, at.name FROM staff_allowances sa INNER JOIN allowance_types at ON at.id = sa.allowance_type_id WHERE sa.staff_id = :sid AND sa.status = "active"',
                    ['sid' => $staffId]
                );
                $deductions = $this->db->fetchAll(
                    'SELECT sd.amount, dt.name FROM staff_deductions sd INNER JOIN deduction_types dt ON dt.id = sd.deduction_type_id WHERE sd.staff_id = :sid AND sd.status = "active"',
                    ['sid' => $staffId]
                );

                $totalAllowances = array_sum(array_column($allowances, 'amount'));
                $totalDeductions = array_sum(array_column($deductions, 'amount'));
                $netPay = $basic + $totalAllowances - $totalDeductions;

                $this->db->execute(
                    'INSERT INTO payslips (payroll_run_id, staff_id, basic_salary, total_allowances, total_deductions, net_pay, status)
                     VALUES (:run_id, :staff_id, :basic, :allowances, :deductions, :net, "generated")
                     ON DUPLICATE KEY UPDATE basic_salary=VALUES(basic_salary), total_allowances=VALUES(total_allowances),
                        total_deductions=VALUES(total_deductions), net_pay=VALUES(net_pay), status=IF(status="paid","paid","generated")',
                    ['run_id' => $runId, 'staff_id' => $staffId, 'basic' => $basic, 'allowances' => $totalAllowances, 'deductions' => $totalDeductions, 'net' => $netPay]
                );
                $payslipId = (int) $this->db->fetchOne('SELECT id FROM payslips WHERE payroll_run_id = :r AND staff_id = :s', ['r' => $runId, 's' => $staffId])['id'];

                $this->db->execute('DELETE FROM payslip_items WHERE payslip_id = :id', ['id' => $payslipId]);
                foreach ($allowances as $allowance) {
                    $this->db->execute('INSERT INTO payslip_items (payslip_id, item_type, label, amount) VALUES (:pid, "allowance", :label, :amount)', ['pid' => $payslipId, 'label' => $allowance['name'], 'amount' => $allowance['amount']]);
                }
                foreach ($deductions as $deduction) {
                    $this->db->execute('INSERT INTO payslip_items (payslip_id, item_type, label, amount) VALUES (:pid, "deduction", :label, :amount)', ['pid' => $payslipId, 'label' => $deduction['name'], 'amount' => $deduction['amount']]);
                }
                $count++;
            }

            $this->db->execute('UPDATE payroll_runs SET status = "processed", processed_by = :uid, processed_at = :now WHERE id = :id', [
                'uid' => isset($actor['id']) ? (int) $actor['id'] : null, 'now' => date('Y-m-d H:i:s'), 'id' => $runId,
            ]);
            $this->audit($actor, 'payroll', 'payroll.run.processed', 'payroll_runs', $runId, null, ['payslips_generated' => $count]);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);

            return ['success' => false, 'message' => 'Unable to generate payslips right now.'];
        }

        return ['success' => true, 'message' => "Generated {$count} payslip(s) successfully."];
    }

    // ------------------------------------------------------------------
    // Payslips
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listPayslips(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $where = ['1=1'];
        $params = [];

        if (($runId = $this->intFilter($filters['payroll_run_id'] ?? 0)) !== null) { $where[] = 'p.payroll_run_id = :run_id'; $params['run_id'] = $runId; }
        if (($staffId = $this->intFilter($filters['staff_id'] ?? 0)) !== null) { $where[] = 'p.staff_id = :staff_id'; $params['staff_id'] = $staffId; }
        if (($status = trim((string) ($filters['status'] ?? ''))) !== '' && in_array($status, ['draft', 'generated', 'paid', 'cancelled'], true)) { $where[] = 'p.status = :status'; $params['status'] = $status; }
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = "(st.first_name LIKE :search1 OR st.last_name LIKE :search2 OR st.staff_no LIKE :search3)";
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT p.*, st.staff_no, st.first_name, st.last_name, st.staff_type, r.period_month, r.period_year
                 FROM payslips p
                 INNER JOIN staff st ON st.id = p.staff_id
                 INNER JOIN payroll_runs r ON r.id = p.payroll_run_id
                 WHERE {$whereSql}
                 ORDER BY r.period_year DESC, r.period_month DESC, st.last_name ASC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    /** @return array<string,mixed>|null */
    public function findPayslip(int $id): ?array
    {
        $payslip = $this->db->fetchOne(
            "SELECT p.*, st.staff_no, st.first_name, st.last_name, st.staff_type, st.designation, d.name AS department_name, r.period_month, r.period_year
             FROM payslips p
             INNER JOIN staff st ON st.id = p.staff_id
             LEFT JOIN departments d ON d.id = st.department_id
             INNER JOIN payroll_runs r ON r.id = p.payroll_run_id
             WHERE p.id = :id",
            ['id' => $id]
        );
        if ($payslip === null) {
            return null;
        }
        $payslip['items'] = $this->db->fetchAll('SELECT * FROM payslip_items WHERE payslip_id = :id ORDER BY item_type ASC, label ASC', ['id' => $id]);
        $payslip['payments'] = $this->db->fetchAll('SELECT * FROM payroll_payments WHERE payslip_id = :id ORDER BY paid_at DESC', ['id' => $id]);

        return $payslip;
    }

    // ------------------------------------------------------------------
    // Payments
    // ------------------------------------------------------------------

    public function recordPayment(int $payslipId, array $data, ?array $actor): array
    {
        $payslip = $this->db->fetchOne('SELECT * FROM payslips WHERE id = :id', ['id' => $payslipId]);
        if ($payslip === null) {
            return ['success' => false, 'message' => 'Payslip not found.'];
        }
        if ($payslip['status'] === 'paid') {
            return ['success' => false, 'message' => 'This payslip has already been paid.'];
        }
        if ($payslip['status'] !== 'generated') {
            return ['success' => false, 'message' => 'This payslip is not ready for payment.'];
        }

        $method = in_array($data['payment_method'] ?? '', ['bank_transfer', 'cash', 'cheque'], true) ? $data['payment_method'] : 'bank_transfer';
        $reference = trim((string) ($data['reference_no'] ?? '')) ?: null;
        $notes = trim((string) ($data['notes'] ?? '')) ?: null;

        $this->db->beginTransaction();
        try {
            $this->db->execute(
                'INSERT INTO payroll_payments (payslip_id, amount, payment_method, reference_no, paid_by, paid_at, notes) VALUES (:pid, :amount, :method, :ref, :paid_by, :now, :notes)',
                ['pid' => $payslipId, 'amount' => $payslip['net_pay'], 'method' => $method, 'ref' => $reference, 'paid_by' => isset($actor['id']) ? (int) $actor['id'] : null, 'now' => date('Y-m-d H:i:s'), 'notes' => $notes]
            );
            $this->db->execute('UPDATE payslips SET status = "paid", paid_at = :now WHERE id = :id', ['now' => date('Y-m-d H:i:s'), 'id' => $payslipId]);

            $stillDraft = (int) ($this->db->fetchOne('SELECT COUNT(*) c FROM payslips WHERE payroll_run_id = :rid AND status <> "paid"', ['rid' => $payslip['payroll_run_id']])['c'] ?? 1);
            if ($stillDraft === 0) {
                $this->db->execute('UPDATE payroll_runs SET status = "paid" WHERE id = :id', ['id' => $payslip['payroll_run_id']]);
            }

            $this->audit($actor, 'payroll', 'payroll.payslip.paid', 'payslips', $payslipId, ['status' => 'generated'], ['status' => 'paid', 'amount' => $payslip['net_pay']]);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);

            return ['success' => false, 'message' => 'Unable to record this payment right now.'];
        }

        return ['success' => true, 'message' => 'Payment recorded successfully.'];
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listPaymentHistory(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $where = ['1=1'];
        $params = [];

        if (($staffId = $this->intFilter($filters['staff_id'] ?? 0)) !== null) { $where[] = 'p.staff_id = :staff_id'; $params['staff_id'] = $staffId; }
        if (($method = trim((string) ($filters['payment_method'] ?? ''))) !== '' && in_array($method, ['bank_transfer', 'cash', 'cheque'], true)) { $where[] = 'pp.payment_method = :method'; $params['method'] = $method; }
        if (($dateFrom = trim((string) ($filters['date_from'] ?? ''))) !== '') { $where[] = 'DATE(pp.paid_at) >= :date_from'; $params['date_from'] = $dateFrom; }
        if (($dateTo = trim((string) ($filters['date_to'] ?? ''))) !== '') { $where[] = 'DATE(pp.paid_at) <= :date_to'; $params['date_to'] = $dateTo; }
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = "(st.first_name LIKE :search1 OR st.last_name LIKE :search2 OR st.staff_no LIKE :search3 OR pp.reference_no LIKE :search4)";
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like; $params['search4'] = $like;
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT pp.*, p.payroll_run_id, st.staff_no, st.first_name, st.last_name, st.staff_type, r.period_month, r.period_year
                 FROM payroll_payments pp
                 INNER JOIN payslips p ON p.id = pp.payslip_id
                 INNER JOIN staff st ON st.id = p.staff_id
                 INNER JOIN payroll_runs r ON r.id = p.payroll_run_id
                 WHERE {$whereSql}
                 ORDER BY pp.paid_at DESC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    // ------------------------------------------------------------------
    // Reports
    // ------------------------------------------------------------------

    /** @return array<string,mixed> */
    public function reportsSummary(?int $runId = null): array
    {
        $where = $runId ? 'WHERE p.payroll_run_id = :rid' : '';
        $params = $runId ? ['rid' => $runId] : [];

        $totals = $this->db->fetchOne(
            "SELECT COALESCE(SUM(basic_salary),0) basic, COALESCE(SUM(total_allowances),0) allowances,
                COALESCE(SUM(total_deductions),0) deductions, COALESCE(SUM(net_pay),0) net, COUNT(*) staff_count
             FROM payslips p {$where}",
            $params
        );

        $byDepartment = $this->db->fetchAll(
            "SELECT COALESCE(d.name, 'Unassigned') AS department, SUM(p.net_pay) total
             FROM payslips p INNER JOIN staff st ON st.id = p.staff_id LEFT JOIN departments d ON d.id = st.department_id
             {$where} GROUP BY department ORDER BY total DESC",
            $params
        );

        $monthly = $this->db->fetchAll(
            'SELECT r.period_month, r.period_year, SUM(p.net_pay) total
             FROM payslips p INNER JOIN payroll_runs r ON r.id = p.payroll_run_id
             GROUP BY r.period_year, r.period_month ORDER BY r.period_year DESC, r.period_month DESC LIMIT 12'
        );

        return [
            'total_basic' => (float) $totals['basic'],
            'total_allowances' => (float) $totals['allowances'],
            'total_deductions' => (float) $totals['deductions'],
            'total_net_pay' => (float) $totals['net'],
            'staff_count' => (int) $totals['staff_count'],
            'by_department' => $byDepartment,
            'monthly_trend' => array_reverse($monthly),
        ];
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function intFilter(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }
}
