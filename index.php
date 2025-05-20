<?php
$indexFilePath = __DIR__ . "/app.php";
$maintence = (json_decode(file_get_contents(__DIR__ . "/config.json"), true))['maintence'];

// Verifica se o arquivo index.php existe e é acessível
if (file_exists($indexFilePath) && is_readable($indexFilePath) && !file_exists(__DIR__ . "/update_progress.json") && !$maintence['status']) {
    include $indexFilePath;
} else {
    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Aplicação em Manutenção</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
                text-align: center;
                padding: 50px;
            }
            h1 {
                font-size: 2.5em;
                margin-bottom: 20px;
            }
            p {
                font-size: 1.2em;
            }
        </style>
    </head>
    <body>
        <h1>Aplicação em Manutenção</h1>
        <p>Desculpe pelo inconveniente. Estamos trabalhando para melhorar nossa aplicação.</p>
        <p>Por favor, tente novamente mais tarde.</p>
    </body>
    <script>
        setTimeout(() => {
            window.location.reload()
        }, 5000);
    </script>
    </html>
    
    ";
}
