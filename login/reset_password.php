<?php
require_once __DIR__ . '/../autoload.php';


$token = $_GET['token'] ?? '';
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $token = $_POST['token'] ?? '';

    if ($password && $confirm_password && $password === $confirm_password) {
        // Verifica se o token é válido
        $query = 'SELECT email, token_expires_at FROM users WHERE reset_token = ?';
        $stmt = $dbConect->prepare($query);
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email, $token_expires_at);
            $stmt->fetch();
            $user = mysql_select_in_array("users", "email='$email'");
            $userName = explode(" ", $user["firstName"])[0];

            if (strtotime($token_expires_at) >= time()) {
                // Atualiza a senha e invalida o token
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_query = 'UPDATE users SET password = ?, reset_token = NULL, token_expires_at = NULL WHERE email = ?';
                $update_stmt = $dbConect->prepare($update_query);
                $update_stmt->bind_param('ss', $hashed_password, $email);
                if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                    $request_ip = trim($ips[0]); // Pega o primeiro IP da lista
                } else {
                    $request_ip = $_SERVER['REMOTE_ADDR'];
                }

                if ($update_stmt->execute()) {                    
                    $success_message = 'Senha alterada com sucesso! Você pode voltar à página inicial.';
                    $modalBody = "
                                    <!DOCTYPE html>
                                    <head>
                                        <meta charset='UTF-8'>
                                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                                        <title>Senha Alterada com Sucesso!</title>
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
                                                <img src='{$site_url}{$app_logo_url}' alt='{$page_title}'>
                                            </div>
                                            <div class='content'>
                                                <p>Olá $userName,</p>
                                                <p>Sua senha na <strong>{$page_title}</strong> foi alterada com sucesso em <strong>{$change_time}</strong>.</p>
                                                <p>Se você realizou essa alteração, nenhuma ação adicional é necessária. Caso não tenha sido você, entre em contato conosco imediatamente.</p>
                                                <p style='text-align: center;'>
                                                    <a href='{$login_link}' class='button'>Acessar Minha Conta</a>
                                                </p>
                                                <p>IP de origem da alteração: <strong>{$request_ip}</strong></p>
                                            </div>
                                            <div class='footer'>
                                                <p>Atenciosamente,<br>Equipe {$page_title}</p>
                                                <p><a href='{$site_url}'>Visite nosso site</a></p>
                                            </div>
                                        </div>
                                    </body>
                                    </html>
                                    ";
                    sendMail("Senha Alterada com Sucesso!", $email, $modalBody);
                } else {
                    $error_message = 'Erro ao atualizar a senha. Por favor, tente novamente.';
                }

                $update_stmt->close();
            } else {
                $error_message = 'O token expirou. Solicite uma nova recuperação de senha.';
            }
        } else {
            $error_message = 'Token inválido. Solicite uma nova recuperação de senha.';
        }

        $stmt->close();
    } else {
        $error_message = 'As senhas não coincidem ou estão inválidas.';
    }
} elseif (!$token) {
    $error_message = 'Token não fornecido.';
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Redefinir Senha - <?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= $app_version ?>">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Redefinir Senha</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                        <?php elseif ($success_message): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                            <div class="d-grid mt-3">
                                <a href="./" class="btn btn-primary">Voltar para Página Inicial</a>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirme a Nova Senha</label>
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" required>
                                </div>
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Redefinir Senha</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <span class="version"><?= $app_version ?></span>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>