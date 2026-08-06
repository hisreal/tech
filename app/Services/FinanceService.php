<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\FileUploader;
use App\Helpers\Paginator;
use App\Traits\Auditable;

/**
 * Backing service for the Finance module: fee structures, student invoices,
 * payment collection, receipts, expenses, and financial reporting.
 */
final class FinanceService
{
    use Auditable;

    private const FEE_STRUCTURE_STATUSES = ['draft', 'active', 'inactive', 'archived'];
    private const PAYMENT_METHODS = ['cash', 'bank_transfer', 'pos', 'online_payment', 'cheque'];
    private const EXPENSE_STATUSES = ['draft', 'submitted', 'approved', 'rejected', 'paid'];

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Select helpers
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function sessionsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM academic_sessions ORDER BY start_date DESC');
    }

    /** @return array<int,array<string,mixed>> */
    public function termsForSelect(?int $sessionId = null): array
    {
        if ($sessionId) {
            return $this->db->fetchAll('SELECT id, name, session_id FROM terms WHERE session_id = :sid ORDER BY start_date ASC', ['sid' => $sessionId]);
        }

        return $this->db->fetchAll('SELECT id, name, session_id FROM terms ORDER BY start_date DESC');
    }

    /** @return array<int,array<string,mixed>> */
    public function classesForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM classes WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function sectionsForSelect(?int $classId = null): array
    {
        if ($classId) {
            return $this->db->fetchAll('SELECT id, name, class_id FROM sections WHERE class_id = :cid AND status = "active" ORDER BY name ASC', ['cid' => $classId]);
        }

        return $this->db->fetchAll('SELECT id, name, class_id FROM sections WHERE status = "active" ORDER BY name ASC');
    }

    public function studentIdForUser(int $userId): ?int
    {
        $row = $this->db->fetchOne('SELECT id FROM students WHERE user_id = :uid', ['uid' => $userId]);

        return $row ? (int) $row['id'] : null;
    }

    /** @return array{total_bill:float,total_paid:float,balance:float} */
    public function studentBillingSummary(int $studentId): array
    {
        $row = $this->db->fetchOne(
            "SELECT COALESCE(SUM(total_amount),0) total_bill, COALESCE(SUM(amount_paid),0) total_paid, COALESCE(SUM(balance),0) balance
             FROM invoices WHERE student_id = :sid AND status <> 'cancelled'",
            ['sid' => $studentId]
        );

        return [
            'total_bill' => (float) ($row['total_bill'] ?? 0),
            'total_paid' => (float) ($row['total_paid'] ?? 0),
            'balance' => (float) ($row['balance'] ?? 0),
        ];
    }

    public function currentSessionId(): ?int
    {
        $row = $this->db->fetchOne("SELECT setting_value FROM school_settings WHERE setting_key = 'academic.current_session_id'");
        $id = $row ? (int) $row['setting_value'] : 0;
        if ($id > 0) {
            return $id;
        }
        $row = $this->db->fetchOne("SELECT id FROM academic_sessions WHERE status = 'active' ORDER BY id DESC LIMIT 1");

        return $row ? (int) $row['id'] : null;
    }

    public function currentTermId(): ?int
    {
        $row = $this->db->fetchOne("SELECT setting_value FROM school_settings WHERE setting_key = 'academic.current_term_id'");
        $id = $row ? (int) $row['setting_value'] : 0;
        if ($id > 0) {
            return $id;
        }
        $row = $this->db->fetchOne("SELECT id FROM terms WHERE status = 'active' ORDER BY id DESC LIMIT 1");

        return $row ? (int) $row['id'] : null;
    }

    // ------------------------------------------------------------------
    // Fee Structures
    // ------------------------------------------------------------------

    /**
     * Each row represents one fee category (structure name) assigned to a
     * session/term/class/section, with exactly one fee_structure_item - this
     * mirrors the UI's one-row-per-category model while staying within the
     * real relational schema (fee_structures -> fee_structure_items -> fee_items).
     *
     * @return array<int,array<string,mixed>>
     */
    public function listFeeStructures(): array
    {
        return $this->db->fetchAll(
            "SELECT fs.id, s.name AS session_name, t.name AS term_name, c.name AS class_name, sec.name AS section_name,
                fs.name AS category, fs.status, fs.session_id, fs.term_id, fs.class_id, fs.section_id,
                fsi.id AS item_row_id, fsi.amount, fi.id AS fee_item_id
             FROM fee_structures fs
             INNER JOIN academic_sessions s ON s.id = fs.session_id
             INNER JOIN terms t ON t.id = fs.term_id
             INNER JOIN classes c ON c.id = fs.class_id
             LEFT JOIN sections sec ON sec.id = fs.section_id
             LEFT JOIN fee_structure_items fsi ON fsi.fee_structure_id = fs.id
             LEFT JOIN fee_items fi ON fi.id = fsi.fee_item_id
             ORDER BY fs.created_at DESC"
        );
    }

    /** @return array<string,mixed>|null */
    public function findFeeStructure(int $id): ?array
    {
        $structure = $this->db->fetchOne(
            "SELECT fs.*, s.name AS session_name, t.name AS term_name, c.name AS class_name, sec.name AS section_name
             FROM fee_structures fs
             INNER JOIN academic_sessions s ON s.id = fs.session_id
             INNER JOIN terms t ON t.id = fs.term_id
             INNER JOIN classes c ON c.id = fs.class_id
             LEFT JOIN sections sec ON sec.id = fs.section_id
             WHERE fs.id = :id",
            ['id' => $id]
        );

        if ($structure === null) {
            return null;
        }

        $item = $this->db->fetchOne(
            'SELECT fsi.*, fi.name AS item_name FROM fee_structure_items fsi INNER JOIN fee_items fi ON fi.id = fsi.fee_item_id WHERE fsi.fee_structure_id = :id LIMIT 1',
            ['id' => $id]
        );
        $structure['amount'] = $item['amount'] ?? 0;
        $structure['item_id'] = $item['id'] ?? null;

        return $structure;
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function saveFeeStructure(array $data, ?int $id, ?array $actor): array
    {
        $sessionId = (int) ($data['session_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        $classId = (int) ($data['class_id'] ?? 0);
        $sectionId = (int) ($data['section_id'] ?? 0) ?: null;
        $category = trim((string) ($data['category'] ?? ''));
        $amount = (float) ($data['amount'] ?? 0);
        $status = strtolower(trim((string) ($data['status'] ?? 'active')));
        $dueDate = trim((string) ($data['due_date'] ?? '')) ?: null;

        $errors = [];
        if ($sessionId < 1) { $errors['session_id'] = 'Choose an academic session.'; }
        if ($termId < 1) { $errors['term_id'] = 'Choose a term.'; }
        if ($classId < 1) { $errors['class_id'] = 'Choose a class.'; }
        if ($category === '') { $errors['category'] = 'Choose or enter a fee category.'; }
        if ($amount <= 0) { $errors['amount'] = 'Fee amount must be a positive number.'; }
        if (!in_array($status, self::FEE_STRUCTURE_STATUSES, true)) { $status = 'active'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $feeItemId = $this->findOrCreateFeeItem($category);

        $this->db->beginTransaction();
        try {
            if ($id) {
                $before = $this->db->fetchOne('SELECT * FROM fee_structures WHERE id = :id', ['id' => $id]);
                if ($before === null) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => 'Fee structure not found.'];
                }
                $this->db->execute(
                    'UPDATE fee_structures SET session_id=:session_id, term_id=:term_id, class_id=:class_id, section_id=:section_id, name=:name, status=:status WHERE id=:id',
                    ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'section_id' => $sectionId, 'name' => $category, 'status' => $status, 'id' => $id]
                );
                $existingItem = $this->db->fetchOne('SELECT id FROM fee_structure_items WHERE fee_structure_id = :id LIMIT 1', ['id' => $id]);
                if ($existingItem) {
                    $this->db->execute('UPDATE fee_structure_items SET fee_item_id = :fee_item_id, amount = :amount WHERE id = :id', ['fee_item_id' => $feeItemId, 'amount' => $amount, 'id' => $existingItem['id']]);
                } else {
                    $this->db->execute('INSERT INTO fee_structure_items (fee_structure_id, fee_item_id, amount) VALUES (:fee_structure_id, :fee_item_id, :amount)', ['fee_structure_id' => $id, 'fee_item_id' => $feeItemId, 'amount' => $amount]);
                }
                $this->audit($actor, 'finance', 'finance.fee_structure.updated', 'fee_structures', $id, $before, ['name' => $category, 'amount' => $amount, 'status' => $status]);
                $newId = $id;
            } else {
                $this->db->execute(
                    'INSERT INTO fee_structures (session_id, term_id, class_id, section_id, name, status, created_by) VALUES (:session_id, :term_id, :class_id, :section_id, :name, :status, :created_by)',
                    ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'section_id' => $sectionId, 'name' => $category, 'status' => $status, 'created_by' => isset($actor['id']) ? (int) $actor['id'] : null]
                );
                $newId = (int) $this->db->lastInsertId();
                $this->db->execute('INSERT INTO fee_structure_items (fee_structure_id, fee_item_id, amount) VALUES (:fee_structure_id, :fee_item_id, :amount)', ['fee_structure_id' => $newId, 'fee_item_id' => $feeItemId, 'amount' => $amount]);
                $this->audit($actor, 'finance', 'finance.fee_structure.created', 'fee_structures', $newId, null, ['name' => $category, 'amount' => $amount, 'status' => $status]);
            }
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            if ($throwable->getCode() === '23000') {
                return ['success' => false, 'message' => 'A fee structure with this session/term/class/section/category already exists.'];
            }
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to save the fee structure right now.'];
        }

        return ['success' => true, 'message' => $id ? 'Fee structure updated successfully.' : 'Fee structure saved successfully.', 'id' => $newId];
    }

    public function duplicateFeeStructure(int $id, ?int $toSessionId, ?int $toTermId, ?array $actor): array
    {
        $source = $this->findFeeStructure($id);
        if ($source === null) {
            return ['success' => false, 'message' => 'Fee structure not found.'];
        }

        return $this->saveFeeStructure([
            'session_id' => $toSessionId ?: $source['session_id'],
            'term_id' => $toTermId ?: $source['term_id'],
            'class_id' => $source['class_id'],
            'section_id' => $source['section_id'],
            'category' => $source['name'],
            'amount' => $source['amount'],
            'status' => $source['status'],
        ], null, $actor);
    }

    public function setFeeStructureStatus(int $id, string $status, ?array $actor): array
    {
        if (!in_array($status, self::FEE_STRUCTURE_STATUSES, true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }
        $before = $this->db->fetchOne('SELECT * FROM fee_structures WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Fee structure not found.'];
        }
        $this->db->execute('UPDATE fee_structures SET status = :status WHERE id = :id', ['status' => $status, 'id' => $id]);
        $this->audit($actor, 'finance', 'finance.fee_structure.status_changed', 'fee_structures', $id, ['status' => $before['status']], ['status' => $status]);

        return ['success' => true, 'message' => 'Fee structure status updated.'];
    }

    public function deleteFeeStructure(int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM fee_structures WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Fee structure not found.'];
        }
        $this->db->execute('DELETE FROM fee_structures WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'finance', 'finance.fee_structure.deleted', 'fee_structures', $id, $before, null);

        return ['success' => true, 'message' => 'Fee structure deleted successfully.'];
    }

    private function findOrCreateFeeItem(string $name): int
    {
        $row = $this->db->fetchOne('SELECT id FROM fee_items WHERE name = :name', ['name' => $name]);
        if ($row) {
            return (int) $row['id'];
        }
        $this->db->execute('INSERT INTO fee_items (name, status) VALUES (:name, "active")', ['name' => $name]);

        return (int) $this->db->lastInsertId();
    }

    // ------------------------------------------------------------------
    // Student lookup + invoice generation
    // ------------------------------------------------------------------

    /** @return array<string,mixed>|null */
    public function findStudentForFees(string $query, ?int $sessionId, ?int $termId): ?array
    {
        $sessionId = $sessionId ?: $this->currentSessionId();
        $termId = $termId ?: $this->currentTermId();
        $query = trim($query);

        if ($query === '') {
            return null;
        }

        $student = $this->db->fetchOne(
            "SELECT s.* FROM students s WHERE (s.registration_no = :q1 OR s.admission_no = :q2 OR CONCAT(s.first_name,' ',s.last_name) LIKE :q3) AND s.status <> 'deleted' LIMIT 1",
            ['q1' => $query, 'q2' => $query, 'q3' => '%' . $query . '%']
        );

        if ($student === null || !$sessionId || !$termId) {
            return null;
        }

        return $this->studentFeeSummary((int) $student['id'], $sessionId, $termId);
    }

    /** @return array<string,mixed>|null */
    public function studentFeeSummary(int $studentId, int $sessionId, int $termId): ?array
    {
        $student = $this->db->fetchOne('SELECT * FROM students WHERE id = :id', ['id' => $studentId]);
        if ($student === null) {
            return null;
        }

        $enrollment = $this->db->fetchOne(
            'SELECT se.*, c.name AS class_name, sec.name AS section_name FROM student_enrollments se
             INNER JOIN classes c ON c.id = se.class_id LEFT JOIN sections sec ON sec.id = se.section_id
             WHERE se.student_id = :sid AND se.session_id = :session_id ORDER BY se.id DESC LIMIT 1',
            ['sid' => $studentId, 'session_id' => $sessionId]
        );

        $invoice = $this->ensureInvoice($studentId, $sessionId, $termId, $enrollment);

        $items = $invoice ? $this->db->fetchAll('SELECT * FROM invoice_items WHERE invoice_id = :id ORDER BY id ASC', ['id' => $invoice['id']]) : [];
        $payments = $this->db->fetchAll(
            "SELECT p.*, r.receipt_no FROM payments p LEFT JOIN receipts r ON r.payment_id = p.id
             WHERE p.student_id = :sid AND p.status = 'paid' ORDER BY p.payment_date DESC LIMIT 50",
            ['sid' => $studentId]
        );

        $sessionRow = $this->db->fetchOne('SELECT name FROM academic_sessions WHERE id = :id', ['id' => $sessionId]);
        $termRow = $this->db->fetchOne('SELECT name FROM terms WHERE id = :id', ['id' => $termId]);

        return [
            'student' => $student,
            'enrollment' => $enrollment,
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments,
            'session_name' => $sessionRow['name'] ?? '',
            'term_name' => $termRow['name'] ?? '',
        ];
    }

    /** @param array<string,mixed>|null $enrollment @return array<string,mixed>|null */
    private function ensureInvoice(int $studentId, int $sessionId, int $termId, ?array $enrollment): ?array
    {
        $existing = $this->db->fetchOne(
            'SELECT * FROM invoices WHERE student_id = :sid AND session_id = :session_id AND term_id = :term_id LIMIT 1',
            ['sid' => $studentId, 'session_id' => $sessionId, 'term_id' => $termId]
        );
        if ($existing) {
            return $existing;
        }

        if ($enrollment === null) {
            return null;
        }

        $classId = (int) $enrollment['class_id'];
        $sectionId = $enrollment['section_id'] ? (int) $enrollment['section_id'] : null;

        $structureItems = $this->db->fetchAll(
            "SELECT fsi.id AS item_row_id, fsi.amount, fi.name AS item_name, fi.id AS fee_item_id
             FROM fee_structures fs
             INNER JOIN fee_structure_items fsi ON fsi.fee_structure_id = fs.id
             INNER JOIN fee_items fi ON fi.id = fsi.fee_item_id
             WHERE fs.session_id = :session_id AND fs.term_id = :term_id AND fs.class_id = :class_id
                AND fs.status = 'active' AND (fs.section_id <=> :section_id OR fs.section_id IS NULL)",
            ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'section_id' => $sectionId]
        );

        if ($structureItems === []) {
            return null;
        }

        $total = array_sum(array_column($structureItems, 'amount'));
        $term = $this->db->fetchOne('SELECT end_date FROM terms WHERE id = :id', ['id' => $termId]);
        $dueDate = $term['end_date'] ?? null;

        $this->db->beginTransaction();
        try {
            $this->db->execute(
                'INSERT INTO invoices (invoice_no, student_id, session_id, term_id, class_id, section_id, total_amount, amount_paid, balance, due_date, status, created_by)
                 VALUES (:invoice_no, :student_id, :session_id, :term_id, :class_id, :section_id, :total1, 0, :total2, :due_date, "unpaid", :created_by)',
                [
                    'invoice_no' => $this->generateNumber('invoices', 'invoice_no', 'INV'),
                    'student_id' => $studentId, 'session_id' => $sessionId, 'term_id' => $termId,
                    'class_id' => $classId, 'section_id' => $sectionId, 'total1' => $total, 'total2' => $total,
                    'due_date' => $dueDate, 'created_by' => null,
                ]
            );
            $invoiceId = (int) $this->db->lastInsertId();
            foreach ($structureItems as $item) {
                $this->db->execute(
                    'INSERT INTO invoice_items (invoice_id, fee_item_id, item_name, amount, status) VALUES (:invoice_id, :fee_item_id, :item_name, :amount, "unpaid")',
                    ['invoice_id' => $invoiceId, 'fee_item_id' => $item['fee_item_id'], 'item_name' => $item['item_name'], 'amount' => $item['amount']]
                );
            }
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);

            return null;
        }

        return $this->db->fetchOne('SELECT * FROM invoices WHERE id = :id', ['id' => $invoiceId]);
    }

    // ------------------------------------------------------------------
    // Payments
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function collectPayment(int $studentId, int $invoiceId, array $data, ?array $actor): array
    {
        $invoice = $this->db->fetchOne('SELECT * FROM invoices WHERE id = :id AND student_id = :sid', ['id' => $invoiceId, 'sid' => $studentId]);
        if ($invoice === null) {
            return ['success' => false, 'message' => 'Invoice not found for this student.'];
        }

        $amount = (float) ($data['amount'] ?? 0);
        $method = strtolower(str_replace(' ', '_', trim((string) ($data['method'] ?? ''))));
        $paymentType = trim((string) ($data['payment_type'] ?? 'School Fees'));
        $paymentDate = trim((string) ($data['payment_date'] ?? date('Y-m-d')));
        $reference = trim((string) ($data['reference'] ?? ''));
        $notes = trim((string) ($data['notes'] ?? ''));
        $balance = (float) $invoice['balance'];

        $errors = [];
        if ($amount <= 0) { $errors['amount'] = 'Payment amount must be a positive number.'; }
        elseif ($amount > $balance) { $errors['amount'] = 'Payment amount cannot exceed the outstanding balance of ' . number_format($balance, 2) . '.'; }
        if (!in_array($method, self::PAYMENT_METHODS, true)) { $errors['method'] = 'Choose a valid payment method.'; }
        if ($method !== 'cash' && $reference === '') { $errors['reference'] = 'Transaction reference is required for non-cash payments.'; }
        if ($paymentDate === '' || strtotime($paymentDate) === false) { $errors['payment_date'] = 'Choose a valid payment date.'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $this->db->beginTransaction();
        try {
            $this->db->execute(
                'INSERT INTO payments (transaction_no, invoice_id, student_id, payment_type, amount, payment_method, transaction_reference, payment_date, status, received_by, notes)
                 VALUES (:transaction_no, :invoice_id, :student_id, :payment_type, :amount, :method, :reference, :payment_date, "paid", :received_by, :notes)',
                [
                    'transaction_no' => $this->generateNumber('payments', 'transaction_no', 'TXN'),
                    'invoice_id' => $invoiceId, 'student_id' => $studentId, 'payment_type' => $paymentType,
                    'amount' => $amount, 'method' => $method, 'reference' => $reference !== '' ? $reference : null,
                    'payment_date' => $paymentDate . ' ' . date('H:i:s'), 'received_by' => isset($actor['id']) ? (int) $actor['id'] : null,
                    'notes' => $notes !== '' ? $notes : null,
                ]
            );
            $paymentId = (int) $this->db->lastInsertId();

            $newPaid = (float) $invoice['amount_paid'] + $amount;
            $newBalance = max(0, (float) $invoice['total_amount'] - $newPaid);
            $newStatus = $newBalance <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid');
            $this->db->execute('UPDATE invoices SET amount_paid = :paid, balance = :balance, status = :status WHERE id = :id', ['paid' => $newPaid, 'balance' => $newBalance, 'status' => $newStatus, 'id' => $invoiceId]);

            if ($newStatus === 'paid') {
                $this->db->execute('UPDATE invoice_items SET status = "paid" WHERE invoice_id = :id', ['id' => $invoiceId]);
            }

            $this->db->execute(
                'INSERT INTO receipts (receipt_no, payment_id, issued_by, issued_at, status, footer_message) VALUES (:receipt_no, :payment_id, :issued_by, :issued_at, "paid", :footer)',
                [
                    'receipt_no' => $this->generateNumber('receipts', 'receipt_no', 'RCP'),
                    'payment_id' => $paymentId, 'issued_by' => isset($actor['id']) ? (int) $actor['id'] : null,
                    'issued_at' => date('Y-m-d H:i:s'), 'footer' => 'Thank you for your payment.',
                ]
            );
            $receiptId = (int) $this->db->lastInsertId();

            $this->audit($actor, 'finance', 'finance.payment.collected', 'payments', $paymentId, null, ['invoice_id' => $invoiceId, 'amount' => $amount, 'method' => $method]);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);

            return ['success' => false, 'message' => 'Unable to collect this payment right now.'];
        }

        $receipt = $this->findReceipt($receiptId);

        return ['success' => true, 'message' => 'Payment collected successfully. Receipt ' . $receipt['receipt_no'] . ' generated.', 'receipt' => $receipt];
    }

    /** @param array<string,mixed> $filters @return array<int,array<string,mixed>> */
    public function paymentHistory(array $filters = [], int $limit = 500): array
    {
        $where = ["p.status <> 'cancelled'"];
        $params = [];

        if (($sessionId = $this->intFilter($filters['session_id'] ?? 0)) !== null) { $where[] = 'i.session_id = :session_id'; $params['session_id'] = $sessionId; }
        if (($termId = $this->intFilter($filters['term_id'] ?? 0)) !== null) { $where[] = 'i.term_id = :term_id'; $params['term_id'] = $termId; }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) { $where[] = 'i.class_id = :class_id'; $params['class_id'] = $classId; }
        if (($studentId = $this->intFilter($filters['student_id'] ?? 0)) !== null) { $where[] = 'p.student_id = :student_id'; $params['student_id'] = $studentId; }
        if (($dateFrom = trim((string) ($filters['date_from'] ?? ''))) !== '') { $where[] = 'DATE(p.payment_date) >= :date_from'; $params['date_from'] = $dateFrom; }
        if (($dateTo = trim((string) ($filters['date_to'] ?? ''))) !== '') { $where[] = 'DATE(p.payment_date) <= :date_to'; $params['date_to'] = $dateTo; }

        $whereSql = implode(' AND ', $where);

        return $this->db->fetchAll(
            "SELECT p.*, s.registration_no, s.first_name, s.last_name, c.name AS class_name,
                sess.name AS session_name, t.name AS term_name, r.receipt_no, i.balance
             FROM payments p
             INNER JOIN students s ON s.id = p.student_id
             LEFT JOIN invoices i ON i.id = p.invoice_id
             LEFT JOIN classes c ON c.id = i.class_id
             LEFT JOIN academic_sessions sess ON sess.id = i.session_id
             LEFT JOIN terms t ON t.id = i.term_id
             LEFT JOIN receipts r ON r.payment_id = p.id
             WHERE {$whereSql}
             ORDER BY p.payment_date DESC
             LIMIT " . max(1, $limit),
            $params
        );
    }

    // ------------------------------------------------------------------
    // Outstanding fees
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $filters @return array<int,array<string,mixed>> */
    public function listOutstanding(array $filters = [], int $limit = 1000): array
    {
        $where = ["i.balance > 0", "i.status <> 'cancelled'"];
        $params = [];

        if (($sessionId = $this->intFilter($filters['session_id'] ?? 0)) !== null) { $where[] = 'i.session_id = :session_id'; $params['session_id'] = $sessionId; }
        if (($termId = $this->intFilter($filters['term_id'] ?? 0)) !== null) { $where[] = 'i.term_id = :term_id'; $params['term_id'] = $termId; }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) { $where[] = 'i.class_id = :class_id'; $params['class_id'] = $classId; }

        $whereSql = implode(' AND ', $where);

        return $this->db->fetchAll(
            "SELECT i.*, s.registration_no, s.first_name, s.last_name, c.name AS class_name,
                sess.name AS session_name, t.name AS term_name
             FROM invoices i
             INNER JOIN students s ON s.id = i.student_id
             INNER JOIN classes c ON c.id = i.class_id
             INNER JOIN academic_sessions sess ON sess.id = i.session_id
             INNER JOIN terms t ON t.id = i.term_id
             WHERE {$whereSql}
             ORDER BY i.balance DESC
             LIMIT " . max(1, $limit),
            $params
        );
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listAllInvoices(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where = ["i.status <> 'cancelled'"];
        $params = [];

        if (($sessionId = $this->intFilter($filters['session_id'] ?? 0)) !== null) { $where[] = 'i.session_id = :session_id'; $params['session_id'] = $sessionId; }
        if (($termId = $this->intFilter($filters['term_id'] ?? 0)) !== null) { $where[] = 'i.term_id = :term_id'; $params['term_id'] = $termId; }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) { $where[] = 'i.class_id = :class_id'; $params['class_id'] = $classId; }
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = '(s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.registration_no LIKE :search3)';
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like;
        }
        $status = trim((string) ($filters['status'] ?? ''));
        if ($status === 'paid') { $where[] = 'i.balance <= 0'; }
        if ($status === 'partial') { $where[] = 'i.balance > 0 AND i.amount_paid > 0'; }
        if ($status === 'outstanding') { $where[] = 'i.balance > 0 AND i.amount_paid <= 0'; }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT i.*, s.registration_no, s.first_name, s.last_name, s.passport_path, c.name AS class_name, sec.name AS section_name,
                    sess.name AS session_name, t.name AS term_name
                 FROM invoices i
                 INNER JOIN students s ON s.id = i.student_id
                 INNER JOIN classes c ON c.id = i.class_id
                 LEFT JOIN sections sec ON sec.id = i.section_id
                 INNER JOIN academic_sessions sess ON sess.id = i.session_id
                 INNER JOIN terms t ON t.id = i.term_id
                 WHERE {$whereSql}
                 ORDER BY i.created_at DESC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    /** @return array<int,array<string,mixed>> */
    public function invoiceItems(int $invoiceId): array
    {
        return $this->db->fetchAll('SELECT * FROM invoice_items WHERE invoice_id = :id ORDER BY id ASC', ['id' => $invoiceId]);
    }

    /** @return array<string,float> */
    public function outstandingByClass(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT c.name, SUM(i.balance) total FROM invoices i INNER JOIN classes c ON c.id = i.class_id
             WHERE i.balance > 0 AND i.status <> 'cancelled' GROUP BY c.name ORDER BY c.name"
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['name']] = (float) $row['total'];
        }

        return $result;
    }

    // ------------------------------------------------------------------
    // Receipts
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $filters @return array<int,array<string,mixed>> */
    public function listReceipts(array $filters = [], int $limit = 500): array
    {
        $where = ['1=1'];
        $params = [];

        if (($dateFrom = trim((string) ($filters['date_from'] ?? ''))) !== '') { $where[] = 'DATE(r.issued_at) >= :date_from'; $params['date_from'] = $dateFrom; }
        if (($dateTo = trim((string) ($filters['date_to'] ?? ''))) !== '') { $where[] = 'DATE(r.issued_at) <= :date_to'; $params['date_to'] = $dateTo; }

        $whereSql = implode(' AND ', $where);

        return $this->db->fetchAll(
            "SELECT r.*, p.transaction_no, p.amount, p.payment_method, p.payment_type, p.student_id, p.transaction_reference,
                s.registration_no, s.first_name, s.last_name, c.name AS class_name
             FROM receipts r
             INNER JOIN payments p ON p.id = r.payment_id
             INNER JOIN students s ON s.id = p.student_id
             LEFT JOIN invoices i ON i.id = p.invoice_id
             LEFT JOIN classes c ON c.id = i.class_id
             WHERE {$whereSql}
             ORDER BY r.issued_at DESC
             LIMIT " . max(1, $limit),
            $params
        );
    }

    /** @return array<string,mixed>|null */
    public function findReceiptByPaymentId(int $paymentId): ?array
    {
        $row = $this->db->fetchOne('SELECT id FROM receipts WHERE payment_id = :id', ['id' => $paymentId]);

        return $row ? $this->findReceipt((int) $row['id']) : null;
    }

    public function findReceipt(int $id): ?array
    {
        $receipt = $this->db->fetchOne(
            "SELECT r.*, p.transaction_no, p.amount, p.payment_method, p.payment_type, p.student_id, p.transaction_reference, p.invoice_id,
                s.registration_no, s.first_name, s.last_name, c.name AS class_name
             FROM receipts r
             INNER JOIN payments p ON p.id = r.payment_id
             INNER JOIN students s ON s.id = p.student_id
             LEFT JOIN invoices i ON i.id = p.invoice_id
             LEFT JOIN classes c ON c.id = i.class_id
             WHERE r.id = :id",
            ['id' => $id]
        );
        if ($receipt === null) {
            return null;
        }
        $receipt['items'] = $receipt['invoice_id'] ? $this->db->fetchAll('SELECT item_name, amount FROM invoice_items WHERE invoice_id = :id', ['id' => $receipt['invoice_id']]) : [];

        return $receipt;
    }

    public function voidReceipt(int $id, ?array $actor): array
    {
        $receipt = $this->db->fetchOne('SELECT * FROM receipts WHERE id = :id', ['id' => $id]);
        if ($receipt === null) {
            return ['success' => false, 'message' => 'Receipt not found.'];
        }
        if ($receipt['status'] !== 'paid') {
            return ['success' => false, 'message' => 'This receipt has already been ' . $receipt['status'] . '.'];
        }

        $payment = $this->db->fetchOne('SELECT * FROM payments WHERE id = :id', ['id' => $receipt['payment_id']]);

        $this->db->beginTransaction();
        try {
            $this->db->execute('UPDATE receipts SET status = "cancelled" WHERE id = :id', ['id' => $id]);
            $this->db->execute('UPDATE payments SET status = "cancelled" WHERE id = :id', ['id' => $receipt['payment_id']]);

            if ($payment && $payment['invoice_id']) {
                $invoice = $this->db->fetchOne('SELECT * FROM invoices WHERE id = :id', ['id' => $payment['invoice_id']]);
                if ($invoice) {
                    $newPaid = max(0, (float) $invoice['amount_paid'] - (float) $payment['amount']);
                    $newBalance = (float) $invoice['total_amount'] - $newPaid;
                    $newStatus = $newBalance <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid');
                    $this->db->execute('UPDATE invoices SET amount_paid = :paid, balance = :balance, status = :status WHERE id = :id', ['paid' => $newPaid, 'balance' => $newBalance, 'status' => $newStatus, 'id' => $invoice['id']]);
                }
            }

            $this->audit($actor, 'finance', 'finance.receipt.voided', 'receipts', $id, $receipt, ['status' => 'cancelled']);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);

            return ['success' => false, 'message' => 'Unable to void this receipt right now.'];
        }

        return ['success' => true, 'message' => 'Receipt voided and payment reversed successfully.'];
    }

    // ------------------------------------------------------------------
    // Expenses
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $filters @return array<int,array<string,mixed>> */
    public function listExpenses(array $filters = [], int $limit = 500): array
    {
        $where = ['1=1'];
        $params = [];

        if (($dateFrom = trim((string) ($filters['date_from'] ?? ''))) !== '') { $where[] = 'e.expense_date >= :date_from'; $params['date_from'] = $dateFrom; }
        if (($dateTo = trim((string) ($filters['date_to'] ?? ''))) !== '') { $where[] = 'e.expense_date <= :date_to'; $params['date_to'] = $dateTo; }
        if (($category = trim((string) ($filters['category'] ?? ''))) !== '') { $where[] = 'e.category = :category'; $params['category'] = $category; }
        if (($status = trim((string) ($filters['status'] ?? ''))) !== '') { $where[] = 'e.status = :status'; $params['status'] = $status; }

        $whereSql = implode(' AND ', $where);

        return $this->db->fetchAll(
            "SELECT e.*, CONCAT(st.first_name,' ',st.last_name) AS recorded_by_name
             FROM expenses e
             LEFT JOIN users u ON u.id = e.recorded_by
             LEFT JOIN staff st ON st.user_id = u.id
             WHERE {$whereSql}
             ORDER BY e.expense_date DESC, e.id DESC
             LIMIT " . max(1, $limit),
            $params
        );
    }

    /** @return array<string,mixed>|null */
    public function findExpense(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM expenses WHERE id = :id', ['id' => $id]);
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $file @param array<string,mixed>|null $actor */
    public function saveExpense(array $data, ?array $file, ?int $id, ?array $actor): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $category = trim((string) ($data['category'] ?? ''));
        $amount = (float) ($data['amount'] ?? 0);
        $method = strtolower(str_replace(' ', '_', trim((string) ($data['method'] ?? 'cash'))));
        $date = trim((string) ($data['date'] ?? date('Y-m-d')));
        $status = strtolower(trim((string) ($data['status'] ?? 'draft')));
        $vendor = trim((string) ($data['vendor'] ?? ''));
        $invoiceNo = trim((string) ($data['invoice_no'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        $errors = [];
        if ($title === '') { $errors['title'] = 'Expense title is required.'; }
        if ($category === '') { $errors['category'] = 'Choose or enter an expense category.'; }
        if ($amount <= 0) { $errors['amount'] = 'Expense amount must be a positive number.'; }
        if (!in_array($method, self::PAYMENT_METHODS, true)) { $errors['method'] = 'Choose a valid payment method.'; }
        if ($date === '' || strtotime($date) === false) { $errors['date'] = 'Choose a valid expense date.'; }
        if (!in_array($status, self::EXPENSE_STATUSES, true)) { $status = 'draft'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $attachmentPath = null;
        if ($file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $attachmentPath = FileUploader::upload($file, 'finance/expenses', ['pdf', 'jpg', 'jpeg', 'png'], 5 * 1024 * 1024)['path'];
            } catch (\Throwable $throwable) {
                Logger::exception($throwable);

                return ['success' => false, 'message' => 'Unable to upload the attachment right now.'];
            }
        }

        $description = $description !== '' ? $description : $title;

        $payload = [
            'category' => $category, 'description' => $description, 'amount' => $amount,
            'expense_date' => $date, 'payment_method' => $method, 'status' => $status,
        ];

        if ($id) {
            $before = $this->db->fetchOne('SELECT * FROM expenses WHERE id = :id', ['id' => $id]);
            if ($before === null) {
                return ['success' => false, 'message' => 'Expense not found.'];
            }
            if ($attachmentPath !== null && !empty($before['attachment_path'])) {
                FileUploader::delete((string) $before['attachment_path']);
            }
            $updatePayload = $payload;
            if ($attachmentPath !== null) {
                $updatePayload['attachment_path'] = $attachmentPath;
            }
            $sets = implode(', ', array_map(static fn (string $key): string => "{$key} = :{$key}", array_keys($updatePayload)));
            $this->db->execute("UPDATE expenses SET {$sets} WHERE id = :id", array_merge($updatePayload, ['id' => $id]));
            $this->audit($actor, 'finance', 'finance.expense.updated', 'expenses', $id, $before, $updatePayload);

            return ['success' => true, 'message' => 'Expense updated successfully.', 'id' => $id];
        }

        $payload['expense_no'] = $this->generateNumber('expenses', 'expense_no', 'EXP');
        $payload['recorded_by'] = isset($actor['id']) ? (int) $actor['id'] : null;
        $payload['attachment_path'] = $attachmentPath;

        $columns = implode(', ', array_keys($payload));
        $placeholders = implode(', ', array_map(static fn (string $key): string => ":{$key}", array_keys($payload)));
        $this->db->execute("INSERT INTO expenses ({$columns}) VALUES ({$placeholders})", $payload);
        $newId = (int) $this->db->lastInsertId();
        $this->audit($actor, 'finance', 'finance.expense.created', 'expenses', $newId, null, $payload);

        return ['success' => true, 'message' => 'Expense saved successfully.', 'id' => $newId];
    }

    public function deleteExpense(int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM expenses WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Expense not found.'];
        }
        if (!empty($before['attachment_path'])) {
            FileUploader::delete((string) $before['attachment_path']);
        }
        $this->db->execute('DELETE FROM expenses WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'finance', 'finance.expense.deleted', 'expenses', $id, $before, null);

        return ['success' => true, 'message' => 'Expense deleted successfully.'];
    }

    // ------------------------------------------------------------------
    // Financial reports
    // ------------------------------------------------------------------

    /** @return array<string,mixed> */
    public function summary(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');

        $revenue = (float) ($this->db->fetchOne(
            "SELECT COALESCE(SUM(amount),0) c FROM payments WHERE status = 'paid' AND DATE(payment_date) BETWEEN :from AND :to",
            ['from' => $dateFrom, 'to' => $dateTo]
        )['c'] ?? 0);

        $expenses = (float) ($this->db->fetchOne(
            "SELECT COALESCE(SUM(amount),0) c FROM expenses WHERE status IN ('approved','paid') AND expense_date BETWEEN :from AND :to",
            ['from' => $dateFrom, 'to' => $dateTo]
        )['c'] ?? 0);

        $outstanding = (float) ($this->db->fetchOne("SELECT COALESCE(SUM(balance),0) c FROM invoices WHERE balance > 0 AND status <> 'cancelled'")['c'] ?? 0);

        $studentsPaid = (int) ($this->db->fetchOne("SELECT COUNT(DISTINCT student_id) c FROM invoices WHERE status = 'paid'")['c'] ?? 0);
        $studentsOutstanding = (int) ($this->db->fetchOne("SELECT COUNT(DISTINCT student_id) c FROM invoices WHERE balance > 0 AND status <> 'cancelled'")['c'] ?? 0);

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net_income' => $revenue - $expenses,
            'outstanding' => $outstanding,
            'students_paid' => $studentsPaid,
            'students_outstanding' => $studentsOutstanding,
        ];
    }

    /** @return array<string,float> */
    public function incomeByCategory(string $dateFrom, string $dateTo): array
    {
        $rows = $this->db->fetchAll(
            "SELECT payment_type, SUM(amount) total FROM payments WHERE status = 'paid' AND DATE(payment_date) BETWEEN :from AND :to GROUP BY payment_type ORDER BY total DESC",
            ['from' => $dateFrom, 'to' => $dateTo]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['payment_type']] = (float) $row['total'];
        }

        return $result;
    }

    /** @return array<string,float> */
    public function expenseByCategory(string $dateFrom, string $dateTo): array
    {
        $rows = $this->db->fetchAll(
            "SELECT category, SUM(amount) total FROM expenses WHERE status IN ('approved','paid') AND expense_date BETWEEN :from AND :to GROUP BY category ORDER BY total DESC",
            ['from' => $dateFrom, 'to' => $dateTo]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['category']] = (float) $row['total'];
        }

        return $result;
    }

    /** @return array<string,float> */
    public function paymentMethodBreakdown(string $dateFrom, string $dateTo): array
    {
        $rows = $this->db->fetchAll(
            "SELECT payment_method, SUM(amount) total FROM payments WHERE status = 'paid' AND DATE(payment_date) BETWEEN :from AND :to GROUP BY payment_method",
            ['from' => $dateFrom, 'to' => $dateTo]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['payment_method']] = (float) $row['total'];
        }

        return $result;
    }

    /** @return array{revenue:array<int,float>,expenses:array<int,float>} */
    public function monthlyTrend(int $year): array
    {
        $revenueRows = $this->db->fetchAll(
            "SELECT MONTH(payment_date) m, SUM(amount) total FROM payments WHERE status = 'paid' AND YEAR(payment_date) = :year GROUP BY MONTH(payment_date)",
            ['year' => $year]
        );
        $expenseRows = $this->db->fetchAll(
            "SELECT MONTH(expense_date) m, SUM(amount) total FROM expenses WHERE status IN ('approved','paid') AND YEAR(expense_date) = :year GROUP BY MONTH(expense_date)",
            ['year' => $year]
        );

        $revenue = array_fill(1, 12, 0.0);
        foreach ($revenueRows as $row) { $revenue[(int) $row['m']] = (float) $row['total']; }
        $expenses = array_fill(1, 12, 0.0);
        foreach ($expenseRows as $row) { $expenses[(int) $row['m']] = (float) $row['total']; }

        return ['revenue' => array_values($revenue), 'expenses' => array_values($expenses)];
    }

    /** @return array<int,array<string,mixed>> */
    public function transactionsFeed(?string $dateFrom = null, ?string $dateTo = null, int $limit = 200): array
    {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');

        $payments = $this->db->fetchAll(
            "SELECT p.transaction_no AS id, DATE(p.payment_date) AS date, CONCAT('Payment - ', p.payment_type) AS description,
                'Income' AS category, p.amount, 'Credit' AS type, CASE p.status WHEN 'paid' THEN 'Completed' WHEN 'pending' THEN 'Pending' ELSE 'Cancelled' END AS status
             FROM payments p WHERE DATE(p.payment_date) BETWEEN :from AND :to",
            ['from' => $dateFrom, 'to' => $dateTo]
        );
        $expenses = $this->db->fetchAll(
            "SELECT e.expense_no AS id, e.expense_date AS date, e.description, 'Expense' AS category, e.amount, 'Debit' AS type,
                CASE e.status WHEN 'paid' THEN 'Completed' WHEN 'approved' THEN 'Completed' WHEN 'rejected' THEN 'Cancelled' ELSE 'Pending' END AS status
             FROM expenses e WHERE e.expense_date BETWEEN :from AND :to",
            ['from' => $dateFrom, 'to' => $dateTo]
        );

        $combined = array_merge($payments, $expenses);
        usort($combined, static fn (array $a, array $b): int => strcmp((string) $b['date'], (string) $a['date']));

        return array_slice($combined, 0, $limit);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function generateNumber(string $table, string $column, string $prefix): string
    {
        $yearPrefix = $prefix . '-' . date('Y') . '-';
        $count = (int) ($this->db->fetchOne("SELECT COUNT(*) c FROM `{$table}` WHERE `{$column}` LIKE :prefix", ['prefix' => $yearPrefix . '%'])['c'] ?? 0);

        for ($attempt = $count + 1; $attempt < $count + 1000; $attempt++) {
            $candidate = $yearPrefix . str_pad((string) $attempt, 6, '0', STR_PAD_LEFT);
            if (!$this->db->fetchOne("SELECT 1 FROM `{$table}` WHERE `{$column}` = :no", ['no' => $candidate])) {
                return $candidate;
            }
        }

        return $yearPrefix . bin2hex(random_bytes(4));
    }

    private function intFilter(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }
}
