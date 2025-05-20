<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

function assinarPdfComVisualizacao(
    string $pdfOriginal,
    string $certificadoP12,
    string $senhaCertificado,
    string $saidaPdf = "",
    int $posX = 75,
    int $posY = 240,
    int $largura = 60,
    int $altura = 15
): string {
    if (empty($saidaPdf)) {
        $info = pathinfo($pdfOriginal);
        $saidaPdf = $info['dirname'] . '/' . $info['filename'] . '_assinado.pdf';
    }

    if (!file_exists($pdfOriginal)) {
        throw new Exception("Arquivo PDF não encontrado: " . $pdfOriginal);
    }
    if (!file_exists($certificadoP12)) {
        throw new Exception("Certificado não encontrado: " . $certificadoP12);
    }

    if (!openssl_pkcs12_read(file_get_contents($certificadoP12), $certInfo, $senhaCertificado)) {
        throw new Exception("Falha ao ler o certificado. Verifique a senha.");
    }

    $pdf = new Fpdi();
    $pageCount = $pdf->setSourceFile($pdfOriginal);
    
    for ($i = 1; $i <= $pageCount; $i++) {
        $pdf->AddPage();
        $pageId = $pdf->importPage($i);
        $pdf->useTemplate($pageId);

        if ($i == $pageCount) {
            $pdf->SetXY($posX, $posY);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell($largura, $altura, 'Assinado Digitalmente', 1, 0, 'C', true);
        }
    }

    // Configura a assinatura digital (TCPDF)
    $pdf->setSignature(
        $certInfo['cert'],  // Certificado
        $certInfo['pkey'],  // Chave privada
        $senhaCertificado,   // Senha do certificado
        '',                  // Razão da assinatura (opcional)
        2,                   // Tipo de assinatura (2 = padrão PDF)
        [$posX, $posY, $posX + $largura, $posY + $altura], // Área visível
        'A'                 // Formato de assinatura (A = Adobe.PPKLite)
    );

    $pdf->Output($saidaPdf, 'F');
    return $saidaPdf;
}