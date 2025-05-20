<?php
function handleHostRequest($method, $operation, $data)
{
    try {
        $zabbixApi = new ZabbixAPI();

        switch ($operation) {
            case 'search':
                handleHostSearch($method, $data, $zabbixApi);
                break;
            case 'details':
                handleHostDetails($method, $data, $zabbixApi);
                break;
            case 'get-interface-list':
                handleHostGetInterfaceList($method, $data, $zabbixApi);
                break;
            case 'get-dedicated-list':
                handleHostGetDedicatedList($method, $data, $zabbixApi);
                break;
            case 'get-interface-chart':
                handleHostGetInterfaceGraph($method, $data, $zabbixApi);
                break;
            case 'load-interface-history':
                handleHostLoadInterfaceHistory($method, $data, $zabbixApi);
                break;
            default:
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Operação inválida']);
                return;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}


function handleHostSearch($method, $dataReceiver, $zabbixApi)
{
    if ($method != "GET") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    try {
        // Parâmetros da paginação
        $page = isset($dataReceiver['page']) ? max(1, intval($dataReceiver['page'])) : 1;
        $perPage = 50;
        $searchTerm = isset($dataReceiver['search']) ? trim($dataReceiver['search']) : '';

        $sortColumn = $dataReceiver['sort'] ?? 'name';
        $sortOrder = $dataReceiver['order'] ?? 'asc';

        // Primeiro obtemos TODOS os hosts (sem paginação inicial)
        $allHosts = $zabbixApi->getHosts(
            ['hostid', 'host', 'description', 'tags', 'macros', 'name', 'status'],
            ['interfaceid', 'ip', 'port', 'type', 'main']
        );

        $allHosts = sortArray($allHosts, $sortColumn, $sortOrder);
        // Filtramos os hosts localmente se houver termo de pesquisa
        if (!empty($searchTerm)) {
            $allHosts = array_filter($allHosts, function ($host) use ($searchTerm) {
                $nameMatch = stripos($host['name'] ?? '', $searchTerm) !== false;
                $hostDescription = stripos($host['description'], $searchTerm) !== false;

                $interfaceMatch = false;
                if (!empty($host['interfaces'])) {
                    foreach ($host['interfaces'] as $interface) {
                        if (
                            stripos($interface['ip'] ?? '', $searchTerm) !== false ||
                            stripos($interface['port'] ?? '', $searchTerm) !== false
                        ) {
                            $interfaceMatch = true;
                            break;
                        }
                    }
                }

                return $nameMatch || $hostDescription  || $interfaceMatch;
            });
        }

        // Aplicamos paginação nos resultados filtrados
        $totalHosts = count($allHosts);
        $paginatedHosts = array_slice($allHosts, ($page - 1) * $perPage, $perPage);

        // Formatar a resposta
        $response = [
            'hosts' => array_map(function ($host) {
                return [
                    'id' => $host['hostid'],
                    'name' => $host['name'] ?? $host['host'], // Usa host se name não existir
                    'host' => $host['host'],
                    'status' => $host['status'],
                    'ip' => $host['interfaces'][0]['ip'] ?? '0.0.0.0',
                    'port' => $host['interfaces'][0]['port'] ?? ''
                ];
            }, $paginatedHosts),
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $totalHosts,
                'totalPages' => ceil($totalHosts / $perPage)
            ]
        ];

        $hostList = [];
        foreach ($response["hosts"] as $item) {
            $isDedicated = mysql_select_in_array("dedicado", "hostid='$item[id]'") ?? null;
            $hostList[] = array_merge($item, ["dedicated" => ($isDedicated ? "true" : "false")]);
        }

        $response["hosts"] = $hostList;

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function handleHostDetails($method, $dataReceiver, $zabbixApi)
{
    if ($method != "GET") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    $hostid = $dataReceiver["hostid"] ?? null;
    if (!$hostid) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID do Host não fornecido!']);
        return;
    }

    try {
        // Obter detalhes completos do host
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

        $isDedicated = mysql_select_in_array("dedicado", "hostid='$response[id]'") ?? null;
        $response = array_merge($response, ["dedicated" => ($isDedicated ? "true" : "false")]);

        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $response]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}


function handleHostGetInterfaceList($method, $dataReceiver, $zabbixApi)
{
    if ($method != "POST") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    $hostid = $dataReceiver["hostid"] ?? null;
    if (!$hostid) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID do Host não fornecido!']);
        return;
    }



    try {
        $response = $zabbixApi->sendRequest('graph.get', [
            'hostids' => $hostid,
            "output" => ["graphid", "name"],
            "selectDiscoveryRule" => true,
            "filter" => [
                "discoveryRule" => [
                    "itemid" => "202371"
                ]
            ],
            "sortfield" => "name",
            "excludeSearch"
        ]);

        if (empty($response)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Nenhum gráfico de interface encontrado!']);
            return;
        }


        // Processa para extrair apenas o nome da interface e ID do gráfico
        $interfaces = array_map(function ($graph) {
            return [
                'graphid' => $graph['graphid'],
                'interface' => preg_replace('/^.*\{#IFNAME}\s*:\s*/', '', $graph['name'])
            ];
        }, $response);

        if (!isset($dataReceiver["all"])) {
            $graph_in_use = mysql_select_in_array("dedicado", "hostid='$hostid'") ?? null;
            if ($graph_in_use) {
                $interfaces = array_filter($interfaces, function ($graph) use ($graph_in_use, $dataReceiver) {
                    return in_array($graph["graphid"], $graph_in_use) == false || (isset($dataReceiver["graphid"]) && $graph["graphid"] == $dataReceiver["graphid"]);
                });
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $interfaces]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function handleHostGetDedicatedList($method, $dataReceiver, $zabbixApi)
{
    if ($method != "POST") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    $hostid = $dataReceiver["hostid"] ?? null;
    if (!$hostid) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID do Host não fornecido!']);
        return;
    }

    $dedicatedList = mysql_select_all_in_array("dedicado", "hostid='$hostid'") ?? null;
    if (!$dedicatedList) {
        http_response_code(200);
        echo json_encode(['status' => 'error', 'message' => 'Este Host não possui dedicados associados!']);
        return;
    }

    try {
        $response = $zabbixApi->sendRequest('graph.get', [
            'hostids' => $hostid,
            "output" => ["graphid", "name"],
            "selectDiscoveryRule" => true,
            "filter" => [
                "discoveryRule" => [
                    "itemid" => "202371"
                ]
            ],
            "sortfield" => "name"
        ]);

        if (empty($response)) {
            http_response_code(200);
            echo json_encode(['status' => 'error', 'message' => 'Nenhum gráfico de interface encontrado!']);
            return;
        }

        // Processa para extrair apenas o nome da interface e ID do gráfico
        $interfaces = array_map(function ($graph) {
            return [
                'graphid' => $graph['graphid'],
                'interface' => preg_replace('/^.*\{#IFNAME}\s*:\s*/', '', $graph['name'])
            ];
        }, $response);

        $is_dedicated = [];
        foreach ($interfaces as $item) {
            $filtered = array_filter($dedicatedList, function ($dedicated) use ($item) {
                return $dedicated['graphid'] == $item['graphid'];
            });

            if (!empty($filtered)) {
                foreach ($filtered as $item) {
                    $is_dedicated[] = $item;
                }
            }
        }


        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $is_dedicated]);
        return;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        return;
    }
}


function handleHostGetInterfaceGraph($method, $dataReceiver, $zabbixApi)
{
    if ($method != "POST") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    // Validação dos parâmetros
    $hostid = $dataReceiver["hostid"] ?? null;
    $graphid = $dataReceiver["graphid"] ?? null;

    if (!$hostid || !$graphid) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'HostID ou GraphID não fornecido!']);
        return;
    }

    try {
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

        // Lógica para determinar o período
        $time_from = null;
        $time_till = time(); // Padrão: agora

        // Se for passado um range completo (from e till)
        if (isset($dataReceiver['time_from']) && isset($dataReceiver['time_till'])) {
            $time_from = (int)$dataReceiver['time_from'];
            $time_till = (int)$dataReceiver['time_till'];
        }
        // Se for passado apenas um período (em segundos)
        elseif (isset($dataReceiver['period'])) {
            $period = (int)$dataReceiver['period'];
            $time_from = time() - $period;
        }
        // Default: último mês (se nada for especificado)
        else {
            $time_from = time() - (3600 * 24 * 30);
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

        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $formattedData]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}


function handleHostLoadInterfaceHistory($method, $dataReceiver)
{
    if ($method != "POST") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }
    genReport($dataReceiver);
}
