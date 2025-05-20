<?php
$zabbixApi = new ZabbixAPI();
$hosts = $zabbixApi->getHosts(
    ['hostid', 'host', 'name', 'status', 'available'],
    ['ip', 'type', 'main']
);


$count_host = count($hosts) ?? 0;
$count_dedicated = mysql_select_count("dedicado") ?? 0;
$count_user = mysql_select_count("users", "email!='root'") ?? 0;
$count_mail_send = mysql_select_count("mail_send") ?? 0;
?>
<style>
    .dashboard-card {
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: pointer;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .card-host {
        border-left: 4px solid #3498db;
    }

    .card-dedicated {
        border-left: 4px solid #2ecc71;
    }

    .card-user {
        border-left: 4px solid #f39c12;
    }

    .card-mail {
        border-left: 4px solid #e74c3c;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
    }
</style>
<div class="container py-4">
    <h1 class="mb-4">Alguns dados Interessantes</h1>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card card-host h-100" onclick="window.location.href='<?= $site_url ?>?page=hosts'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Hosts</h5>
                            <p class="card-text">Total de hosts monitorados</p>
                        </div>
                        <i class="fas fa-server fa-2x text-primary"></i>
                    </div>
                    <div class="stat-number text-primary"><?= $count_host ?></div>
                    <div class="text-end mt-2">
                        <small class="text-muted">Clique para ver detalhes</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card dashboard-card card-dedicated h-100" onclick="window.location.href='<?= $site_url ?>?page=dedicated'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Clientes Dedicados</h5>
                            <p class="card-text">Total de clientes dedicados</p>
                        </div>
                        <i class="fas fa-user-tie fa-2x text-success"></i>
                    </div>
                    <div class="stat-number text-success"><?= $count_dedicated ?></div>
                    <div class="text-end mt-2">
                        <small class="text-muted">Clique para ver detalhes</small>
                    </div>
                </div>
            </div>
        </div>

        <?php
        if ($userData && $userData["access_level"] == "admin") {
        ?>
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card card-user h-100" onclick="window.location.href='<?= $site_url ?>?page=users'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Usuários</h5>
                                <p class="card-text">Total de usuários cadastrados</p>
                            </div>
                            <i class="fas fa-users fa-2x text-warning"></i>
                        </div>
                        <div class="stat-number text-warning"><?= $count_user ?></div>
                        <div class="text-end mt-2">
                            <small class="text-muted">Clique para ver detalhes</small>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php } ?>
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card card-mail h-100" onclick="window.location.href='<?= $site_url ?>?page=mail-history'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Relatorios Enviados</h5>
                            <p class="card-text">Total de relatorios enviados</p>
                        </div>
                        <i class="fas fa-envelope fa-2x text-danger"></i>
                    </div>
                    <div class="stat-number text-danger"><?= $count_mail_send ?></div>
                    <div class="text-end mt-2">
                        <small class="text-muted">Clique para ver detalhes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>