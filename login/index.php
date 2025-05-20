<?php

use GuzzleHttp\Cookie\SetCookie;

require_once __DIR__ . '/../autoload.php';


// Verifica se há um cookie de autenticação salvo
if (isset($_COOKIE['remember_user'])) {
    $cookieData = json_decode($_COOKIE['remember_user'], true);
    $username = $cookieData['username'];
    $password = $cookieData['password'];

    // Verifica as credenciais do cookie
    $result = mysql_select_in_array('users', "email='$username'");
    if ($result && password_verify($password, $result["password"])) {
        $_SESSION['idUser'] = $result["id"];
        $_SESSION['firstName'] = $result["firstName"];
        $_SESSION['lastName'] = $result["lastName"];
        $_SESSION['email'] = $result["email"];
        $_SESSION['password'] = $result["password"];

        header("Location: ../");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $saveSession = isset($_POST['saveSession']);

    if ($username && $password) {
        if (!isset($error_message)) {
            // Verifica as credenciais do usuário
            $result = mysql_select_in_array('users', "email='$username'");
            if ($result && password_verify($password, $result["password"])) {
                $_SESSION['idUser'] = $result["id"];
                $_SESSION['firstName'] = explode(" ", $result["firstName"])[0];
                $_SESSION['password'] = $result["password"];
                $_SESSION['login_attempts'] = 0; // Reseta as tentativas de login

                // Salva o login em um cookie se o usuário selecionou "Manter conectado"
                if ($saveSession) {
                    $cookieData = json_encode([
                        'username' => $username,
                        'password' => $password
                    ]);
                    setcookie('remember_user', $cookieData, time() + (86400 * 30), "/"); // Cookie válido por 30 dias
                }

                header("Location: ../");
                exit;
            } else {
                // Incrementa o contador de tentativas de login
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $error_message = 'Usuário ou senha incorretos! Por favor, tente novamente.';
            }
        }
    } else {
        $error_message = 'Por favor, preencha todos os campos.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - <?= $page_title ?></title>
    <meta name="description"
        content="Faça login ou se registre para continuar!">
    <link rel="shortcut icon" href="<?= $app_logo_url ?>" type="image/x-png">
    <link rel="stylesheet" href="<?= $site_url ?>assets/framework/bootstrap-5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css?v=<?= $app_version ?>">

    <link rel="manifest" href="<?= $site_url ?>pwa/manifest.php">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= $app_name ?>">

    <script src="<?= $site_url ?>pwa/service-worker.js"></script>
</head>

<body>
    <div class="container">
        <div id="loginForm" class="row justify-content-center">
            <div class="page-content">
                <div class="card">
                    <div class="card-header text-center d-flex justify-content-around">
                        <h3 id="pageLogin" class="active">Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= htmlspecialchars($error_message) ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="card-inputs">
                                <div class="form-group mb-3">
                                    <label for="username" class="form-label">Usuário</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Senha</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="form-group mb-3 d-flex justify-content-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="saveSession"
                                            name="saveSession">
                                        <label class="form-check-label" for="saveSession">Manter conectado</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Entrar</button>
                            </div>
                        </form>
                        <div class="mt-3 text-center">
                            <?php if (isset($register_enable) && $register_enable) { ?>
                                <a href="register.php<?= isset($_GET["invite"]) ? "?invite=$_GET[invite]" : "" ?>"
                                    class="btn btn-link">Criar Conta</a>
                            <?php } ?>
                            <a href="forgot_password.php" class="btn btn-link">Esqueci minha senha</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <span class="version"><?= $app_version ?></span>
    </footer>
    <script src="<?= $site_url ?>assets/framework/bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>
</body>

</html>