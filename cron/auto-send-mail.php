<?php
require_once __DIR__ . "/../autoload.php";
require_once __DIR__ . "/../api/zabbix.php";
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

$today = date("d");
$send_today = mysql_select_all_in_array("dedicado", "date_send_mail='$today' and auto_send='1'");

$prepared = [];
$send_list = __DIR__ . "/../consumption/send_list.json";

if ($send_today) {
    $zabbixApi = new ZabbixAPI();
    $gen_consumption = __DIR__ . "/../consumption/send-mail.py";
    $log_file = __DIR__ . "/../log/auto_send_mail_log.txt";

    $period = $pdf_data["period"];
    $time_till = time();
    $time_from = $time_till - $period;
    foreach ($send_today as $item) {
        $graphid = $item["graphid"];
        $hostid = $item["hostid"];

        $graphCheck = $zabbixApi->sendRequest('graph.get', [
            'graphids' => $graphid,
            'hostids' => $hostid,
            "output" => ["graphid", "name"],
            "selectItems" => "extend"
        ]);

        if (empty($graphCheck)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Gráfico não encontrado no host!']);
            return;
        }

        $graphData = $zabbixApi->sendRequest('history.get', [
            'output' => 'extend',
            'history' => 3,
            'itemids' => array_column($graphCheck[0]['items'], 'itemid'),
            'time_from' => time() - $period,
            'time_till' => time(),
            'sortfield' => 'clock',
            'sortorder' => 'ASC'
        ]);

        $formattedData = [
            'graphid' => $graphid,
            'name' => $graphCheck[0]['name'],
            'period' => $period,
            'values' => array_values(array_filter(array_map(function ($item) {
                if ((int)$item['value'] > 0) {
                    return [
                        'timestamp' => $item['clock'],
                        'value' => $item['value']
                    ];
                }
                return null; // Explícito para clareza
            }, $graphData)))
        ];

        $dedicated_data = [
            "name" => mb_strtoupper($item["name"]),
            "time_from" => $time_from,
            "time_till" => $time_till,
            "graphid" => $graphid,
            "hostid" => $hostid,
            "dedicatedId" => $item["id"]
        ];

        $report = genReport($dedicated_data, true) ?? null;
        if (!$report) {
            file_put_contents(__DIR__ . "/error_prepare.txt", date("Y-m-d H:i:s") . ": Erro ao gerar relatório para o cliente: " . mb_strtoupper($item["name"]) . "\n", FILE_APPEND);
        } else {
            $mail_body = '<html>
            <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
                <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: center; align-items: center;"><img src="' . $url_logo . '" alt="' . $smtp["srcname"] . '" style="max-height: 80px; max-width: 80px; margin-bottom: 15px;"></div>
                        <h2 style="color: #2c3e50; margin-top: 10px;">Relatório Mensal de Serviços</h2>
                        <p style="color: #7f8c8d; font-size: 14px;">Enviado em ' . date("d/m/Y")  . '</p>
                    </div>
                    
                    <p>Prezado(a) Cliente,</p>
                    
                    <p>Segue em anexo o relatório de serviços ao período de ' . date("d/m/Y", $time_from) . " a " . date("d/m/Y", $time_till) . '(' . round(($period / (24 * 60 * 60)), 2, PHP_ROUND_HALF_DOWN) . 'dias)</p>
                    
                    <p>Este relatório contém informações detalhadas sobre o desempenho e disponibilidade dos serviços contratados.</p>
                    
                    <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <p style="margin: 0; font-weight: bold;">Dúvidas ou informações adicionais:</p>
                        <p style="margin: 5px 0 0 0;">Telefone: ' . $suport_phone . '</p>
                        <p style="margin: 5px 0 0 0;">Email: <a href="mailto:' . $suport_mail . '">' . $suport_mail . '</a></p>
                    </div>
                    
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                        <p>Atenciosamente,</p>
                        <p><strong>' . $smtp["srcname"] . '</strong><br>
                        Setor de NOC</p>
                    </div>
                </div>
            </body>
        </html>';


            $dedicatedName =  mb_strtoupper(preg_replace('/[^a-zA-Z0-9_-]/', '_', $item["name"]));
            $isSend = sendMail(
                "Relatório Mensal - " . mb_strtoupper($item["name"]) . " - " . date("d/m/Y", $time_from) . " a " . date("d/m/Y", $time_till),
                $item["email"],
                $mail_body,
                $smtp["mailUsername"],
                [["path" => $report, "name" => "relatorio_de_consumo_{$dedicatedName}_" . date("d/m/Y", $time_from) . "_a_" . date("d/m/Y", $time_till)]]
            ) ?? null;
            if (!$isSend) {
                file_put_contents(__DIR__ . "/error_prepare.txt", date("Y-m-d H:i:s") . ": Falha ao enviar o relatorio do cliente '" . mb_strtoupper($item["name"]) . "' para '$item[email]' \n", FILE_APPEND);
            }

            $insertResult = mysql_insert(
                "mail_send",
                [
                    "dedicado" => $item["id"],
                    "email" => $item["email"],
                    "subject" => "Relatório Mensal - " . date("d/m/Y", $time_from) . " a " . date("d/m/Y", $time_till),
                    "is_fail" => $isSend ? 0 : 1,
                ]
            );

            file_put_contents(__DIR__ . '/../consumption-report/mail_send_debug.log', date('Y-m-d H:i:s') . " - Envio Automatico: " .
                $item["id"] . " - SEND_MAIL_" . $insertResult . " - " . ($insertResult ? 'OK' : 'FAIL') . "\n", FILE_APPEND);
            unlink($report);
            return;
        }
    }
} else {
    file_put_contents($send_list, json_encode([], JSON_PRETTY_PRINT));
}
