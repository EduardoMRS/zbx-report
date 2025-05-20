<?php

function handleUserRequest($method, $operation, $data)
{
    try {
        switch ($operation) {
            case 'get':
                return handleUserGet($method, $data);
            case 'new':
                return handleUserAdd($method, $data);
            case 'update':
                return handleUserUpdate($method, $data);
            case 'update-access':
                return handleUserUpdateAccess($method, $data);
            case 'send-recovery':
                return handleUserSendRecovery($method, $data);
            case 'delete':
                return handleUserDelete($method, $data);
            case 'search':
                return handleUserSearch($method, $data);
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

// Função para obter dados de um usuário específico
function handleUserGet($method)
{
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
        return;
    }

    $userId = $data['id'] ?? $_GET['id'] ?? null;
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID do usuário não fornecido']);
        return;
    }

    $user = mysql_select_in_array("users", "id='$userId' and email!='root'");
    if (!$user) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado']);
        return;
    }

    echo json_encode(['status' => 'success', 'data' => $user]);
}

// Função para adicionar um usuário
function handleUserAdd($method, $data)
{
    if ($method !== 'PUT') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
        return;
    }

    if (!$data['firstName'] || !$data['lastName'] || !$data['email'] || !$data['access_level']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dados para cadastro incompletos!']);
        return;
    }

    $updateData = [
        'firstName' => $data['firstName'] ?? '',
        'lastName' => $data['lastName'] ?? '',
        'document' => $data['document'] ?? '',
        'phone' => $data['phone'] ?? '',
        'email' => $data['email'] ?? '',
        'password' => $data['password'] ?? "10203040",
        'sex' => $data['sex'] ?? '',
        'birth' => $data['birth'] ?? '',
        'access_level' => $data['access_level'] ?? '',
    ];

    if (mysql_insert('users', $updateData)) {
        echo json_encode(['status' => 'success', 'message' => 'Usuario cadastrado com sucesso!']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar dados']);
    }
}


// Função para atualizar dados do usuário
function handleUserUpdate($method, $data)
{
    if ($method !== 'PUT') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
        return;
    }

    $userId = $data['idUser'] ?? null;
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID do usuário não fornecido']);
        return;
    }
    if (!$data['firstName'] || !$data['lastName'] || !$data['email'] || !$data['access_level']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dados para atualização incompletos!']);
        return;
    }

    $updateData = [
        'firstName' => $data['firstName'] ?? '',
        'lastName' => $data['lastName'] ?? '',
        'document' => $data['document'] ?? '',
        'phone' => $data['phone'] ?? '',
        'email' => $data['email'] ?? '',
        'sex' => $data['sex'] ?? '',
        'birth' => $data['birth'] ?? '',
        'access_level' => $data['access_level'] ?? '',
    ];

    if (mysql_update('users', $updateData, 'id', $userId)) {
        echo json_encode(['status' => 'success', 'message' => 'Dados atualizados com sucesso!']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar dados']);
    }
}

// Função para atualizar nível de acesso
function handleUserUpdateAccess($method, $data)
{
    if ($method !== 'PUT') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
        return;
    }

    $userId = $data['userId'] ?? null;
    $accessLevel = $data['accessLevel'] ?? null;
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID do usuário não fornecido', 'data' => $data]);
        return;
    }
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Nivel de acesso não fornecido']);
        return;
    }

    if (!$userId || !$accessLevel) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dados incompletos']);
        return;
    }

    if (mysql_update("users", ['access_level' => $accessLevel], "id", $userId)) {
        echo json_encode(["status" => "success", "message" => "Permissão alterada com sucesso!"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erro ao efetuar a alteração!"]);
    }
}

// Função para enviar código de recuperação
function handleUserSendRecovery($method, $data)
{
    if ($method !== 'PUT') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
        return;
    }
    $userId = $data['userId'] ?? null;
    if (!$userId) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'ID do usuário não fornecido']);
        return;
    }

    $user = mysql_select_in_array('users', "id='$userId'");
    if (!$user) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado']);
        return;
    }

    global $app_short_name, $site_url, $app_logo_url;

    $token = bin2hex(random_bytes(16)); // Gera token único
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $request_time = date('d/m/Y H:i:s');
    $email = $user['email'];
    if (mysql_update("users", ['reset_token' => $token, 'token_expires_at' => $expires], "id", "$user[id]")) {
        $reset_link = "{$site_url}login/reset_password.php?token=$token";
        $userName = explode(" ", $user["firstName"])[0];

        try {
            $body = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recuperação de Senha</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #1a1a1a;
                    color: #e0e0e0;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    padding: 20px;
                    background-color: #2a2a2a;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
                }
                .logo {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .logo img {
                    width: 100px;
                    height: auto;
                    border-radius: 8px;
                }
                .content {
                    line-height: 1.6;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #007bff;
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 5px;
                    text-align: center;
                    margin: 20px 0;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 0.9em;
                    color: #a0a0a0;
                    text-align: center;
                }
                .footer a {
                    color: #007bff;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'>
                    <img src='{$app_logo_url}' alt='Logo {$app_short_name}'>
                </div>
                <div class='content'>
                    <p>Olá $userName,</p>
                    <p>Recebemos uma solicitação para redefinir sua senha. Caso não tenha feito esta solicitação, ignore este e-mail.</p>
                    <p>Para redefinir sua senha, clique no botão abaixo ou copie e cole o link em seu navegador:</p>
                    <p style='text-align: center;'>
                        <a href='{$reset_link}' class='button'>Redefinir Senha</a>
                    </p>
                    <p>Ou acesse: <a href='{$reset_link}'>{$reset_link}</a></p>
                    <p>Solicitação realizada em: <strong>{$request_time}</strong></p>
                </div>
                <div class='footer'>
                    <p>Atenciosamente,<br>Equipe {$app_short_name}</p>
                    <p><a href='{$site_url}'>Visite nosso site</a></p>
                </div>
            </div>
        </body>
        </html>
    ";

            if (sendMail("Recuperação de Senha", $email, $body, $app_short_name)) {
                echo json_encode(["status" => "success", "message" => "Um link de recuperação foi enviado ao e-mail de $userName!"]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Erro ao enviar o e-mail de recuperação!"]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Erro ao enviar o e-mail: {$e->getMessage()}"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erro ao gerar token de recuperação"]);
    }
}

// Função para deletar usuário
function handleUserDelete($method, $data)
{
    if ($method !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
        return;
    }

    $userId = $data['id'] ?? null;
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID do usuário não fornecido']);
        return;
    }

    // Remover foto de perfil se existir
    $profilePhoto = __DIR__ . "/../user-data/img-profile/$userId.webp";
    if (file_exists($profilePhoto)) {
        unlink($profilePhoto);
    }

    if (mysql_delete("users", "id", $userId)) {
        echo json_encode(["status" => "success", "message" => "Usuário deletado com sucesso!"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erro ao deletar usuário!"]);
    }
}


// Função para buscar usuários
function handleUserSearch($method, $data)
{
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
        return;
    }


    // Verifica se há parâmetros de ordenação na URL
    $sortColumn = $data['sort'] ?? 'firstName'; // Coluna padrão para ordenação
    $sortOrder = $data['order'] ?? 'asc';  // Ordem padrão (ascendente)

    // Obtém e filtra a lista de exercícios
    $userList = mysql_select_all_in_array("users", "email!='root'", "id,firstName, lastName, email, document, phone, sex, last_access, creationDate,access_level", null, "firstName") ?? null;
    if (!$userList) {
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
        $userList = array_values(array_filter($userList, function ($user) use ($search) {
            // Normalizações iniciais
            $normalizedSearch = normalizeString($search);
            $numericSearch = cleanNumber($search);

            // Preparar campos do usuário
            $firstName = normalizeString($user['firstName'] ? $user['firstName'] : '');
            $lastName = normalizeString($user['lastName'] ? $user['lastName'] : '');
            $fullName = $firstName . ' ' . $lastName;
            $email = normalizeString($user['email'] ?? '');
            $document = cleanNumber($user['document'] ?? '');
            $phone = cleanNumber($user['phone'] ?? '');

            // Verificar se a busca parece ser numérica
            $isNumericSearch = preg_match('/[0-9]/', $search) && (strlen($numericSearch) >= 3);

            // Busca em campos textuais (sempre verifica)
            $textMatch = stripos($fullName, $normalizedSearch) !== false ||
                stripos($email, $normalizedSearch) !== false;

            // Busca em campos numéricos (apenas se a busca contém números)
            $numberMatch = $isNumericSearch && (
                strpos($document, $numericSearch) !== false ||
                strpos($phone, $numericSearch) !== false);

            return $textMatch || $numberMatch;
        }));
    }

    // Ordena os exercícios
    $userList = sortArray($userList, $sortColumn, $sortOrder);

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'content' => $userList,
        'count' => count($userList) // Adiciona contagem para debug
    ]);
}
