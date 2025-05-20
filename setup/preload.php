<?php
// Verificar se o composer está instalado
function isComposerAvailable()
{
    return shell_exec('which composer') !== null;
}

// Função para verificar/instalar pacotes
function ensurePackagesInstalled()
{
    $requiredPackages = [
        'phpmailer/phpmailer' => 'PHPMailer (para envio de e-mails)',
        'phpoffice/phpspreadsheet' => 'PHPOffice/PHPSpreadsheet (para manipulação de planilhas)',
        'setasign/fpdf' => 'setasign/fpdf (para geração básica de PDFs)',
        'setasign/fpdi' => 'setasign/fpdi (para mesclar o timbrado como background)',
        'jpgraph/jpgraph' => 'jpgraph/jpgraph (para geração de gráficos)',
        'setasign/fpdi-fpdf' => 'setasign/fpdi-fpdf (para geração de gráficos e pdfs)',
        'tecnickcom/tcpdf' => 'tecnickcom/tcpdf (para adição de assinaturas digitais)',
    ];

    $missingPackages = [];
    $installCommand = 'composer require ';

    // Verificar se o composer.lock existe
    $composerLockPath = __DIR__ . '/composer.lock';

    if (file_exists($composerLockPath)) {
        $composerLock = json_decode(file_get_contents($composerLockPath), true);

        // Verificar pacotes instalados
        foreach ($requiredPackages as $package => $description) {
            $found = false;
            foreach ($composerLock['packages'] as $installed) {
                if ($installed['name'] === $package) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingPackages[$package] = $description;
                $installCommand .= $package . ' ';
            }
        }
    } else {
        // Se não existir composer.lock, assumir que todos estão faltando
        foreach ($requiredPackages as $package => $description) {
            $missingPackages[$package] = $description;
            $installCommand .= $package . ' ';
        }
    }

    // Se houver pacotes faltantes e composer estiver disponível, tentar instalar
    if (!empty($missingPackages) && isComposerAvailable()) {
        $output = shell_exec($installCommand . '--no-interaction --quiet 2>&1');

        // Verificar se a instalação foi bem-sucedida
        if (file_exists($composerLockPath)) {
            $composerLock = json_decode(file_get_contents($composerLockPath), true);
            foreach (array_keys($missingPackages) as $package) {
                $found = false;
                foreach ($composerLock['packages'] as $installed) {
                    if ($installed['name'] === $package) {
                        $found = true;
                        unset($missingPackages[$package]);
                        break;
                    }
                }
            }
        }
    }

    return $missingPackages;
}

// Verifica e instalar pacotes necessários
$missingPackages = ensurePackagesInstalled();

require_once(__DIR__ . "/../autoload.php");
$config_path = __DIR__ . "/../config.json";

function getConfigData()
{
    global $config_path;
    if (!file_exists($config_path)) {
        return [];
    }
    $config_content = file_get_contents($config_path);
    return json_decode($config_content, true) ?? [];
}

function init_table_users()
{
    return "CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `firstName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) DEFAULT NULL,
  `document` varchar(45) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `email` varchar(250) NOT NULL,
  `password` varchar(256) NOT NULL,
  `sex` varchar(45) DEFAULT NULL,
  `birth` date DEFAULT NULL,
  `access_level` enum('basic','view','admin') DEFAULT 'basic',
  `active` int DEFAULT '1',
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expires_at` varchar(255) DEFAULT NULL,
  `creationDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_access` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8mb3;

";
}

function init_root_user()
{
    global $config_path;
    if (!mysql_select_in_array("users", "email='root'")) {
        $config_data = getConfigData();
        $random_pass = uniqid("root_");

        // Salva senha do usuario root no arquivo de configuração
        $config_data['root_pass'] = $random_pass;
        $json_data = json_encode($config_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        file_put_contents($config_path, $json_data);

        if (mysql_insert("users", [
            'email' => "root",
            'password' => password_hash($random_pass, PASSWORD_DEFAULT),
            'access_level' => 'admin'
        ])) {
            return $random_pass;
        }
    }
    return null;
}

function init_table_dedicado()
{
    return "CREATE TABLE `dedicado` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(250) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(300) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_send_mail` int DEFAULT NULL,
  `auto_send` int DEFAULT '0',
  `inactive` int DEFAULT '0',
  `hostid` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `graphid` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

            ";
}

function init_table_mail_send()
{
    return "CREATE TABLE `mail_send` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dedicado` int DEFAULT NULL,
  `email` varchar(250) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject` varchar(250) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_fail` int DEFAULT '0',
  `data_send` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `dedicado_idx` (`dedicado`),
  CONSTRAINT `dedicado` FOREIGN KEY (`dedicado`) REFERENCES `dedicado` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

";
}

// Inicialização
$new_root = [];
$db_success = false;

if (mysql_generic(init_table_users())) {
    $db_success = true;
    $root_pass = init_root_user();
    if ($root_pass) {
        $new_root = ["user" => "root", "pass" => $root_pass];
    }
}

mysql_generic(init_table_dedicado());
mysql_generic(init_table_mail_send());

$response = [
    "status" => $db_success ? "success" : "warning",
    "message" => "Instalação concluída com sucesso!",
    "database_initialized" => $db_success
];

if (!empty($new_root)) {
    $response['root_account'] = $new_root;
}

// Usado para debug da saida
// echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (php_sapi_name() !== 'cli') {

    // Adiciona os parâmetros
    $url .= $site_url . '?setup&configFinish';

    // Adiciona os dados na sessão para a página de resultados
    session_start();
    $_SESSION['install_result'] = $response;
    $_SESSION['missing_packages'] = $missingPackages ?? [];

    // Redireciona
    header("Location: $url");
    exit();
}
