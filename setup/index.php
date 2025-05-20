<?php
$httpProtocol = (isset($_SERVER['HTTPS']) || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) ? 'https://' : 'http://';
$base_url = explode("?", $_SERVER["REQUEST_URI"])[0];
$site_url = $httpProtocol . $_SERVER["HTTP_HOST"] . $base_url;

if(!isset($_GET["setup"])) {
    header("Location: $site_url../?setup");
    exit;
}
if (isset($_GET["success"])) {
    include_once __DIR__ . "/preload.php";
    exit;
}

if (isset($_GET["configFinish"])) {
    include_once __DIR__ . "/install-result.php";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Configuração Inicial</title>
    <link rel="stylesheet" href="<?= $site_url ?>assets/css/style.css">
    <meta name="robots" content="noindex">

    <style>
        .alert-info {
            margin: 0;
            font-size: 12px;
            color: #838383;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Dados de Inicialização</h3>
                    </div>
                    <div class="card-body">
                        <form id="appConfigForm">
                            <fieldset class="mb-4">
                                <div class="row mb-3">
                                    <label for="url_base_prod" class="form-label">URL Base</label>
                                    <div class="input-group">
                                        <span><?= $httpProtocol . $_SERVER["HTTP_HOST"] ?></span>
                                        <input type="text" class="form-control" id="url_base_prod" name="url_base_prod" value="<?= $base_url ?>" placeholder="<?= $base_url ?>">
                                    </div>
                                    <p class="alert-info">Recomendamos não mexer, mas se mexer é parar de funcionar basta preencher com <strong><?= $base_url ?></strong></p>
                                </div>
                            </fieldset>

                            <fieldset class="mb-4">
                                <h3 class="border-bottom pb-2">Integração com Zabbix</h3>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="zabbixApiToken" class="form-label">API Token</label>
                                        <input type="text" class="form-control" id="zabbixApiToken" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="zabbixUrl" class="form-label">Nome do Banco</label>
                                        <input type="url" inputmode="url" class="form-control" id="zabbixUrl" required>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="mb-4">
                                <h3 class="border-bottom pb-2">Configuração do Banco de Dados</h3>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="dbHost" class="form-label">Host do Banco</label>
                                        <input type="text" class="form-control" id="dbHost" name="database[dbHost]" value="127.0.0.1" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dbName" class="form-label">Nome do Banco</label>
                                        <input type="text" class="form-control" id="dbName" name="database[dbName]" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="dbUserName" class="form-label">Usuário</label>
                                        <input type="text" class="form-control" id="dbUserName" name="database[dbUserName]" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="dbUserPassword" class="form-label">Senha</label>
                                        <input type="password" class="form-control" id="dbUserPassword" name="database[dbUserPassword]">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Testar Conexão</label>
                                        <button type="button" onclick="testDB()" class="btn btn-outline-secondary w-100" id="testDbConnection">Testar</button>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="mb-4">
                                <h3 class="border-bottom pb-2">Configuração de E-mail (SMTP)</h3>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="mailHost" class="form-label">Servidor SMTP</label>
                                        <input type="text" class="form-control" id="mailHost" name="smtp[mailHost]">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mailPort" class="form-label">Porta</label>
                                        <input type="text" class="form-control" id="mailPort" name="smtp[mailPort]">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="srcmail" class="form-label">E-mail Remetente</label>
                                        <input type="email" class="form-control" id="srcmail" name="smtp[srcmail]">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="srcname" class="form-label">Nome Remetente</label>
                                        <input type="text" class="form-control" id="srcname" name="smtp[srcname]">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="mailUsername" class="form-label">Usuário SMTP</label>
                                        <input type="text" class="form-control" id="mailUsername" name="smtp[mailUsername]">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mailUserPassword" class="form-label">Senha SMTP</label>
                                        <input type="password" class="form-control" id="mailUserPassword" name="smtp[mailUserPassword]">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <button type="button" onclick="testSMTP()" class="btn btn-outline-primary" id="testSmtpConnection">Testar Conexão SMTP</button>
                                </div>
                            </fieldset>

                            <div class="d-flex justify-content-center mt-4">
                                <button type="button" onclick="saveConfig()" class="btn btn-primary">Salvar Configurações</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        let url_base_prod = document.getElementById("url_base_prod");
        let dbHost = document.getElementById("dbHost");
        let dbName = document.getElementById("dbName");
        let dbUserName = document.getElementById("dbUserName");
        let dbUserPassword = document.getElementById("dbUserPassword");
        let mailHost = document.getElementById("mailHost");
        let mailPort = document.getElementById("mailPort");
        let srcmail = document.getElementById("srcmail");
        let srcname = document.getElementById("srcname");
        let mailUsername = document.getElementById("mailUsername");
        let mailUserPassword = document.getElementById("mailUserPassword");
        let digital_certificate = document.getElementById("digital_certificate");
        let certificate_password = document.getElementById("certificate_password");


        function saveConfig() {
            // Preparar objeto de configuração
            const configData = {
                url_base_prod: url_base_prod.value,
                database: {
                    dbHost: dbHost.value,
                    dbUserName: dbUserName.value,
                    dbUserPassword: dbUserPassword.value,
                    dbName: dbName.value
                },
                smtp: {
                    mailHost: mailHost.value,
                    srcmail: srcmail.value,
                    srcname: srcname.value,
                    mailPort: mailPort.value,
                    mailUsername: mailUsername.value,
                    mailUserPassword: mailUserPassword.value
                },                
                zabbix: {
                    api_token: zabbixApiToken.value,
                    url: zabbixUrl.value
                },
            };

            // Enviar para a API
            fetch(`<?= $site_url ?>api/?op=config-save`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(configData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert('Configurações salvas com sucesso!');
                        window.location.href = "<?= $site_url ?>?setup&success"
                    } else {
                        alert(`Erro ao salvar: ${data.message || 'Erro desconhecido'}`);
                    }
                })
                .catch(error => {
                    console.error('Erro ao salvar configurações:', error);
                    alert('Erro ao salvar configurações');
                });
        }

        function testSMTP() {
            document.getElementById("testSmtpConnection").setAttribute("disabled", true);
            document.getElementById("testSmtpConnection").innerHTML = "Verificando...";
            fetch(`<?= $site_url ?>api/?op=config-test-smtp`, {
                    method: "TEST",
                    body: JSON.stringify({
                        smtp: {
                            mailHost: mailHost.value,
                            srcmail: srcmail.value,
                            srcname: srcname.value,
                            mailPort: mailPort.value,
                            mailUsername: mailUsername.value,
                            mailUserPassword: mailUserPassword.value
                        }
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    document.getElementById("testSmtpConnection").removeAttribute("disabled");
                    document.getElementById("testSmtpConnection").innerHTML = "Testar Conexão SMTP";
                })
        }

        function testDB() {
            document.getElementById("testDbConnection").setAttribute("disabled", true);
            document.getElementById("testDbConnection").innerHTML = "Verificando...";
            fetch(`<?= $site_url ?>api/?op=config-test-db`, {
                    method: "TEST",
                    body: JSON.stringify({
                        database: {
                            dbHost: dbHost.value,
                            dbUserName: dbUserName.value,
                            dbUserPassword: dbUserPassword.value,
                            dbName: dbName.value
                        }
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    document.getElementById("testDbConnection").removeAttribute("disabled");
                    document.getElementById("testDbConnection").innerHTML = "Testar";
                })
        }
    </script>
</body>

</html>