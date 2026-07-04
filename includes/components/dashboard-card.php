<?php
/**
 * Reusable dashboard/KPI card.
 */

$title = $title ?? '';
$value = $value ?? '';
$icon = $icon ?? 'fa-chart-line';
$color = $color ?? 'success';
$link = $link ?? null;
$body = '<div class="summary-card"><span class="summary-icon ' . sms_e($color) . '"><i class="fa-solid ' . sms_e($icon) . '"></i></span><h4>' . sms_e($value) . '</h4><p class="text-muted mb-0">' . sms_e($title) . '</p></div>';

echo $link ? '<a href="' . sms_e($link) . '" class="text-decoration-none">' . $body . '</a>' : $body;
