<?php
require_once __DIR__ . "/../autoload.php";

$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? $_POST;

file_put_contents('mail_send_debug.log', date('Y-m-d H:i:s') . " - Input: " . $input . "\n", FILE_APPEND);

if (!$data) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Dados não fornecidos ou formato inválido']);
    exit;
}

if (empty($data) || !isset($data[0]['id']) || !isset($data[0]['email']) || !isset($data[0]['is_fail'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Estrutura de dados inválida']);
    exit;
}

$results = [];
foreach ($data as $item) {
    $insertResult = mysql_insert(
        "mail_send",
        [
            "dedicado" => $item["id"],
            "email" => $item["email"],
            "is_fail" => $item["is_fail"],
        ]
    );

    $results[] = [
        'id' => $item["id"],
        'success' => $insertResult
    ];

    file_put_contents('mail_send_debug.log', date('Y-m-d H:i:s') . " - Insert: " .
        $item["id"] . " - " . $item["is_fail"] . " - " . ($insertResult ? 'OK' : 'FAIL') . "\n", FILE_APPEND);
}

echo json_encode(['status' => 'success', 'results' => $results]);
