<?php
function genReport($dataReceiver, $onlyPath = false)
{
    require_once __DIR__ . "/../../consumption-report/prepare-pdf.php";

    // Validação dos parâmetros
    $dedicatedId = $dataReceiver["dedicatedId"] ?? null;
    global $pdf_data;

    if (!$dedicatedId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dados para geração do gráfico não fornecidos!']);
        return;
    }

    $dedicated = mysql_select_in_array("dedicado", "id='$dedicatedId'") ?? null;
    if (!$dedicated) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Cliente não encontrado!']);
        return;
    }

    $hostid = $dedicated["hostid"];
    $graphid = $dedicated["graphid"];

    $time_from = $dataReceiver['time_from'] + (24 * 60 * 60) ?? null;
    $time_till = $dataReceiver['time_till'] ?? null;

    if (!$time_from && !$time_till) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Periodo invalido!']);
        return;
    }

    $time_from = (int)$time_from;
    $time_till = (int)$time_till;

    try {
        $zabbixApi = new ZabbixAPI();
        // Verifica se o gráfico pertence ao host
        $graphCheck = $zabbixApi->sendRequest('graph.get', [
            'graphids' => $graphid,
            'hostids' => $hostid,
            "output" => ["graphid", "name"],
            "selectItems" => "extend"
        ]);

        if (empty($graphCheck)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Gráfico não encontrado para este host!']);
            return;
        }

        // Validação do range de tempo
        if ($time_from >= $time_till) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Data inicial deve ser anterior à data final!']);
            return;
        }

        // Obtém os dados do gráfico
        $graphData = $zabbixApi->sendRequest('history.get', [
            'output' => 'extend',
            'history' => 3, // 3 = valores numéricos
            'itemids' => array_column($graphCheck[0]['items'], 'itemid'),
            'time_from' => $time_from,
            'time_till' => $time_till,
            'sortfield' => 'clock',
            'sortorder' => 'ASC'
        ]);

        // Formata os dados
        $formattedData = [
            'graphid' => $graphid,
            'name' => $graphCheck[0]['name'],
            'time_from' => $time_from,
            'time_till' => $time_till,
            'values' => array_values(array_filter(array_map(function ($item) {
                if ((int)$item['value'] > 0) {
                    return [
                        'timestamp' => $item['clock'],
                        'value' => $item['value']
                    ];
                }
                return null;
            }, $graphData)))
        ];

        // Criar nome do arquivo PDF
        $filename = 'relatorio_de_consumo_' . $dedicated['name'] . '_' . date('Y-m-d');
        $filename = mb_strtoupper(preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename)) . ".pdf";

        // Gerar PDF em memória
        $temp_json = [
            "responsavel" => $pdf_data["responsavel"],
            "period" => $pdf_data["period"],
            "data_start" => $time_from,
            "data_end" => $time_till,
            "data" => [
                "name" => $dedicated["name"],
                "history" => $formattedData
            ]
        ];
        $temp_path = __DIR__ . "/../../tmp/";
        if (!is_dir($temp_path)) {
            mkdir($temp_path, 0777, true);
        }

        $pdf_temp = $temp_path . uniqid("temp_history_") . ".pdf";
        generateBandwidthPDF($temp_json, $pdf_temp);

        if (file_exists($pdf_temp)) {
            $cert_p12 = __DIR__ . "/../../signature/cert.p12";
            $cert_pass = null;
            if (isset($pdf_data["signed"]) && $pdf_data["signed"]) {
                $cert_pass = $pdf_data["signature_pass"] ?? null;
            }
            $pdf_temp = ($cert_pass ? assinarPdfComVisualizacao($pdf_temp, $cert_p12, $cert_pass) : null) ?? $pdf_temp;
            if ($onlyPath) {
                return $pdf_temp;
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($pdf_temp));
            readfile($pdf_temp);

            // Limpar arquivo temporário
            unlink($pdf_temp);
            exit;
        } else {
            throw new Exception("Falha ao gerar o PDF");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
