<?php
/**
 * Shared validation helpers for future controllers.
 */

function sms_required($value): bool
{
    return trim((string) $value) !== '';
}

function sms_valid_email($value): bool
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}
