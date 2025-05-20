<?php
require_once(__DIR__ . '/../vendor/autoload.php');
error_reporting(E_ALL & ~E_DEPRECATED);

function generateBandwidthPDF(array $data, string $outputPath)
{
    try {
        // Verificar se o diretório de saída existe
        $outputDir = dirname($outputPath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Configurações de layout (em milímetros)
        $pageWidth = 210; // A4 width
        $pageHeight = 297; // A4 height
        $margins = [
            'left' => 20,
            'right' => 20,
            'top' => 30,
            'bottom' => 20
        ];
        $contentWidth = $pageWidth - $margins['left'] - $margins['right'];

        // Extrair dados do array
        $responsavelTecnico = $data['responsavel'];
        $linkData = $data['data'];
        $historyValues = $linkData['history']['values'];

        // Calcular período
        $dateRange = calculateDateRange($historyValues);
        $startDate = $data["data_start"] ? (is_numeric($data["data_start"]) ? (new DateTime())->setTimestamp($data["data_start"]) : new DateTime($data["data_start"])) : $dateRange['start'];
        $endDate = $data["data_end"] ? (is_numeric($data["data_end"]) ? (new DateTime())->setTimestamp($data["data_end"]) : new DateTime($data["data_end"])) : $dateRange['end'];
        $periodStr = $startDate->format('d/m/Y') . ' a ' . $endDate->format('d/m/Y');


        // Calcular estatísticas
        $stats = calculateStats($historyValues, $startDate, $endDate);

        // Gerar gráfico temporário
        $tempDir = sys_get_temp_dir() . '/bandwidth_reports';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $graphFilename = str_replace(' ', '_', substr($linkData['name'], 0, 20)) . '_graph.png';
        $graphPath = $tempDir . '/' . $graphFilename;
        generateWaveGraph($historyValues, $graphPath);

        // Criar PDF temporário
        $tempPdfPath = $tempDir . '/' . str_replace(' ', '_', substr($linkData['name'], 0, 20)) . '_temp_content.pdf';

        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, $margins['bottom']);
        $pdf->AddPage();

        // Configurar margens
        $pdf->SetLeftMargin($margins['left']);
        $pdf->SetRightMargin($margins['right']);
        $pdf->SetY($margins['top']);

        // Título do relatório
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 10, fixTextUTF_8('RELATÓRIO TÉCNICO DE FORNECIMENTO DE LINK'), 0, 1, 'C');
        $pdf->Ln(5);

        // Configurações de fonte
        $pdf->SetFont('Arial', 'B', 14);

        // Cabeçalho
        $pdf->Cell(0, 10, fixTextUTF_8($linkData['name']), 0, 1, 'C');

        // Informações do relatório
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 10, fixTextUTF_8('ASSUNTO: Consumo referente a ' . $periodStr), 0, 1);
        $pdf->Ln(5);

        $pdf->Cell(0, 10, fixTextUTF_8('Data de emissão: ') . date('d/m/Y'), 0, 1);

        // Tabela de estatísticas
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 10, fixTextUTF_8('Estatísticas:'), 0, 1);
        $pdf->SetFont('Arial', '', 8);

        // Ajuste das larguras das colunas
        $colWidths = [60, 50]; // Nome do campo e Valor/Unidade combinados

        foreach ($stats as $key => $value) {
            if ($pdf->GetY() + 10 > $pageHeight - $margins['bottom']) {
                $pdf->AddPage();
                $pdf->SetY($margins['top']);
            }

            $pdf->Cell($colWidths[0], 8, $key, 1, 0, 'L');
            $pdf->Cell($colWidths[1], 8, $value, 1, 1, 'C');
        }

        $pdf->Ln(15);

        // Gráfico
        if ($pdf->GetY() + 100 > $pageHeight - $margins['bottom']) {
            $pdf->AddPage();
            $pdf->SetY($margins['top']);
        }

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 10, fixTextUTF_8('Gráfico de Consumo:'), 0, 1);
        $pdf->Image($graphPath, $margins['left'], $pdf->GetY(), $contentWidth);
        $pdf->SetY($pdf->GetY() + 60);

        // Assinatura
        if ($pdf->GetY() + 50 > $pageHeight - $margins['bottom']) {
            $pdf->AddPage();
        }

        $pdf->Ln(60); // Espaço antes da assinatura

        // Linha de assinatura (centralizada)
        $lineWidth = 60;
        $lineX = ($pageWidth - $lineWidth) / 2;
        $lineY = $pdf->GetY();
        $pdf->Line($lineX, $lineY, $lineX + $lineWidth, $lineY);

        // Nome do responsável (centralizado)
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, fixTextUTF_8($responsavelTecnico), 0, 1, 'C');

        // Texto "Responsável Técnico" (centralizado)
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 5, fixTextUTF_8("Responsável Técnico"), 0, 1, 'C');

        // Salvar PDF temporário
        $pdf->Output($tempPdfPath, 'F');

        try {
            // Tentar aplicar o timbrado
            applyTimbrado($tempPdfPath, $outputPath);
        } catch (Exception $e) {
            // Se falhar ao aplicar timbrado, copiar o PDF temporário diretamente
            copy($tempPdfPath, $outputPath);
            error_log("Aviso: Não foi possível aplicar o timbrado. PDF gerado sem timbrado. Erro: " . $e->getMessage());
        }

        // Limpar arquivos temporários
        if (file_exists($graphPath)) {
            unlink($graphPath);
        }
        if (file_exists($tempPdfPath)) {
            unlink($tempPdfPath);
        }

        return $outputPath;
    } catch (Exception $e) {
        die("Erro ao gerar PDF: " . $e->getMessage());
        return null;
    }
}

function calculateDateRange(array $values): array
{
    if (empty($values)) {
        $now = new DateTime();
        return ['start' => $now, 'end' => $now];
    }

    $timestamps = array_column($values, 'timestamp');
    return [
        'start' => (new DateTime())->setTimestamp(min($timestamps)),
        'end' => (new DateTime())->setTimestamp(max($timestamps))
    ];
}

function formatBandwidth(float $valueBps): string
{
    if ($valueBps >= 10 ** 9) { // Gbps
        return number_format($valueBps / 10 ** 9, 2) . ' Gbps';
    } elseif ($valueBps >= 10 ** 6) { // Mbps
        return number_format($valueBps / 10 ** 6, 2) . ' Mbps';
    } elseif ($valueBps >= 10 ** 3) { // Kbps
        return number_format($valueBps / 10 ** 3, 2) . ' Kbps';
    }
    return number_format($valueBps, 2) . ' bps';
}

function formatBandwidthTotal(float $valueBits): string
{
    $valueBytes = $valueBits / 8;

    if ($valueBytes >= 1024 ** 4) { // TB
        return number_format($valueBytes / 1024 ** 4, 2) . ' TB';
    } elseif ($valueBytes >= 1024 ** 3) { // GB
        return number_format($valueBytes / 1024 ** 3, 2) . ' GB';
    } elseif ($valueBytes >= 1024 ** 2) { // MB
        return number_format($valueBytes / 1024 ** 2, 2) . ' MB';
    } elseif ($valueBytes >= 1024) { // KB
        return number_format($valueBytes / 1024, 2) . ' KB';
    }
    return number_format($valueBytes, 2) . ' B';
}

function calculateStats(array $values, DateTime $date_start, DateTime $date_end): array
{
    if (empty($values)) {
        return [
            fixTextUTF_8('Consumo Máximo') => "0 bps",
            fixTextUTF_8('Consumo Total') => "0 B",
            fixTextUTF_8('Período Analisado') => fixTextUTF_8("Nenhum dado disponível")
        ];
    }

    // Filtrar valores apenas se necessário (quando o período selecionado é diferente do período dos dados)
    $filteredValues = $values;
    $date_start_ts = $date_start->getTimestamp();
    $date_end_ts = $date_end->getTimestamp();

    $data_start_ts = min(array_column($values, 'timestamp'));
    $data_end_ts = max(array_column($values, 'timestamp'));

    if ($date_start_ts > $data_start_ts || $date_end_ts < $data_end_ts) {
        $filteredValues = array_filter($values, function ($item) use ($date_start_ts, $date_end_ts) {
            return $item['timestamp'] >= $date_start_ts && $item['timestamp'] <= $date_end_ts;
        });
        $filteredValues = array_values($filteredValues); // Reindexar array
    }

    $numericValues = array_column($filteredValues, 'value');
    $periodDays = $date_start->diff($date_end)->days + 1;

    $maxValue = !empty($numericValues) ? max($numericValues) : 0;
    $speedUnit = explode(' ', formatBandwidth($maxValue))[1];

    $convertedMax = $maxValue;
    switch ($speedUnit) {
        case 'Gbps':
            $convertedMax = $maxValue / 10 ** 9;
            break;
        case 'Mbps':
            $convertedMax = $maxValue / 10 ** 6;
            break;
        case 'Kbps':
            $convertedMax = $maxValue / 10 ** 3;
            break;
    }

    $totalBits = !empty($numericValues) ? array_sum($numericValues) * 300 : 0; // Intervalo de 5 minutos (300s)

    return [
        fixTextUTF_8('Consumo Máximo') => number_format($convertedMax, 2) . ' ' . $speedUnit,
        fixTextUTF_8('Consumo Total') => formatBandwidthTotal($totalBits),
        fixTextUTF_8('Período Analisado') => $date_start->format('d/m/Y') . ' - ' .
            $date_end->format('d/m/Y') . ' (' . $periodDays . ' dias)'
    ];
}

function generateWaveGraph(array $values, string $outputPath)
{
    if (empty($values)) {
        throw new Exception("Nenhum dado fornecido para gerar o gráfico");
    }

    $timestamps = array_column($values, 'timestamp');
    $numericValues = array_column($values, 'value');

    // Configurações do gráfico
    $width = 800;
    $height = 400;
    $leftPadding = 100;   // Espaço aumentado para labels Y
    $rightPadding = 40;   // Espaço à direita
    $topPadding = 40;     // Espaço no topo
    $bottomPadding = 80;  // Espaço aumentado para labels X

    // Criar imagem
    $image = imagecreatetruecolor($width, $height);

    // Cores
    $white = imagecolorallocate($image, 255, 255, 255);
    $lightGray = imagecolorallocate($image, 245, 245, 245);
    $blue = imagecolorallocate($image, 31, 119, 180);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 220, 220, 220);
    $darkGray = imagecolorallocate($image, 180, 180, 180);

    // Preencher fundo
    imagefilledrectangle($image, 0, 0, $width, $height, $lightGray);

    // Calcular escalas
    $minValue = min($numericValues);
    $maxValue = max($numericValues);
    $valueRange = $maxValue - $minValue;
    $valueRange = $valueRange == 0 ? 1 : $valueRange;

    $minTime = min($timestamps);
    $maxTime = max($timestamps);
    $timeRange = $maxTime - $minTime;
    $timeRange = $timeRange == 0 ? 1 : $timeRange;

    // Adicionar grid de fundo
    $gridSteps = 5; // Número de linhas de grid
    $valueStep = $valueRange / $gridSteps;

    // Área do gráfico (sem os paddings)
    $graphWidth = $width - $leftPadding - $rightPadding;
    $graphHeight = $height - $topPadding - $bottomPadding;

    for ($i = 0; $i <= $gridSteps; $i++) {
        $value = $minValue + ($i * $valueStep);
        $y = (int)round($topPadding + ($graphHeight - (($value - $minValue) / $valueRange * $graphHeight)));

        // Linha do grid
        imageline($image, $leftPadding, $y, $width - $rightPadding, $y, $gray);
    }

    // Desenhar eixos
    imageline($image, $leftPadding, $topPadding, $leftPadding, $height - $bottomPadding, $black); // Eixo Y
    imageline($image, $leftPadding, $height - $bottomPadding, $width - $rightPadding, $height - $bottomPadding, $black); // Eixo X

    // Desenhar linhas do gráfico
    $prevX = $prevY = 0;
    foreach ($values as $i => $item) {
        $x = (int)round($leftPadding + (($item['timestamp'] - $minTime) / $timeRange * $graphWidth));
        $y = (int)round($topPadding + ($graphHeight - (($item['value'] - $minValue) / $valueRange * $graphHeight)));

        if ($i > 0) {
            imageline($image, $prevX, $prevY, $x, $y, $blue);
            imagesetthickness($image, 2);
        }

        $prevX = $x;
        $prevY = $y;
    }

    // Configurações da fonte
    $fontPath = __DIR__ . '/../assets/fonts/DejaVuSans.ttf';
    if (!file_exists($fontPath)) {
        throw new Exception("Fonte DejaVuSans.ttf não encontrada em: $fontPath");
    }

    // Adicionar rótulo do eixo Y (centralizado verticalmente)
    $maxValueText = formatBandwidth($maxValue);
    $ylabel = "Consumo ($maxValueText)";

    // Centralizar o rótulo verticalmente
    $ylabelX = $leftPadding - 78;
    $ylabelY = (int)(($topPadding + $graphHeight) / 5) * 4;

    // Rotacionar 90 graus e centralizar
    imagettftext(
        $image,
        10,
        90,
        $ylabelX,
        $ylabelY,
        $black,
        $fontPath,
        $ylabel
    );

    // Valores do eixo Y
    for ($i = 0; $i <= $gridSteps; $i++) {
        $value = $minValue + ($i * $valueStep);
        $y = (int)round($topPadding + ($graphHeight - (($value - $minValue) / $valueRange * $graphHeight)));
        $valueText = formatBandwidth($value);

        imagettftext(
            $image,
            8,
            0,
            $leftPadding - 70,
            $y + 4,
            $black,
            $fontPath,
            $valueText
        );
    }

    // Adicionar rótulos do eixo X (datas)
    $dateSteps = 5; // Número de labels de data
    $timeStep = $timeRange / $dateSteps;

    for ($i = 0; $i <= $dateSteps; $i++) {
        $timestamp = $minTime + ($i * $timeStep);
        $x = (int)round($leftPadding + (($timestamp - $minTime) / $timeRange * $graphWidth));

        $date = (new DateTime())->setTimestamp($timestamp);
        $dateText = $date->format('d/m/Y');

        $dateBox = imagettfbbox(8, 45, $fontPath, $dateText);
        $dateWidth = $dateBox[2] - $dateBox[0];
        $dateHeight = $dateBox[3] - $dateBox[5];

        imagettftext(
            $image,
            8,
            45,
            $x - ($dateWidth / 2),
            $height - $bottomPadding + 50,
            $black,
            $fontPath,
            $dateText
        );

        // Marcador no eixo X
        imageline($image, $x, $height - $bottomPadding - 3, $x, $height - $bottomPadding + 3, $black);
    }

    // Salvar imagem
    imagepng($image, $outputPath);
    imagedestroy($image);
}

function applyTimbrado(string $contentPdfPath, string $outputPath)
{
    // Usar FPDI para mesclar com o timbrado
    $pdf = new \setasign\Fpdi\Fpdi();

    // Contar páginas do conteúdo
    $pageCount = $pdf->setSourceFile($contentPdfPath);

    // Verificar se o timbrado existe
    $timbradoPath = __DIR__ . '/../consumption-report/timbrado.pdf';

    if (file_exists($timbradoPath)) {
        // Carregar timbrado
        $timbradoPageId = $pdf->setSourceFile($timbradoPath);
        $timbrado = $pdf->importPage(1);

        // Processar cada página com timbrado
        for ($i = 1; $i <= $pageCount; $i++) {
            $contentPageId = $pdf->setSourceFile($contentPdfPath);
            $content = $pdf->importPage($i);

            // Adicionar página e mesclar
            $pdf->AddPage();
            $pdf->useTemplate($timbrado);
            $pdf->useTemplate($content);
        }
    } else {
        // Processar cada página sem timbrado
        for ($i = 1; $i <= $pageCount; $i++) {
            $contentPageId = $pdf->setSourceFile($contentPdfPath);
            $content = $pdf->importPage($i);

            // Adicionar página apenas com o conteúdo
            $pdf->AddPage();
            $pdf->useTemplate($content);
        }
    }

    $pdf->Output($outputPath, 'F');
}

function fixTextUTF_8($text)
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
}
