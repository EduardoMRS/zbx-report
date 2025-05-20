<?php
require_once(__DIR__ . "/autoload.php");
include_once(__DIR__ . "/page-routes.php");
$page_title = "Assistente de NOC";
$hasInclude = false;

if (isset($_GET["page"]) && $_GET["page"] != "" && $_GET["page"] != "home" && !isset($_GET["admin"])) {

    switch ($_GET["page"]) {
        case 'hosts':
            $page_title = "Listagem de Hosts";
            $hasInclude = __DIR__ . "/pages/hosts.php";
            break;
        case 'config':
            if (!$userData && $userData["access_level"] != "admin") {
                echo "<div class='container'><h1>Ooops! Pagina não encontrada!</h1></div>";
                $hasInclude = "error";
                exit;
            }
            $page_title = "Configurações";
            $hasInclude = __DIR__ . "/pages/config.php";
            break;
        case 'dedicated':
            $page_title = "Clientes Dedicado";
            $hasInclude = __DIR__ . "/pages/dedicated.php";
            break;
        case 'users':
            if (!$userData && $userData["access_level"] != "admin") {
                echo "<div class='container'><h1>Ooops! Pagina não encontrada!</h1></div>";
                $hasInclude = "error";
                exit;
            }
            $page_title = "Usuarios";
            $hasInclude = __DIR__ . "/pages/users.php";
            break;
        case 'mail-history':
            $page_title = "E-Mails enviados";
            $hasInclude = __DIR__ . "/pages/mail.php";
            break;
        default:
            echo "<div class='container'><h1>Ooops! Pagina não encontrada!</h1></div>";
            $hasInclude = "error";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <html lang="pt-BR" <?php echo isset($userPreference["theme"]) ? 'data-theme="' . $userPreference["theme"] . '"' : "" ?>>
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="<?= $site_url ?>assets/css/style.css">
    <meta name="robots" content="noindex">
    <script src="<?= $site_url ?>assets/js/jquery-3.6.0.min.js"></script>
    <script src="<?= $site_url ?>assets/js/jquery.mask.min.js"></script>
    <script src="<?= $site_url ?>assets/js/script.js"></script>
    <link rel="shortcut icon" href="<?= $app_logo_url ?>" type="image/x-png">
</head>

<body>
    <?php
    include_once(__DIR__ . "/pages/nav.php");
    ?>
    <div class="nav-overlay top fixed">
        <div class="nav">
            <div>
                <button class="nav-item menu-toggle" onclick="window.location.href='<?= $site_url ?>'">
                    <i class="bi bi-house-door-fill"></i>
                </button>
            </div>
            <div>
                <button id="menuToggle" class="nav-item menu-toggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </div>

    <?php
    if ($hasInclude) {
        if ($hasInclude == "error") {
            exit;
        }
        include_once($hasInclude);
    } else {
        include_once __DIR__ . "/pages/home.php";
    } ?>
    <script src="<?= $site_url ?>assets/framework/bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $site_url ?>assets/js/popper.min.js"></script>
    <?php
    if (isset($userData["access_level"]) && $userData["access_level"] != "admin") {
        echo "<script>troll();</script>";
    }
    ?>
</body>

</html>