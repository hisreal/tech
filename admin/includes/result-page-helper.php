<?php
/** Reusable Result Management page renderer. */
function sms_result_status_class(string $status): string
{
    return 'status-' . strtolower(str_replace(' ', '-', $status));
}

function sms_result_render_cards(array $cards): void
{
    echo '<section class="row g-3 mb-4">';
    foreach ($cards as $card) {
        echo '<div class="col-sm-6 col-xl-3">';
        sms_render_component('statistics-card', $card);
        echo '</div>';
    }
    echo '</section>';
}

function sms_result_render_badge(string $status): string
{
    return '<span class="status-badge ' . sms_e(sms_result_status_class($status)) . '"><i class="fa-solid fa-circle"></i> ' . sms_e($status) . '</span>';
}

?>