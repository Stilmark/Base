<?php

namespace Stilmark\Base;

class Render {

    public static function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function csv($data, string $filename, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $fp = fopen('php://output', 'w');
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        exit;
    }

    public static function view($view, $data = []): void
    {
        // Todo: Implement view rendering
    }

}