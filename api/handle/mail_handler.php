<?php

function handleMailRequest($method, $operation, $data)
{
    try {
        switch ($operation) {
            case 'history-search':
                return handleMailSearch($method, $data);
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

// Função para buscar usuários
function handleMailSearch($method, $data)
{
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
        return;
    }


    // Verifica se há parâmetros de ordenação na URL
    $sortColumn = $data['sort'] ?? 'data_send'; // Coluna padrão para ordenação
    $sortOrder = $data['order'] ?? 'desc';  // Ordem padrão (ascendente)

    $dedicatedList = mysql_select_all_in_array("dedicado") ?? null;

    if(!$dedicatedList) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'content' => [],
            'count' => 0
        ]);
        exit;
    }
    $search = $data['search'] ?? '';

    if ($search && $search != "all") {
        $dedicatedList = array_values(array_filter($dedicatedList, function ($user) use ($search) {
            // Normalizações iniciais
            $normalizedSearch = normalizeString($search);

            // Preparar campos do usuário
            $firstName = normalizeString($user['firstName'] ? $user['firstName'] : '');
            $lastName = normalizeString($user['lastName'] ? $user['lastName'] : '');
            $fullName = $firstName . ' ' . $lastName;
            $email = normalizeString($user['email'] ?? '');

            // Busca em campos textuais (sempre verifica)
            $textMatch = stripos($fullName, $normalizedSearch) !== false ||
                stripos($email, $normalizedSearch) !== false;
            return $textMatch;
        }));
    }

    $mailList = [];
    foreach ($dedicatedList as $item) {
        $result = mysql_select_all_in_array("mail_send", "dedicado='$item[id]'") ?? null;
        if ($result) {
            foreach ($result as $mail) {
                $mailList[] =  array_merge($mail, ["name" => $item["name"]]);
            }
        }
    }
    $mailList = sortArray($mailList, $sortColumn, $sortOrder);

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'content' => $mailList,
        'count' => count($mailList) // Adiciona contagem para debug
    ]);
}
