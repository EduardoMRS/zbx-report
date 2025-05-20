<?php
function handleDedicatedRequest($method, $operation, $data)
{
    try {
        switch ($operation) {
            case 'search':
                handleDedicatedSearch($method, $data);
                break;
            case 'get':
                handleDedicatedGet($method, $data);
                break;
            case 'save':
                handleDedicatedSave($method, $data);
                break;
            case 'delete':
                handleDedicatedDelete($method, $data);
                break;
            case 'sent-report':
                handleSentReport($method, $data);
                break;
            default:
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Operação inválida']);
                return;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        return;
    }
}


function handleDedicatedSearch($method, $dataReceiver)
{
    if ($method != "GET") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    try {
        $page = isset($dataReceiver['page']) ? max(1, intval($dataReceiver['page'])) : 1;
        $perPage = 50;
        $searchTerm = isset($dataReceiver['search']) ? trim($dataReceiver['search']) : '';

        $sortColumn = $dataReceiver['sort'] ?? 'name';
        $sortOrder = $dataReceiver['order'] ?? 'asc';

        $allDedicated = mysql_select_all_in_array("dedicado") ?? null;
        if (!$allDedicated) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => [],
                'count' => 0
            ]);
            exit;
        }
        $allDedicated = sortArray($allDedicated, $sortColumn, $sortOrder);

        if (!empty($searchTerm)) {
            $allDedicated = array_filter($allDedicated, function ($dedicated) use ($searchTerm) {
                $idMatch = stripos($dedicated['id'] ?? '', $searchTerm) !== false;
                $nameMatch = stripos($dedicated['name'] ?? '', $searchTerm) !== false;
                $addessMatch = stripos($dedicated['address'], $searchTerm) !== false;
                $emailMatch = stripos($dedicated['email'], $searchTerm) !== false;

                return $idMatch || $nameMatch || $addessMatch || $emailMatch;
            });
        }

        // Aplicamos paginação nos resultados filtrados
        $totalDedicated = count($allDedicated);
        $paginatedDedicated = array_slice($allDedicated, ($page - 1) * $perPage, $perPage);

        // Formatar a resposta
        $response = [
            'dedicateds' => array_map(function ($dedicated) {
                return [
                    'id' => $dedicated['id'],
                    'name' => $dedicated['name'],
                    'address' => $dedicated['address'],
                    'email' => $dedicated['email']
                ];
            }, $paginatedDedicated),
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $totalDedicated,
                'totalPages' => ceil($totalDedicated / $perPage)
            ]
        ];

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}


function handleDedicatedGet($method, $dataReceiver)
{
    if ($method != "GET") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    $hostid = $dataReceiver["hostid"] ?? null;
    if (!$hostid) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'ID do host não fornecido!']);
        return;
    }
    $dedicatedId = isset($dataReceiver["id"]) && (int)$dataReceiver["id"] != 0 ? $dataReceiver["id"] : null;

    if ($dedicatedId) {
        $dedicated = mysql_select_in_array("dedicado", "id='$dedicatedId'") ?? [];
    } else {
        $dedicated = mysql_select_in_array("dedicado", "hostid='$hostid'") ?? [];
    }

    try {
        $zabbixApi = new ZabbixAPI();
        $hostDetails = $zabbixApi->sendRequest('host.get', [
            'hostids' => $hostid,
            'output' => 'extend',
            'selectInterfaces' => 'extend',
            'selectGroups' => 'extend',
            'selectTags' => 'extend',
            'selectMacros' => 'extend',
            'selectInventory' => 'extend'
        ]);

        if (empty($hostDetails)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Host não encontrado']);
            return;
        }

        $host = $hostDetails[0];

        // Formatar a resposta
        $response = [
            'id' => $host['hostid'],
            'host' => $host['host'],
            'name' => $host['name'],
            'status' => $host['status'],
            'description' => $host['description'] ?? '',
            'interfaces' => $host['interfaces'] ?? [],
            'groups' => $host['groups'] ?? [],
            'tags' => $host['tags'] ?? [],
            'macros' => $host['macros'] ?? [],
            'inventory' => $host['inventory'] ?? []
        ];

        $response = array_merge($response, ["dedicated" => $dedicated]);

        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $response]);
        return;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        return;
    }
}

function handleDedicatedSave($method, $dataReceiver)
{
    if ($method != "PUT") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    if (!isset($dataReceiver["name"]) || !isset($dataReceiver["email"]) || !isset($dataReceiver["date_send_mail"]) || !isset($dataReceiver["hostid"]) || !isset($dataReceiver["graphid"])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Dados para registro do host faltando!']);
        return;
    }

    $hostid = $dataReceiver["hostid"] ?? null;
    $graphid = $dataReceiver["graphid"] ?? null;
    $dedicatedId = $dataReceiver["id"] ?? null;

    if ($dedicatedId && (int)$dedicatedId != 0) {
        $dedicated = $dedicatedId ? mysql_select_in_array("dedicado", "id='$dedicatedId'") : null;
    } else {
        $dedicated = $hostid && $graphid ? mysql_select_in_array("dedicado", "hostid='$hostid' and graphid='$graphid'") ?? null : null;
    }

    $data = [
        "name" => "$dataReceiver[name]",
        "email" => "$dataReceiver[email]",
        "address" => "$dataReceiver[address]" ?? "",
        "date_send_mail" => "$dataReceiver[date_send_mail]",
        "auto_send" => isset($dataReceiver["auto_send"]) ? ($dataReceiver["auto_send"] == "true" ? "1" : "0") : "0",
        "hostid" => "$dataReceiver[hostid]",
        "graphid" => "$dataReceiver[graphid]"
    ];

    if ($dedicated) {
        if (mysql_update("dedicado", $data, "id", "$dedicated[id]")) {
            echo json_encode([
                "status" => "success",
                "message" => "Cliente atualizados com sucesso!"
            ]);
            return;
        }
    } else {
        $newDedicated = mysql_insert("dedicado", $data);
        if ($newDedicated) {
            echo json_encode([
                "status" => "success",
                "message" => "Cliente adicionado com sucesso!",
                "id" => $newDedicated
            ]);
            return;
        }
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Oops! Ocorreu um erro inesperado...']);
    exit;
}


function handleDedicatedDelete($method, $dataReceiver)
{
    if ($method != "DELETE") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    $dedicatedId = $dataReceiver["id"] ?? null;
    if (!$dedicatedId) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'ID do cliente não informado!']);
        return;
    }

    $dedicated = $dedicatedId != 0 ? mysql_select_in_array("dedicado", "id='$dedicatedId'") ?? null : null;

    if ($dedicated) {
        if (mysql_delete("dedicado", "id", "$dedicated[id]")) {
            echo json_encode([
                "status" => "success",
                "message" => "O cliente '$dedicated[name]'foi deletado com sucesso da lista de dedicados!"
            ]);
            return;
        }
    } else {
        echo json_encode([
            "status" => "success",
            "message" => "Cliente deletado com sucesso!"
        ]);
        return;
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Oops! Ocorreu um erro inesperado...']);
    exit;
}


function handleSentReport($method, $dataReceiver)
{
    if ($method != "POST") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    $dedicatedId = $dataReceiver["dedicatedId"] ?? null;
    if (!$dedicatedId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID do cliente não fornecido!']);
        return;
    }

    $dedicated = mysql_select_in_array("dedicado", "id='$dedicatedId'") ?? null;
    $report = genReport($dataReceiver, true) ?? null;
    if (!$report) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Relatório não encontrado!']);
        return;
    }
    global $url_logo, $suport_phone, $suport_mail, $smtp;
    $time_from = $dataReceiver['time_from'] + (24 * 60 * 60) ?? null;
    $time_till = $dataReceiver['time_till'] ?? null;
    $period = ($time_till - $time_from) / (60 * 60 * 24);

    $mail_body = '<html>
                    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
                        <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                            <div style="text-align: center; margin-bottom: 20px;">
                                <div style="display: flex; justify-content: center; align-items: center;"><img src="' . $url_logo . '" alt="Logo" style="max-height: 80px; max-width: 80px; margin-bottom: 15px;"></div>
                                <h2 style="color: #2c3e50; margin-top: 10px;">Relatório Mensal de Serviços</h2>
                                <p style="color: #7f8c8d; font-size: 14px;">Enviado em ' . date("d/m/Y")  . '</p>
                            </div>
                            
                            <p>Prezado(a) Cliente,</p>
                            
                            <p>Segue em anexo o relatório de serviços ao período de ' . date("d/m/Y", $time_from) . " a " . date("d/m/Y", $time_till) . '(' . round($period, 2, PHP_ROUND_HALF_DOWN) . 'dias)</p>
                            
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


    $dedicatedName =  mb_strtoupper(preg_replace('/[^a-zA-Z0-9_-]/', '_', $dedicated["name"]));
    if (sendMail(
        "Relatório Mensal - " . mb_strtoupper($dedicated["name"]) . " - " . date("d/m/Y", $time_from) . " a " . date("d/m/Y", $time_till),
        $dedicated["email"],
        $mail_body,
        $smtp["mailUsername"],
        [["path" => $report, "name" => "relatorio_de_consumo_{$dedicatedName}_" . date("d/m/Y", $time_from) . "_a_" . date("d/m/Y", $time_till)]]
    )) {
        $insertResult = mysql_insert(
            "mail_send",
            [
                "dedicado" => $dedicated["id"],
                "email" => $dedicated["email"],
                "subject" => "Relatório Mensal - " . date("d/m/Y", $time_from) . " a " . date("d/m/Y", $time_till),
                "is_fail" => 0,
            ]
        );
        echo json_encode([
            "status" => "success",
            "message" => "Relatório enviado com sucesso para o cliente '$dedicated[name]'!"
        ]);
        file_put_contents(__DIR__ . '/../../consumption-report/mail_send_debug.log', date('Y-m-d H:i:s') . " - Envio Manual: " .
            $dedicated["id"] . " - SEND_MAIL_" . $insertResult . " - " . ($insertResult ? 'OK' : 'FAIL') . "\n", FILE_APPEND);
        unlink($report);
        return;
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Ocorreu um erro ao enviar o relatório!']);
        return;
    }
}
