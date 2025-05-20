<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function handleConfigRequest($method, $operation, $data)
{
    try {
        switch ($operation) {
            case 'get':
                handleConfigGet($method, $data);
                break;
            case 'save':
                handleConfigSave($method, $data);
                break;
            case 'test-db':
                handleTestDbConnection($method, $data);
                break;
            case 'test-smtp':
                handleTestSmtpConnection($method, $data);
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


function handleConfigGet($method)
{
    if ($method != "GET") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    $config = file_get_contents(__DIR__ . "/../../config.json") ?? null;

    if (!$config) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Dados de configuração não encontrados!']);
        return;
    }

    echo json_encode([
        "status" => "success",
        "data" => json_decode($config)
    ]);
}

function handleConfigSave($method, $dataReceiver)
{
    if ($method != "POST") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    $config_file_path = __DIR__ . "/../../config.json";
    $config_default = file_get_contents($config_file_path) ?? null;

    if (!$config_default) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Dados de configuração não encontrados!']);
        return;
    }

    if (!$dataReceiver) {
        http_response_code(400); // 400 Bad Request é mais apropriado aqui
        echo json_encode(['status' => 'error', 'message' => 'Dados para salvamento não fornecidos!']);
        return;
    }

    // Decodifica para array associativo
    $config_default = json_decode($config_default, true);

    if ($config_default === null) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro ao decodificar configuração existente!']);
        return;
    }

    // Mescla os arrays
    $config_save = array_merge($config_default, $dataReceiver);

    // Codifica de volta para JSON formatado
    $json_data = json_encode($config_save, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (file_put_contents($config_file_path, $json_data) !== false) {
        echo json_encode([
            "status" => "success",
            "data" => "Dados atualizados com sucesso!"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro ao realizar o salvamento!']);
    }
}


function handleTestDbConnection($method, $data)
{
    if ($method != "TEST") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    if (empty($data['database'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dados do banco não fornecidos!']);
        return;
    }

    $dbConfig = $data['database'];
    
    try {
        // Testar conexão MySQL
        $dsn = "mysql:host={$dbConfig['dbHost']};dbname={$dbConfig['dbName']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        new PDO(
            $dsn,
            $dbConfig['dbUserName'],
            $dbConfig['dbUserPassword'],
            $options
        );
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Conexão com o banco de dados estabelecida com sucesso!'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Falha na conexão com o banco de dados',
            'error' => $e->getMessage()
        ]);
    }
}

function handleTestSmtpConnection($method, $data)
{
    if ($method != "TEST") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido!']);
        return;
    }

    if (empty($data['smtp'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dados SMTP não fornecidos!']);
        return;
    }

    $smtpConfig = $data['smtp'];
    
    try {
        // Usando PHPMailer (recomendado instalar via composer)
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = $smtpConfig['mailHost'];
        $mail->Port = $smtpConfig['mailPort'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtpConfig['mailUsername'];
        $mail->Password = $smtpConfig['mailUserPassword'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //ENCRYPTION_STARTTLS Ou ENCRYPTION_SMTPS
        
        // Teste básico de conexão
        $mail->smtpConnect();
        $mail->smtpClose();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Conexão SMTP estabelecida com sucesso!'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Falha na conexão SMTP',
            'error' => $e->getMessage()
        ]);
    }
}