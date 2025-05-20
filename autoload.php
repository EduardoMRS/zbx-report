<?php
session_start();
// error_reporting(0); // Desativa a exibição de erros
// ini_set('display_errors', 0); // Desativa a exibição de erros no navegador
error_reporting(E_ALL & ~E_DEPRECATED);

$configApp = json_decode(file_get_contents(__DIR__ . "/config.json"), true) ?? [];

// Variaveis Globais
extract($configApp);
extract($database);
$httpProtocol = (isset($_SERVER['HTTPS']) || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) ? 'https://' : 'http://';
$base_path = explode("?", str_replace("index.php", "", $_SERVER['PHP_SELF']))[0];
$site_url = $httpProtocol . str_replace("//", "/", (($_SERVER['HTTP_HOST'] ?? "127.0.0.1:8443") . "/" . ($debug ? $url_base_debug :  $url_base_prod) . "/"));
$app_version = "1.0.1";

if (isset($app_logo_url) && !str_contains($app_logo_url, "http")) {
    $app_logo_url = $site_url . $app_logo_url;
}
if (isset($url_logo) && !str_contains($url_logo, "http")) {
    $url_logo = $site_url . $url_logo;
}


// Carrega funções
$function_list = glob(__DIR__ . "/assets/function/*.php");
foreach ($function_list as $file) {
    require_once($file);
}

$data_user = null;
if (isset($_SESSION["idUser"])) {
    $data_user = mysql_select_in_array("users", "id='$_SESSION[idUser]'");
}