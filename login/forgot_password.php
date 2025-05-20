<?php
include __DIR__ . "/../autoload.php";




$saveSession = isset($_POST['saveSession']);
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
$_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0;
$attemptsLimit = 3; //Numero de tentativas


// Se não houver erro no reCAPTCHA ou se não for necessário validá-lo ainda
if (!isset($error_message)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $email = $_POST['email'] ?? '';
        if ($email) {
            $user_exists = mysql_select_one_value('users', 'email', 'email', $email);
            if ($user_exists) {
                $token = bin2hex(random_bytes(16)); // Gera token único
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $request_ip = $_SERVER['REMOTE_ADDR'];
                $request_time = date('d/m/Y H:i:s');

                // Salvar token no banco
                $query = 'UPDATE users SET reset_token = ?, token_expires_at = ? WHERE email = ?';
                $stmt = $dbConect->prepare($query);
                if ($stmt) {
                    $stmt->bind_param('sss', $token, $expires, $email);
                    if ($stmt->execute()) {
                        $success_message = 'Um link para recuperação de senha foi enviado! Verifique a caixa de span e lixo de eletronico.';

                        $reset_link = $site_url . "login/reset_password.php?token=$token";

                        $user = mysql_select_in_array("users", "email='$email'");
                        $userName = explode(" ", $user["firstName"])[0];
                        // Captura o IP de origem, considerando o proxy reverso
                        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                            $request_ip = trim($ips[0]); // Pega o primeiro IP da lista
                        } else {
                            $request_ip = $_SERVER['REMOTE_ADDR'];
                        }

                        $mailBody = "
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
                                                <img src='{$site_url}{$app_logo_url}' alt='Logo {$page_title}'>
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
                                                <p>IP de origem: <strong>{$request_ip}</strong></p>
                                            </div>
                                            <div class='footer'>
                                                <p>Atenciosamente,<br>Equipe {$page_title}</p>
                                                <p><a href='{$site_url}'>Visite nosso site</a></p>
                                            </div>
                                        </div>
                                    </body>
                                    </html>
                                ";
                        sendMail("Recuperação de Senha", $email, $mailBody);
                    } else {
                        $error_message = "Erro ao salvar o token no banco de dados.";
                    }
                    $stmt->close();
                } else {
                    $error_message = "Erro na preparação da consulta.";
                }
            } else {
                $error_message = "E-mail não encontrado.";
            }
        } else {
            $error_message = "Por favor, insira seu e-mail.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Recuperação de Senha - <?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= $app_version ?>">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Recuperação de Senha</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"> <?= htmlspecialchars($error_message) ?> </div>
                        <?php elseif (isset($success_message)): ?>
                            <div class="alert alert-success"> <?= htmlspecialchars($success_message) ?> </div>
                        <?php endif; ?>
                        <?php
                        if (!isset($_SESSION['login_attempts']) || $_SESSION['login_attempts'] < $attemptsLimit) {
                        ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Enviar</button>
                                </div>
                            </form>
                        <?php
                        }
                        ?>
                        <div class="mt-3 text-center">
                            <a href="./" class="btn btn-link">Fazer Login</a>
                        </div>
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