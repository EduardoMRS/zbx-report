<?php
include_once __DIR__ . '/../autoload.php';
if (isset($register_enable) && $register_enable) {
    header("Location: ./");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $birth = $_POST['birth'] ?? '';
    $document = $_POST['document'] ?? '';

    if ($firstName && $lastName && $phone && $document && $password && $email) {
        // Verificar conexão com o banco
        if (!$dbConect) {
            die("Erro na conexão com o banco de dados: " . $dbConect->connect_error);
        }

        // Verificar requisitos da senha
        if (strlen($password) < 8 || strlen($password) > 20) {
            $error_message = "A senha deve ter entre 8 e 20 caracteres.";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error_message = "A senha deve conter pelo menos uma letra maiúscula.";
        } elseif (!preg_match('/[a-z]/', $password)) {
            $error_message = "A senha deve conter pelo menos uma letra minúscula.";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error_message = "A senha deve conter pelo menos um número.";
        } elseif (!preg_match('/[\W]/', $password)) {
            $error_message = "A senha deve conter pelo menos um caractere especial.";
        } else {
            // Hash da senha
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            if (mysql_select_in_array("users", "email='$email'")) {
                $error_message = "E-mail já cadastrado.";
            } else {
                if (mysql_select_in_array("users", "document='$document'")) {
                    $error_message = "CPF já cadastrado.";
                } else {
                    if (mysql_select_in_array("users", "phone='$phone'")) {
                        $error_message = "Numero de Telefone já cadastrado.";
                    } else {
                        $userInsert = [
                            "firstName" => $firstName,
                            "lastName" => $lastName,
                            "document" => $document,
                            "phone" => $phone,
                            "email" => $email,
                            "birth" => $birth,
                            "password" => $hashed_password
                        ];

                        $newUser = mysql_insert("users", $userInsert);

                        if ($newUser) {
                            $mailBody = "
                                        <!DOCTYPE html>
                                        <head>
                                        <meta charset='UTF-8'>
                                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                                        <title>Cadastro Realizado com Sucesso</title>
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
                                                <p>Olá {$userName},</p>
                                                <p>Seu cadastro na <strong>{$page_title}</strong> foi realizado com sucesso! Estamos muito felizes em tê-lo conosco.</p>
                                                <p>Agora você pode acessar nossa plataforma e aproveitar todos os recursos disponíveis.</p>               
                                                <p>Se você tiver alguma dúvida ou precisar de ajuda, não hesite em entrar em contato conosco.</p>
                                            </div>
                                            <div class='footer'>
                                                <p>Atenciosamente,<br>Equipe {$page_title}</p>
                                                <p><a href='{$site_url}'>Visite nosso site</a></p>
                                            </div>
                                        </div>
                                        </body>
                                        </html>
                                        ";
                            sendMail("Cadastro Realizado com Sucesso", $email, $mailBody);
                            $result = mysql_select_in_array('users', "id='$newUser'");
                            if ($result) {
                                $_SESSION['idUser'] = $result["id"];
                                $_SESSION['firstName'] = explode(" ", $result["firstName"])[0];
                                $_SESSION['password'] = $result["password"];
                            }
                            header("Location: " . $_SERVER['PHP_SELF'] . "?success");
                            exit;
                        } else {
                            $error_message = "Erro ao registrar usuário: " . $stmt->error;
                        }
                    }
                }
            }
        }
    } else {
        $error_message = "Por favor, preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registro - <?= $page_title ?></title>
    <link rel="stylesheet" href="<?= $site_url ?>assets/framework/bootstrap-5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css?v=<?= $app_version ?>">
    <script src="<?= $site_url ?>assets/js/jquery-3.6.0.min.js"></script>
    <script src="<?= $site_url ?>assets/js/jquery.mask.min.js"></script>
    <style>
        .password-requirements {
            margin-top: 5px;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
        }

        .requirement i {
            margin-right: 5px;
            font-size: 0.8rem;
        }

        .valid {
            color: #28a745;
        }

        .invalid {
            color: #dc3545;
        }

        .password-strength {
            height: 5px;
            margin-top: 5px;
            background-color: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }

        .weak {
            background-color: #dc3545;
            width: 25%;
        }

        .medium {
            background-color: #ffc107;
            width: 50%;
        }

        .strong {
            background-color: #28a745;
            width: 75%;
        }

        .very-strong {
            background-color: #007bff;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="page-content">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 id="pageRegister" class="active">Registro</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">
                                Usuário registrado com sucesso! <br>
                                <a href="./"
                                    class="btn btn-success mt-4 d-flex justify-content-center">Fazer Login</a>
                            </div>
                        <?php elseif (isset($error_message)): ?>
                            <div class="alert alert-danger"> <?= htmlspecialchars($error_message) ?> </div>
                        <?php endif;
                        if (!isset($_GET['success'])):
                        ?>
                            <form method="POST" action="" id="registerForm">
                                <div class="card-inputs">
                                    <div class="row mb-3 gap-3">
                                        <div>
                                            <label for="firstName" class="form-label">Nome</label>
                                            <input type="text" class="form-control"
                                                value="<?= isset($_POST["firstName"]) ? $_POST["firstName"] : "" ?>"
                                                id="firstName" name="firstName" placeholder="Ex: João" required>
                                        </div>
                                        <div>
                                            <label for="lastName" class="form-label">Sobrenome</label>
                                            <input type="text" class="form-control"
                                                value="<?= isset($_POST["lastName"]) ? $_POST["lastName"] : "" ?>"
                                                id="lastName" name="lastName" placeholder="da Silva" required>
                                        </div>
                                    </div>
                                    <div class="row justify-content-between mb-3 gap-2">
                                        <div class="col-md-7">
                                            <label for="email" class="form-label">E-mail</label>
                                            <input type="email" class="form-control"
                                                value="<?= isset($_POST["email"]) ? $_POST["email"] : "" ?>" id="email"
                                                name="email" placeholder="Ex: meu.email@outlook.com" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="phone" class="form-label">Telefone</label>
                                            <input type="text" class="form-control"
                                                value="<?= isset($_POST["phone"]) ? $_POST["phone"] : "" ?>" id="phone"
                                                name="phone" placeholder="Ex: (00)00000-0000" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3 gap-3">
                                        <div class="col-md-auto">
                                            <label for="birth" class="form-label">Data de nascimento</label>
                                            <input type="date" class="form-control"
                                                value="<?= isset($_POST["birth"]) ? $_POST["birth"] : "" ?>" id="birth"
                                                name="birth" required>
                                        </div>
                                        <div class="col-md-auto">
                                            <label for="document" class="form-label">CPF</label>
                                            <input type="text" inputmode="numeric" class="form-control"
                                                value="<?= isset($_POST["document"]) ? $_POST["document"] : "" ?>" id="document"
                                                name="document" placeholder="Ex: 123.456.789-00" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Senha</label>
                                        <input type="password" class="form-control"
                                            value="<?= isset($_POST["password"]) ? $_POST["password"] : "" ?>" id="password"
                                            name="password" placeholder="**********" required maxlength="20">
                                        <div class="password-requirements">
                                            <div class="requirement">
                                                <i id="length-icon" class="fas fa-circle invalid"></i>
                                                <span id="length-text">8-20 caracteres</span>
                                            </div>
                                            <div class="requirement">
                                                <i id="uppercase-icon" class="fas fa-circle invalid"></i>
                                                <span id="uppercase-text">Pelo menos uma letra maiúscula</span>
                                            </div>
                                            <div class="requirement">
                                                <i id="lowercase-icon" class="fas fa-circle invalid"></i>
                                                <span id="lowercase-text">Pelo menos uma letra minúscula</span>
                                            </div>
                                            <div class="requirement">
                                                <i id="number-icon" class="fas fa-circle invalid"></i>
                                                <span id="number-text">Pelo menos um número</span>
                                            </div>
                                            <div class="requirement">
                                                <i id="special-icon" class="fas fa-circle invalid"></i>
                                                <span id="special-text">Pelo menos um caractere especial</span>
                                            </div>
                                        </div>
                                        <div class="password-strength">
                                            <div id="strength-bar" class="strength-bar"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary" id="submit-button" disabled>Registrar</button>
                                </div>
                            </form>

                            <div class="mt-3 text-center">
                                <a href="./" class="btn btn-link">Fazer Login</a>
                            </div>
                        <?php endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <span class="version"><?= $app_version ?></span>
    </footer>

    <script>
        $(document).ready(function() {
            $('#document').mask('000.000.000-00');
            $('#phone').mask('(00)00000-0000');

            // Validação da senha em tempo real
            $('#password').on('input', function() {
                const password = $(this).val();
                let isValid = true;

                // Verificar comprimento
                if (password.length >= 8 && password.length <= 20) {
                    $('#length-icon').removeClass('invalid').addClass('valid');
                    $('#length-text').css('color', '#28a745');
                } else {
                    $('#length-icon').removeClass('valid').addClass('invalid');
                    $('#length-text').css('color', '#dc3545');
                    isValid = false;
                }

                // Verificar letra maiúscula
                if (/[A-Z]/.test(password)) {
                    $('#uppercase-icon').removeClass('invalid').addClass('valid');
                    $('#uppercase-text').css('color', '#28a745');
                } else {
                    $('#uppercase-icon').removeClass('valid').addClass('invalid');
                    $('#uppercase-text').css('color', '#dc3545');
                    isValid = false;
                }

                // Verificar letra minúscula
                if (/[a-z]/.test(password)) {
                    $('#lowercase-icon').removeClass('invalid').addClass('valid');
                    $('#lowercase-text').css('color', '#28a745');
                } else {
                    $('#lowercase-icon').removeClass('valid').addClass('invalid');
                    $('#lowercase-text').css('color', '#dc3545');
                    isValid = false;
                }

                // Verificar número
                if (/[0-9]/.test(password)) {
                    $('#number-icon').removeClass('invalid').addClass('valid');
                    $('#number-text').css('color', '#28a745');
                } else {
                    $('#number-icon').removeClass('valid').addClass('invalid');
                    $('#number-text').css('color', '#dc3545');
                    isValid = false;
                }

                // Verificar caractere especial
                if (/[\W_]/.test(password)) {
                    $('#special-icon').removeClass('invalid').addClass('valid');
                    $('#special-text').css('color', '#28a745');
                } else {
                    $('#special-icon').removeClass('valid').addClass('invalid');
                    $('#special-text').css('color', '#dc3545');
                    isValid = false;
                }

                // Atualizar força da senha
                updatePasswordStrength(password);

                // Habilitar/desabilitar botão de envio
                $('#submit-button').prop('disabled', !isValid);
            });

            function updatePasswordStrength(password) {
                let strength = 0;

                // Comprimento
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;

                // Tipos de caracteres
                if (/[A-Z]/.test(password)) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[\W_]/.test(password)) strength++;

                // Atualizar barra de força
                const $strengthBar = $('#strength-bar');
                $strengthBar.removeClass('weak medium strong very-strong');

                if (strength <= 3) {
                    $strengthBar.addClass('weak');
                } else if (strength <= 5) {
                    $strengthBar.addClass('medium');
                } else if (strength <= 7) {
                    $strengthBar.addClass('strong');
                } else {
                    $strengthBar.addClass('very-strong');
                }
            }
        });
    </script>
    <script src="<?= $site_url ?>/assets/framework/bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>
</body>

</html>