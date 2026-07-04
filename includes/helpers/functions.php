<?php
/**
 * Global helper functions shared by all modules.
 */

if (!function_exists('sms_config')) {
    function sms_config(?string $key = null, $default = null)
    {
        global $smsConfig;

        if (!isset($smsConfig)) {
            require __DIR__ . '/../config/config.php';
        }

        if ($key === null) {
            return $smsConfig;
        }

        $segments = explode('.', $key);
        $value = $smsConfig;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('sms_e')) {
    function sms_e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sms_money')) {
    function sms_money($amount): string
    {
        return sms_config('currency', '₦') . number_format((float) $amount);
    }
}

if (!function_exists('sms_asset')) {
    function sms_asset(string $path, string $prefix = '../'): string
    {
        return rtrim($prefix, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('sms_current_page')) {
    function sms_current_page(): string
    {
        return basename($_SERVER['PHP_SELF'] ?? '');
    }
}

if (!function_exists('sms_is_active_nav')) {
    function sms_is_active_nav(array $item, ?string $currentPage = null): bool
    {
        $currentPage = $currentPage ?: sms_current_page();
        $pages = $item['pages'] ?? [$item['href'] ?? ''];

        return in_array($currentPage, $pages, true);
    }
}

if (!function_exists('sms_render_component')) {
    function sms_render_component(string $component, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../components/' . $component . '.php';
    }
}
