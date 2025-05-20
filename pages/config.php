<?php
if (!$userData || $userData["access_level"] != "admin") {
    echo "<div class='container'><h1>Ooops! Pagina não encontrada!</h1></div>";
    $hasInclude = "error";
    exit;
}

$timbrado = __DIR__ . "/../consumption-report/timbrado.pdf";
$p12 = __DIR__ . "/../signature/cert.p12";
$timbrado = is_file($timbrado) ? $site_url . "consumption-report/timbrado.pdf" : null;
$p12 = is_file($p12) ? $site_url . "signature/cert.p12" : null;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Configuração do Aplicativo</h3>
                </div>
                <div class="card-body">
                    <form id="appConfigForm">
                        <fieldset class="mb-4">
                            <h3 class="border-bottom pb-2">Informações Básicas</h3>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="app_name" class="form-label">Nome do Aplicativo</label>
                                    <input type="text" class="form-control" id="app_name" name="app_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="app_short_name" class="form-label">Nome Curto</label>
                                    <input type="text" class="form-control" id="app_short_name" name="app_short_name">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="page_title" class="form-label">Título da Página</label>
                                    <input type="text" class="form-control" id="page_title" name="page_title">
                                </div>
                                <?php
                                if ($userData && $userData["email"] == "root") {
                                ?>
                                    <div class="col-md-6">
                                        <label class="form-label">Modo Manutenção</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="maintence_status" name="maintence[status]">
                                            <label class="form-check-label" for="maintence_status">Ativar modo manutenção</label>
                                        </div>
                                    </div>
                                <?php } ?>

                            </div>
                        </fieldset>

                        <fieldset class="mb-4">
                            <h3 class="border-bottom pb-2">E-Mail Informativo</h3>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="suport_phone" class="form-label">Telefone</label>
                                    <input type="text" class="phone form-control" id="suport_phone" name="suport_phone">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">E-Mail</label>
                                    <input type="text" class="form-control" id="suport_mail" name="suport_mail">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tecnico Responsavel</label>
                                    <input type="text" class="form-control" id="responsavel" name="responsavel">
                                </div>
                            </div>

                            <!-- Added PDF Template Section -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Template PDF Timbrado</label>
                                    <div class="input-group">
                                        <?php
                                        if ($timbrado) {
                                            echo '
                                        <a href="' . $timbrado . '" target="_blank" class="btn btn-outline-primary">
                                            Visualizar Template Atual
                                        </a>';
                                        }
                                        ?>
                                        <input type="file" class="form-control" id="pdf_template" name="pdf_template" accept=".pdf">
                                    </div>
                                    <small class="text-muted">Faça upload de um novo arquivo PDF para substituir o template atual</small>
                                </div>
                            </div>

                            <!-- Added Digital Certificate Section -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Certificado Digital (.p12)</label>

                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="use_digital_signature" name="use_digital_signature">
                                        <label class="form-check-label" for="use_digital_signature">Usar assinatura digital</label>
                                    </div>
                                    <div class="input-group mb-2"><?php
                                                                    if ($p12) {
                                                                        echo '
                                        <a href="' . $p12 . '" target="_blank" class="btn btn-outline-primary">
                                            Baixar Certificado Atual
                                        </a>';
                                                                    }
                                                                    ?>
                                        <input type="file" class="form-control" id="digital_certificate" name="digital_certificate" accept=".p12,.pfx">
                                    </div>
                                    <div class="mb-2">
                                        <label for="certificate_password" class="form-label">Senha do Certificado</label>
                                        <input type="password" class="form-control" id="certificate_password" name="certificate_password">
                                    </div>
                                    <small class="text-muted">Faça upload do certificado digital (.p12) e informe a senha</small>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mb-4">
                            <h3 class="border-bottom pb-2">URLs e Logos</h3>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="url_base_prod" class="form-label">URL Base (Produção)</label>
                                    <input type="text" class="form-control" id="url_base_prod" name="url_base_prod">
                                </div>
                                <div class="col-md-6">
                                    <label for="url_base_debug" class="form-label">URL Base (Debug)</label>
                                    <input type="text" class="form-control" id="url_base_debug" name="url_base_debug" value="zbx">
                                </div>
                                <div class="col-md-6">
                                    <label for="app_logo_url" class="form-label">URL do Logo do App</label>
                                    <input type="text" class="form-control" id="app_logo_url" name="app_logo_url">
                                </div>
                            </div>
                        </fieldset>

                        <?php
                        if ($userData && $userData["email"] == "root") {
                        ?>
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
                        <?php } ?>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="reset" onclick="loadConfig()" class="btn btn-secondary">Limpar</button>
                            <button type="button" onclick="saveConfig()" class="btn btn-primary">Salvar Configurações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let app_name = document.getElementById("app_name");
    let app_short_name = document.getElementById("app_short_name");
    let description = document.getElementById("description");
    let page_title = document.getElementById("page_title");
    let suport_phone = document.getElementById("suport_phone");
    let suport_mail = document.getElementById("suport_mail");
    let responsavel = document.getElementById("responsavel");
    let url_base_prod = document.getElementById("url_base_prod");
    let url_base_debug = document.getElementById("url_base_debug");
    let app_logo_url = document.getElementById("app_logo_url");
    let pdf_template = document.getElementById("pdf_template");
    let use_digital_signature = document.getElementById("use_digital_signature");
    let digital_certificate = document.getElementById("digital_certificate");
    let certificate_password = document.getElementById("certificate_password");

    <?php
    if ($userData && $userData["email"] == "root") {
    ?>
        let maintence_status = document.getElementById("maintence_status");
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
        let zabbixApiToken = document.getElementById("zabbixApiToken");
        let zabbixUrl = document.getElementById("zabbixUrl");
    <?php } ?>


    loadConfig();

    function loadConfig() {
        fetch(`<?= $site_url ?>api/?op=config-get`)
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    const config = data.data;
                    app_name.value = config.app_name || '';
                    app_short_name.value = config.app_short_name || '';
                    description.value = config.description || '';
                    page_title.value = config.page_title || '';
                    url_base_prod.value = config.url_base_prod || '';
                    url_base_debug.value = config.url_base_debug || '';
                    app_logo_url.value = config.app_logo_url || '';
                    suport_phone.value = config.suport_phone || '';
                    suport_mail.value = config.suport_mail || '';
                    responsavel.value = config.pdf_data.responsavel || '';
                    use_digital_signature.checked = config.pdf_data.signed || false;
                    certificate_password.value = config.pdf_data.signature_pass || '';

                    <?php
                    if ($userData && $userData["email"] == "root") {
                    ?>
                        maintence_status.checked = config.maintence.status || false;

                        dbHost.value = config.database.dbHost || '127.0.0.1';
                        dbName.value = config.database.dbName || '';
                        dbUserName.value = config.database.dbUserName || '';
                        dbUserPassword.value = config.database.dbUserPassword || '';

                        mailHost.value = config.smtp.mailHost || '';
                        mailPort.value = config.smtp.mailPort || '';
                        srcmail.value = config.smtp.srcmail || '';
                        srcname.value = config.smtp.srcname || '';
                        mailUsername.value = config.smtp.mailUsername || '';
                        mailUserPassword.value = config.smtp.mailUserPassword || '';
                        zabbixApiToken.value = config.zabbix.api_token || '';
                        zabbixUrl.value = config.zabbix.url || '';
                    <?php } ?>
                }
            })
            .catch(error => {
                console.error('Erro ao carregar configurações:', error);
                showAlert('Erro ao carregar configurações', 'danger');
            });
    }

    function saveConfig() {
        let configData = {};
        if (use_digital_signature.checked) {
            if (!certificate_password.value) {
                alert("Por favor, informe a senha do certificado digital.");
                return;
            }
        }

        <?php
        if ($userData && $userData["email"] == "root") {
        ?>
            configData = {
                app_name: app_name.value,
                app_short_name: app_short_name.value,
                app_logo_url: app_logo_url.value,
                description: description.value,
                page_title: page_title.value,
                suport_phone: suport_phone.value,
                suport_mail: suport_mail.value,
                url_base_prod: url_base_prod.value,
                url_base_debug: url_base_debug.value,
                maintence: {
                    status: maintence_status.checked
                },

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
                pdf_data: {
                    responsavel: responsavel.value,
                    period: "2592000",
                    signed: use_digital_signature.checked,
                    signature_pass: certificate_password.value,
                }
            };
        <?php } else { ?>
            configData = {
                app_name: app_name.value,
                app_short_name: app_short_name.value,
                app_logo_url: app_logo_url.value,
                description: description.value,
                page_title: page_title.value,
                suport_phone: suport_phone.value,
                suport_mail: suport_mail.value,
                url_base_prod: url_base_prod.value,
                url_base_debug: url_base_debug.value,
                pdf_data: {
                    responsavel: responsavel.value,
                    period: "2592000",
                    signed: use_digital_signature.checked,
                    signature_pass: certificate_password.value,
                }
            };
        <?php } ?>

        const formData = new FormData();
        formData.append('config', JSON.stringify(configData));

        if (pdf_template.files[0]) {
            formData.append('pdf_template', pdf_template.files[0]);
        }

        if (digital_certificate.files[0]) {
            formData.append('digital_certificate', digital_certificate.files[0]);
        }


        // Enviar para a API
        fetch(`<?= $site_url ?>api/?op=config-save`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert('Configurações salvas com sucesso!');
                    window.location.reload();
                } else {
                    alert(`Erro ao salvar: ${data.message || 'Erro desconhecido'}`);
                }
            })
            .catch(error => {
                console.error('Erro ao salvar configurações:', error);
                alert('Erro ao salvar configurações');
            });
    }

    <?php
    if ($userData && $userData["email"] == "root") {
    ?>

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
    <?php } ?>

    $(document).ready(function() {
        $('.phone').mask('(00)0000-0000');
    });
</script>