<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Builds pagination metadata and Bootstrap-compatible pagination links.
 */
final class Paginator
{
    /**
     * Builds pagination state from counts.
     *
     * @return array<string, int>
     */
    public static function make(int $total, int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $lastPage = max(1, (int) ceil($total / $perPage));

        return [
            'total' => $total,
            'page' => min($page, $lastPage),
            'per_page' => $perPage,
            'last_page' => $lastPage,
            'offset' => (min($page, $lastPage) - 1) * $perPage,
        ];
    }

    /** Renders simple Bootstrap-style page links. */
    public static function links(array $pagination, string $baseUrl): string
    {
        $page = (int) $pagination['page'];
        $lastPage = (int) $pagination['last_page'];
        $html = '<nav><ul class="pagination">';
        $html .= self::item('Previous', $baseUrl, max(1, $page - 1), $page === 1);

        for ($i = 1; $i <= $lastPage; $i++) {
            $html .= self::item((string) $i, $baseUrl, $i, false, $i === $page);
        }

        $html .= self::item('Next', $baseUrl, min($lastPage, $page + 1), $page === $lastPage);
        $html .= '</ul></nav>';

        return $html;
    }

    /** Renders one pagination item. */
    private static function item(string $label, string $baseUrl, int $page, bool $disabled = false, bool $active = false): string
    {
        $class = 'page-item' . ($disabled ? ' disabled' : '') . ($active ? ' active' : '');
        $url = htmlspecialchars($baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . 'page=' . $page, ENT_QUOTES, 'UTF-8');

        return sprintf('<li class="%s"><a class="page-link" href="%s">%s</a></li>', $class, $url, htmlspecialchars($label, ENT_QUOTES, 'UTF-8'));
    }
}
