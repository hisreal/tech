<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\SettingsModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Shared CSV / Excel / PDF export helpers used by every report page across
 * the system, so each module only needs to gather (headers, rows) and hand
 * off to one of these instead of re-implementing file generation.
 */
final class ReportExporter
{
    /**
     * @param array<int,string> $headers
     * @param array<int,array<int,mixed>> $rows
     */
    public static function csv(string $filename, array $headers, array $rows): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . self::safeFilename($filename) . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    /**
     * @param array<int,string> $headers
     * @param array<int,array<int,mixed>> $rows
     */
    public static function excel(string $filename, string $sheetTitle, array $headers, array $rows): never
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $sheetTitle) ?? 'Report', 0, 31));

        $sheet->fromArray($headers, null, 'A1');
        if ($rows) {
            $sheet->fromArray($rows, null, 'A2');
        }

        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('0F766E');
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->getColor()->setRGB('FFFFFF');

        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . self::safeFilename($filename) . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public static function pdf(string $filename, string $html, string $orientation = 'portrait'): never
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();
        $dompdf->stream(self::safeFilename($filename) . '.pdf', ['Attachment' => true]);
        exit;
    }

    /**
     * Builds a consistently styled HTML document for tabular reports, ready to hand to pdf().
     *
     * @param array<int,string> $headers
     * @param array<int,array<int,mixed>> $rows
     * @param array<string,mixed> $summary Label => value pairs shown above the table.
     */
    public static function tableHtml(string $title, string $subtitle, array $headers, array $rows, array $summary = []): string
    {
        $schoolName = self::schoolName();

        ob_start();
        ?>
        <html>
        <head>
        <meta charset="utf-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #10201d; }
            .head { text-align: center; border-bottom: 2px solid #0f766e; padding-bottom: 8px; margin-bottom: 14px; }
            h1 { font-size: 17px; margin: 0 0 2px; color: #0f766e; }
            h2 { font-size: 12px; margin: 0; color: #64748b; font-weight: normal; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background: #0f766e; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
            td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
            tr:nth-child(even) td { background: #f8fafc; }
            .summary { margin: 12px 0; }
            .summary span { display: inline-block; margin-right: 22px; font-weight: bold; }
            .empty { text-align: center; color: #64748b; padding: 16px; }
            .footer { margin-top: 16px; font-size: 9px; color: #94a3b8; text-align: right; }
        </style>
        </head>
        <body>
        <div class="head">
            <h1><?php echo htmlspecialchars($schoolName); ?></h1>
            <h2><?php echo htmlspecialchars($title); ?><?php echo $subtitle !== '' ? ' - ' . htmlspecialchars($subtitle) : ''; ?></h2>
        </div>
        <?php if ($summary): ?>
        <div class="summary">
            <?php foreach ($summary as $label => $value): ?>
                <span><?php echo htmlspecialchars((string) $label); ?>: <?php echo htmlspecialchars((string) $value); ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <table>
            <thead><tr><?php foreach ($headers as $header): ?><th><?php echo htmlspecialchars($header); ?></th><?php endforeach; ?></tr></thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr><?php foreach ($row as $cell): ?><td><?php echo htmlspecialchars((string) $cell); ?></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?><tr><td class="empty" colspan="<?php echo count($headers); ?>">No records found.</td></tr><?php endif; ?>
            </tbody>
        </table>
        <div class="footer">Generated <?php echo date('Y-m-d H:i'); ?></div>
        </body>
        </html>
        <?php
        return (string) ob_get_clean();
    }

    private static function schoolName(): string
    {
        static $name = null;
        if ($name === null) {
            $settings = (new SettingsModel())->all();
            $name = (string) ($settings['school.name']['value'] ?? 'School Management System');
        }

        return $name;
    }

    private static function safeFilename(string $filename): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename) ?? 'report';
    }
}
