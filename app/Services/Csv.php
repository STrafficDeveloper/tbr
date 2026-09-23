<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Streams a CSV download that opens cleanly in Excel. Every value came from
 * the public, so cells that would start a formula are defused first.
 */
final class Csv
{
    /**
     * @param list<string> $headings
     * @param iterable<list<string|int|float|null>> $rows
     */
    public static function download(string $filename, array $headings, iterable $rows): never
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '-', $filename) . '"');
        header('Cache-Control: no-store');

        $out = fopen('php://output', 'wb');

        // BOM: without it Excel reads UTF-8 as ANSI and mangles names.
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headings, ',', '"', '');

        foreach ($rows as $row) {
            fputcsv($out, array_map(self::safe(...), $row), ',', '"', '');
        }

        fclose($out);
        exit;
    }

    /**
     * A member named "=HYPERLINK(...)" would otherwise run as a formula in the
     * admin's spreadsheet (CSV injection). A leading quote makes it text.
     */
    public static function safe(string|int|float|null $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $value : $value;
    }
}
