<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/** Builds the authenticated student sidebar profile summary from database records. */
final class StudentSidebarService
{
    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /** @param array<string,mixed>|null $authUser @return array<string,mixed> */
    public function summary(?array $authUser): array
    {
        if (!$authUser || empty($authUser['id'])) {
            return $this->fallback();
        }

        $student = $this->db->fetchOne(
            "SELECT
                s.id,
                s.registration_no,
                s.first_name,
                s.middle_name,
                s.last_name,
                s.passport_path,
                c.name AS class_name,
                sec.name AS section_name
            FROM students s
            LEFT JOIN student_enrollments se ON se.student_id = s.id AND se.status = 'active'
            LEFT JOIN classes c ON c.id = se.class_id
            LEFT JOIN sections sec ON sec.id = se.section_id
            WHERE s.user_id = :user_id
            ORDER BY se.id DESC
            LIMIT 1",
            ['user_id' => (int) $authUser['id']]
        );

        if (!$student) {
            return $this->fallback((string) ($authUser['username'] ?? 'Student'));
        }

        $fullName = trim(implode(' ', array_filter([
            $student['first_name'] ?? '',
            $student['middle_name'] ?? '',
            $student['last_name'] ?? '',
        ])));

        return [
            'name' => $fullName !== '' ? $fullName : (string) ($authUser['username'] ?? 'Student'),
            'role' => 'Student',
            'image' => $this->photo((string) ($student['passport_path'] ?? '')),
            'meta' => [
                'Reg No' => (string) ($student['registration_no'] ?? 'Not set'),
                'Class' => (string) ($student['class_name'] ?? 'Not set'),
                'Section' => (string) ($student['section_name'] ?? 'Not set'),
                'Term' => $this->currentTermName(),
            ],
        ];
    }

    /** @return array<string,mixed> */
    private function fallback(string $name = 'Student'): array
    {
        return [
            'name' => $name,
            'role' => 'Student',
            'image' => '../assets/img/avatar/avatar1.jpg',
            'meta' => [
                'Reg No' => 'Not set',
                'Class' => 'Not set',
                'Section' => 'Not set',
                'Term' => $this->currentTermName(),
            ],
        ];
    }

    private function photo(string $path): string
    {
        $path = trim($path);
        return $path !== '' ? '../' . ltrim($path, './') : '../assets/img/avatar/avatar1.jpg';
    }

    private function currentTermName(): string
    {
        $setting = $this->db->fetchOne("SELECT setting_value FROM school_settings WHERE setting_key = 'academic.current_term_id' LIMIT 1");
        $termId = (int) ($setting['setting_value'] ?? 0);

        if ($termId > 0) {
            $term = $this->db->fetchOne('SELECT name FROM terms WHERE id = :id LIMIT 1', ['id' => $termId]);
            if ($term && !empty($term['name'])) {
                return (string) $term['name'];
            }
        }

        $active = $this->db->fetchOne("SELECT name FROM terms WHERE status = 'active' ORDER BY id DESC LIMIT 1");
        return $active ? (string) $active['name'] : 'Not set';
    }
}
