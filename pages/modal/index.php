<?php
$modal = $_GET["modal"] ?? null;
if (!$modal) {
    echo "<h4>Ooops, parece que algo deu errado!</h4>";
}
require_once __DIR__ . "/../../autoload.php";

switch ($modal) {
    case 'host-details':
        include_once __DIR__ . "/host-details.php";
        break;
    case 'dedicated-details':
        include_once __DIR__ . "/dedicated-details.php";
        break;
    case 'dedicated-history':
        include_once __DIR__ . "/dedicated-history.php";
        break;
    default:
        echo "<h4>Ooops, parece que algo deu errado!</h4>";
        break;
}
