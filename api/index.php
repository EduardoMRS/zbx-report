<?php
include_once(__DIR__ . "/../autoload.php");
include_once(__DIR__ . "/../vendor/autoload.php");



$userData = null;
function checkLogin($isAdmin = false)
{
    global $userData, $api;
    $providedApiKey = $_SERVER["$api[header]"] ?? null;
    if ($providedApiKey) {
        if (verifyApiKey($providedApiKey)) {
            return;
        }
        http_response_code(401);
        echo json_encode(['error' => 'API key inválida ou ausente']);
        exit;
    }

    if (!isset($_SESSION["idUser"])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Acesso Negado!']);
        exit;
    }

    $userData = mysql_select_in_array("users", "id=$_SESSION[idUser]");
    if ($isAdmin && $userData["access_level"] != "admin") {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Acesso Negado!']);
        exit;
    }
}

// Definir cabeçalho para JSON
header('Content-Type: application/json');

// Obter o método HTTP correto
$method = $_SERVER['REQUEST_METHOD'];

// Processar os dados de entrada
if ($method === 'POST' || $method === 'PUT' || $method === 'TEST' || $method === 'DELETE') {
    // Para FormData (incluindo uploads de arquivos)
    if (!empty($_FILES)) {
        $data = $_POST; // Dados normais do formulário
        // $_FILES já contém os arquivos enviados
    } else {
        // Para JSON ou outros tipos de POST
        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?? $_POST;
    }
} else {
    $data = $_GET;
}

// Extrair operação
$opParts = isset($_GET['op']) ? explode('-', $_GET['op']) : [null, null];
$type = $opParts[0] ?? null;
$operation = isset($opParts[1]) ? str_replace($type . "-", "", $_GET['op']) : null;

// Roteamento baseado no recurso
switch ($type) {
    case 'host':
        checkLogin();
        include_once __DIR__ . "/handle/host_handler.php";
        handleHostRequest($method, $operation, $data);
        break;
    case 'config':
        checkLogin(true);
        include_once __DIR__ . "/handle/config_handler.php";
        handleConfigRequest($method, $operation, $data);
        break;
    case 'dedicated':
        checkLogin();
        include_once __DIR__ . "/handle/dedicated_handler.php";
        handleDedicatedRequest($method, $operation, $data);
        break;
    case 'user':
        checkLogin(true);
        include_once __DIR__ . "/handle/user_handler.php";
        handleUserRequest($method, $operation, $data);
        break;
    case 'setup':
        include_once __DIR__ . "/handle/setup_handler.php";
        handleConfigRequest($method, $operation, $data);
        break;
    case 'mail':
        include_once __DIR__ . "/handle/mail_handler.php";
        handleMailRequest($method, $operation, $data);
        break;
    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Recurso não encontrado']);
        break;
}
