<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Database;

/**
 * Reusable validation helper for form and file input.
 */
final class Validator
{
    /** @var array<string, array<int, string>> */
    private array $errors = [];

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     */
    private function __construct(private array $data, private array $rules)
    {
        $this->validate();
    }

    /**
     * Creates and runs a validator instance.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     */
    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    /**
     * Returns whether validation passed.
     */
    public function passes(): bool
    {
        return $this->errors === [];
    }

    /**
     * Returns validation errors.
     *
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Executes all rules against the input data.
     */
    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            $rules = explode('|', $ruleString);

            foreach ($rules as $rule) {
                [$name, $parameter] = array_pad(explode(':', $rule, 2), 2, null);
                $this->apply($field, $value, $name, $parameter);
            }
        }
    }

    /**
     * Applies a single validation rule.
     */
    private function apply(string $field, mixed $value, string $rule, ?string $parameter): void
    {
        match ($rule) {
            'required' => $this->required($field, $value),
            'email' => $this->email($field, $value),
            'numeric' => $this->numeric($field, $value),
            'integer' => $this->integer($field, $value),
            'date' => $this->date($field, $value),
            'min' => $this->min($field, $value, (int) $parameter),
            'max' => $this->max($field, $value, (int) $parameter),
            'confirmed' => $this->confirmed($field, $value),
            'unique' => $this->unique($field, $value, (string) $parameter),
            'file' => $this->file($field, $value),
            default => null,
        };
    }

    /** Ensures a value is present. */
    private function required(string $field, mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->add($field, ucfirst(str_replace('_', ' ', $field)) . ' is required.');
        }
    }

    /** Ensures a value is a valid email. */
    private function email(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->add($field, 'Please enter a valid email address.');
        }
    }

    /** Ensures a value is numeric. */
    private function numeric(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->add($field, ucfirst($field) . ' must be numeric.');
        }
    }

    /** Ensures a value is an integer. */
    private function integer(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->add($field, ucfirst($field) . ' must be an integer.');
        }
    }

    /** Ensures a value is a valid date. */
    private function date(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && strtotime((string) $value) === false) {
            $this->add($field, ucfirst($field) . ' must be a valid date.');
        }
    }

    /** Ensures a value has a minimum string length. */
    private function min(string $field, mixed $value, int $length): void
    {
        if (is_string($value) && mb_strlen($value) < $length) {
            $this->add($field, ucfirst($field) . " must be at least {$length} characters.");
        }
    }

    /** Ensures a value has a maximum string length. */
    private function max(string $field, mixed $value, int $length): void
    {
        if (is_string($value) && mb_strlen($value) > $length) {
            $this->add($field, ucfirst($field) . " must not exceed {$length} characters.");
        }
    }

    /** Ensures a confirmation field matches. */
    private function confirmed(string $field, mixed $value): void
    {
        if (($this->data[$field . '_confirmation'] ?? null) !== $value) {
            $this->add($field, ucfirst($field) . ' confirmation does not match.');
        }
    }

    /** Ensures a value is unique in a table column. */
    private function unique(string $field, mixed $value, string $parameter): void
    {
        if ($value === null || $value === '' || !str_contains($parameter, ',')) {
            return;
        }

        [$table, $column] = array_map('trim', explode(',', $parameter, 2));
        $row = Database::getInstance()->fetchOne("SELECT 1 FROM `{$table}` WHERE `{$column}` = :value LIMIT 1", ['value' => $value]);

        if ($row !== null) {
            $this->add($field, ucfirst($field) . ' has already been taken.');
        }
    }

    /** Ensures an uploaded file has no upload error. */
    private function file(string $field, mixed $value): void
    {
        if (!is_array($value) || (int) ($value['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->add($field, ucfirst($field) . ' must be a valid uploaded file.');
        }
    }

    /** Adds an error message. */
    private function add(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
