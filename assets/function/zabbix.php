<?php
define('ZABBIX_URL', $zabbix['url'] ?? null);
define('API_TOKEN', $zabbix['api_token'] ?? null);

class ZabbixAPI
{
    private $apiUrl = ZABBIX_URL;
    private $apiToken = API_TOKEN;
    private $requestId;

    public function __construct()
    {
        $this->requestId = 1;
    }

    public function sendRequest($method, $params = [])
    {
        // Preparar os dados da requisição
        $data = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $this->requestId++
        ];

        // Configurar o cabeçalho de autorização
        $headers = [
            'Content-Type: application/json-rpc',
            'Authorization: Bearer ' . $this->apiToken
        ];

        // Inicializar cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para desenvolvimento

        // Executar a requisição
        $response = curl_exec($ch);

        // Verificar erros
        if (curl_errno($ch)) {
            throw new Exception('Erro cURL: ' . curl_error($ch));
        }

        curl_close($ch);

        // Decodificar a resposta
        $decodedResponse = json_decode($response, true);

        if (isset($decodedResponse['error'])) {
            throw new Exception('Erro Zabbix API: ' . $decodedResponse['error']['data']);
        }

        return $decodedResponse['result'];
    }

    // Métodos específicos da API

    public function getHosts($output = ['hostid', 'host'], $selectInterfaces = [], $limit = null, $offset = null, $search = null)
    {
        $params = [
            'output' => $output,
            'selectInterfaces' => $selectInterfaces
        ];

        // Adiciona parâmetros de paginação se fornecidos
        if ($limit !== null) {
            $params['limit'] = $limit;
        }
        if ($offset !== null) {
            $params['startSearch'] = true; // Habilita busca com offset
        }

        if ($search !== null && $search !== '') {
            $params['search'] = [
                'name' => $search,
                'host' => $search
            ];
            // Para interfaces, precisamos filtrar depois
        }

        $result = $this->sendRequest('host.get', $params);

        // Filtro adicional para interfaces se houver termo de pesquisa
        if ($search !== null && $search !== '') {
            $result = array_filter($result, function ($host) use ($search) {
                if (!empty($host['interfaces'])) {
                    foreach ($host['interfaces'] as $interface) {
                        if (
                            stripos($interface['ip'] ?? '', $search) !== false ||
                            stripos($interface['port'] ?? '', $search) !== false
                        ) {
                            return true;
                        }
                    }
                }
                return false;
            });
        }

        return $result;
    }

    public function getItems($hostids, $output = ['itemid', 'name', 'key_', 'lastvalue'])
    {
        $params = [
            'hostids' => $hostids,
            'output' => $output
        ];

        return $this->sendRequest('item.get', $params);
    }
}
