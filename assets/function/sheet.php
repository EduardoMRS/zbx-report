<?php
// - Funções auxiliares para processar arquivos
function parseExcelFile($filePath)
{
    // Requer a biblioteca PhpSpreadsheet (instale via composer: composer require phpoffice/phpspreadsheet)
    require_once __DIR__.'/../../vendor/autoload.php';

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    $headers = array_shift($rows);
    $headers = array_map('strtolower', $headers);

    $data = [];
    foreach ($rows as $row) {
        if (empty(array_filter($row))) continue; // Pular linhas vazias
        $data[] = array_combine($headers, $row);
    }

    return $data;
}

function parseCSVFile($filePath, $delimiter = ',')
{
    $handle = fopen($filePath, 'r');
    if ($handle === false) {
        throw new Exception('Não foi possível abrir o arquivo CSV');
    }

    $headers = fgetcsv($handle, 0, $delimiter);
    if ($headers === false) {
        throw new Exception('CSV vazio ou inválido');
    }
    $headers = array_map('strtolower', $headers);

    $data = [];
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        if (empty(array_filter($row))) continue; // Pular linhas vazias
        $data[] = array_combine($headers, $row);
    }

    fclose($handle);
    return $data;
}
