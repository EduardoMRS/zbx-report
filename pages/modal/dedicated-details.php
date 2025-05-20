<?php
$hostid = $_GET['id'] ?? null;
$dedicatedId = $_GET['dedicatedId'] ?? null;
$dedicated = null;
if (!$hostid && !$dedicatedId) {
    die('ID do Host ou ID do Cliente não informado!');
}
$dedicatedFirst = mysql_select_in_array("dedicado", "hostid='$hostid'") ?? 0;
if ($dedicatedId) {
    $dedicated = mysql_select_in_array("dedicado", "id='$dedicatedId'") ?? null;
    if (!$dedicated) {
        die('Cliente não encontrado!');
    }
    $hostid = $dedicated['hostid'];
}
$firstHost = null;
if ($dedicatedFirst) {
    $firstHost = $dedicatedFirst["graphid"] ?? null;
}
?>
<div class="modal-page">
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-hover: #0b5ed7;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }

        .client-card {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .form-card {
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
        }

        .form-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .status-active {
            color: var(--success-color) !important;
        }

        .status-inactive {
            color: var(--danger-color) !important;
        }

        .badge-custom {
            font-size: 0.85rem;
            font-weight: 500;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }

            100% {
                opacity: 1;
            }
        }

        .status-alert {
            animation: pulse 1.5s infinite;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .required-field::after {
            content: " *";
            color: var(--danger-color);
        }

        .buttons-interactions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .buttons-interactions button {
            font-size: 12px;
        }
    </style>

    <?php
    if (!$dedicatedId) {
    ?>
        <div class="client-card card p-4 mb-4">
            <h2 class="mb-1"><i class="fas fa-user-tie me-2"></i>Cliente Dedicado</h2>
            <div class="form-group hide" id="section-dedicated-list">
                <select name="dedicated-list" class="form-control text-center" id="dedicated-list"></select>
            </div>
        </div>
    <?php } ?>


    <div class="row">
        <form id="clientForm" <?= $dedicated ? '' : 'class="hide"' ?>>
            <div class="col-md-12">
                <div class="card form-card p-4">
                    <h4 class="mb-3"><i class="fas fa-edit me-2"></i>Dados do Host</h4>
                    <input type="hidden" id="dedicated-id">
                    <input type="hidden" id="host-id" value="<?= $hostid ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="mb-0">
                                <i class="fas fa-id-card me-1"></i> ID do Host: <?= htmlspecialchars($hostid) ?>
                            </p>
                        </div>
                        <div>
                            <p class="mb-0 mt-1">
                                <i class="fas fa-circle me-1" id="status-icon"></i>
                                Status: <span id="host-status">Carregando...</span>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <i class="fas fa-file-alt fa-2x text-info"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Descrição</h5>
                                    <p class="mb-0" id="host-description">Carregando...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="interface-chart">Interface para monitoramento</label>
                            <select name="interface-chart" class="form-control" id="interface-chart"></select>
                        </div>
                        <div id="consumoContainer"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card form-card p-4">
                    <h4 class="mb-3"><i class="fas fa-edit me-2"></i>Informações do Cliente</h4>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="client-name" class="form-label required-field">Nome do Cliente</label>
                            <input type="text" class="form-control" id="client-name" required>
                            <div class="invalid-feedback">Por favor, informe o nome do cliente.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="client-email" class="form-label required-field">E-mail</label>
                            <input type="email" class="form-control" id="client-email" required>
                            <div class="invalid-feedback">Por favor, informe um e-mail válido.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="client-address" class="form-label">Endereço</label>
                            <textarea class="form-control" id="client-address" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="email-date" class="form-label">Data para Envio de E-mail</label>
                            <select class="form-control" name="email-date" id="email-date">
                                <option value="null">
                                    < -- Selecione um dia -->
                                </option>
                                <?php
                                for ($i = 1; $i <= 28; $i++) {
                                    echo "<option value=\"$i\">Todo dia $i</option>";
                                }
                                ?>
                            </select>

                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="d-flex align-items-center">
                                <div class="form-check form-switch me-3">
                                    <input class="form-check-input" type="checkbox" id="email-active">
                                    <label class="form-check-label" for="email-active">Envio automatico</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="buttons-interactions">
                        <button type="button" class="btn btn-secondary" onclick="loadClientData()">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="save-btn">
                            <span class="spinner-border spinner-border-sm d-none" id="save-spinner" role="status" aria-hidden="true"></span>
                            <span id="save-text"><?= $dedicatedId ? "Atualizar Cliente" : "Cadastrar Cliente" ?></span>
                        </button>
                        <button type="button" class="btn btn-danger hide" id="delete-btn">Remover</button>
                        <?php
                        if (!$dedicatedId) {
                        ?>
                            <button type="button" class="btn btn-primary hide" id="add-btn">
                                <span>Adicionar outro cliente</span>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="<?= $site_url ?>assets/js/chart.js"></script>

    <script>
        document.getElementById('clientForm').addEventListener('submit', saveClientData);
        selectInterfaceList = document.getElementById("interface-chart");
        hostName = "";

        async function loadClientData(id = <?= $dedicatedId ?? 0 ?>) {
            try {
                const response = await fetch(`<?= $site_url ?>api/?op=dedicated-get&hostid=<?= $hostid ?>&id=${id}`);
                const data = await response.json();
                const host = data.data;
                const client = data.data.dedicated;

                if (data.status === 'success') {
                    if (client.graphid) {
                        selectInterfaceList.value = client.graphid;
                    }
                    hostName = data.data.name ?? '';
                    document.getElementById('dedicated-id').value = client.id ?? 0;
                    document.getElementById('client-name').value = client.name ?? data.data.name ?? '';
                    document.getElementById('client-email').value = client.email || '';
                    document.getElementById('client-address').value = client.address || '';

                    if (client.date_send_mail) {
                        document.getElementById('email-date').value = client.date_send_mail;
                    }

                    document.getElementById('email-active').checked = client.auto_send ? client.auto_send == 1 ? true : false : false;

                    document.getElementById('host-status').innerHTML = host.status ? "Ativo" : "Inativo";
                    document.getElementById('host-description').innerHTML = host.description || "Nenhuma descrição fornecida";
                    document.getElementById("delete-btn").setAttribute("data-name", client.name ?? data.data.name ?? '');
                    document.getElementById("delete-btn").setAttribute("data-dedicatedid", client.id ?? 0);
                    if (client.id) {
                        document.getElementById('save-text').innerHTML = "Atualizar Cliente";
                        document.getElementById("delete-btn").classList.remove("hide");
                    }
                    <?php
                    if (!$dedicatedId) {
                    ?>
                        selectDedicatedList.value = client.id;
                    <?php } ?>
                } else if (data.status === 'not_found') {
                    console.log('Nenhum cliente encontrado, criando novo registro');
                } else {
                    showAlert('Erro ao carregar dados do cliente', 'danger');
                }
            } catch (error) {
                console.error('Erro:', error);
                showAlert('Erro na comunicação com o servidor', 'danger');
            }
        }

        async function saveClientData(e) {
            e.preventDefault();

            // Validação básica
            const form = document.getElementById('clientForm');
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            if (selectInterfaceList.value === "null") {
                alert("Para salvar é necessario selecionar uma interface!");
                return;
            }

            // Mostra spinner e desabilita botão
            const saveBtn = document.getElementById('save-btn');
            const spinner = document.getElementById('save-spinner');
            const saveText = document.getElementById('save-text');

            saveBtn.disabled = true;
            spinner.classList.remove('d-none');
            saveText.textContent = 'Salvando...';

            try {
                const clientData = {
                    id: document.getElementById('dedicated-id').value ?? 0,
                    hostid: document.getElementById('host-id').value,
                    graphid: selectInterfaceList.value,
                    name: document.getElementById('client-name').value,
                    email: document.getElementById('client-email').value,
                    address: document.getElementById('client-address').value,
                    date_send_mail: document.getElementById('email-date').value,
                    auto_send: document.getElementById('email-active').checked
                };

                console.log(clientData);

                fetch(`<?= $site_url ?>api/?op=dedicated-save`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(clientData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            alert(data.message);
                        }
                        <?php
                        if (!$dedicatedId) {
                        ?>
                            loadDedicatedList();
                            if (data.id) {
                                selectDedicatedList.value = data.id;
                            }
                        <?php } ?>
                    })
            } catch (error) {
                console.error('Erro:', error);
                showAlert('Erro na comunicação com o servidor', 'danger');
            } finally {
                // Restaura botão
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
                saveText.textContent = 'Atualizar Cliente';
                document.getElementById("delete-btn").classList.remove("hide");
            }
        }

        function showAlert(message, type) {
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());

            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            document.querySelector('.modal-page').insertAdjacentHTML('afterbegin', alertHtml);
        }

        loadInterfaceChartList();
        <?php
        if ($dedicatedId) {
        ?>
            loadClientData();
        <?php } ?>

        function loadInterfaceChartList() {
            fetch(`<?= $site_url ?>api/?op=host-get-interface-list`, {
                    method: "POST",
                    body: JSON.stringify({
                        hostid: "<?= $hostid ?>",
                        graphid: <?= !$dedicated ? ($firstHost ?? "null") : "\"$dedicated[graphid]\"" ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        selectInterfaceList.innerHTML = "<option value='null'><-- Selecione uma interface --></option>";

                        interfaceList = Array.isArray(data.data) ? data.data : Object.values(data.data);
                        if (data.data.length == 0) {
                            selectInterfaceList.innerHTML = "<option value='null'>Nenhuma interface encontrada</option>";
                            return;
                        }
                        interfaceList.forEach(item => {
                            let option = document.createElement("option");
                            option.value = item.graphid;
                            option.innerHTML = item.interface;

                            selectInterfaceList.append(option);
                        })
                    }
                })
        }

        <?php
        if (!$dedicatedId) {
        ?>
            sectionDedicatedList = document.getElementById("section-dedicated-list");
            selectDedicatedList = document.getElementById("dedicated-list");
            loadDedicatedList();

            function loadDedicatedList() {
                fetch(`<?= $site_url ?>api/?op=host-get-dedicated-list`, {
                        method: "POST",
                        body: JSON.stringify({
                            hostid: "<?= $hostid ?>"
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            dedicatedList = data.data;
                            if (dedicatedList.length > 0) {
                                document.getElementById("add-btn").classList.remove("hide");
                            } else {
                                document.getElementById("add-btn").classList.add("hide");
                            }
                            if (dedicatedList.length > 1) {
                                selectDedicatedList.innerHTML = "<option value='null'><-- Selecione um cliente --></option>";
                                dedicatedList = data.data;
                                dedicatedList.forEach(item => {
                                    let option = document.createElement("option");
                                    option.value = item.id;
                                    option.innerHTML = item.name;
                                    selectDedicatedList.append(option);
                                })
                                sectionDedicatedList.classList.remove("hide");
                                return;
                            }
                        }

                        document.getElementById('clientForm').classList.remove("hide");
                        document.getElementById('delete-btn').classList.add("hide");
                        loadClientData();
                        document.getElementById("save-text").innerHTML = "Cadastrar Cliente";
                        sectionDedicatedList.classList.add("hide");
                    })
            }

            selectDedicatedList.addEventListener("change", () => {
                dedicatedId = selectDedicatedList.value;
                if (selectDedicatedList.value != "null") {
                    if (selectDedicatedList.value != "new") {
                        loadClientData(dedicatedId);
                        for (var i = 0; i < selectDedicatedList.length; i++) {
                            if (selectDedicatedList.options[i].value == 'new')
                                selectDedicatedList.remove(i);
                        }
                    }
                    document.getElementById('clientForm').classList.remove("hide");
                } else {
                    document.getElementById('clientForm').classList.add("hide");
                }
            })
        <?php } ?>



        <?php
        if (!$dedicatedId) {
        ?>
            document.getElementById("add-btn").addEventListener("click", () => {
                document.getElementById('clientForm').reset();
                document.getElementById("client-name").value = hostName;
                document.getElementById("save-text").innerHTML = "Cadastrar Cliente";
                document.getElementById("dedicated-id").value = "0";
                selectDedicatedList.innerHTML += ("<option value='new'><-- Novo Cliente --></option>");
                selectDedicatedList.value = "new";
                document.getElementById("delete-btn").classList.add("hide");
            })
        <?php } ?>

        selectInterfaceList.addEventListener("change", () => {
            if (selectInterfaceList.value != "null") {
                loadChartInterface(selectInterfaceList.value);
            }
        })

        function loadChartInterface(graphid) {
            fetch(`<?= $site_url ?>api/?op=host-get-interface-chart`, {
                    method: "POST",
                    body: JSON.stringify({
                        hostid: "<?= $hostid ?>",
                        graphid: `${graphid}`
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        renderInterfaceChart(data.data);
                    }
                })
                .catch(error => {
                    console.error("Erro ao carregar gráfico:", error);
                });
        }

        function renderInterfaceChart(chartData) {
            const oldCanvas = document.getElementById('interfaceChartCanvas');
            if (oldCanvas) oldCanvas.remove();

            const container = document.getElementById('consumoContainer');
            const canvas = document.createElement('canvas');
            canvas.id = 'interfaceChartCanvas';
            canvas.height = 300;
            container.appendChild(canvas);

            // Limitar a quantidade de pontos para melhor performance
            const maxDataPoints = 2000; // Ajuste conforme necessário
            const values = downsampleData(chartData.values, maxDataPoints);

            const timestamps = values.map(item => new Date(item.timestamp * 1000));
            const numericValues = values.map(item => parseFloat(item.value));

            // Verificar se o período abrange mais de um dia
            const timeRange = timestamps[timestamps.length - 1] - timestamps[0];
            const isMultiDay = timeRange > 24 * 60 * 60 * 1000;

            // Simplificar labels adaptando ao período
            const labelInterval = Math.max(1, Math.floor(values.length / (isMultiDay ? 15 : 10)));
            const displayLabels = timestamps.map((date, index) =>
                index % labelInterval === 0 ? formatTimeLabel(date, isMultiDay) : ''
            );

            const ctx = canvas.getContext('2d');

            // Configurações específicas para grandes conjuntos de dados
            const chartOptions = {
                type: 'line',
                data: {
                    labels: displayLabels,
                    datasets: [{
                        label: chartData.name,
                        data: numericValues,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 1, // Reduzido para melhor visualização
                        tension: 0.1,
                        fill: true,
                        pointRadius: 0, // Remove pontos para melhor performance
                        pointHoverRadius: 3 // Mostra pontos apenas no hover
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 0 // Desativa animações para grandes datasets
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: false
                    },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: true,
                                maxRotation: 45,
                                minRotation: 45,
                                maxTicksLimit: 15 // Limita número de ticks no eixo X
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Consumo'
                            },
                            ticks: {
                                callback: formatBytes
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${formatBytes(context.raw)}`;
                                },
                                title: function(context) {
                                    return new Date(values[context[0].dataIndex].timestamp * 1000).toLocaleString();
                                }
                            }
                        },
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: chartData.name,
                            font: {
                                size: 16
                            }
                        },
                        zoom: {
                            pan: {
                                enabled: true,
                                mode: 'xy'
                            },
                            zoom: {
                                wheel: {
                                    enabled: true
                                },
                                pinch: {
                                    enabled: true
                                },
                                mode: 'xy'
                            }
                        }
                    }
                }
            };

            const chart = new Chart(ctx, chartOptions);

            if (typeof Chart.Zoom !== 'undefined') {
                Chart.register(Chart.Zoom);
            }
        }

        function downsampleData(values, maxPoints = 1000) {
            if (values.length <= maxPoints) return values;

            const step = Math.ceil(values.length / maxPoints);
            const sampledValues = [];

            for (let i = 0; i < values.length; i += step) {
                sampledValues.push(values[i]);
            }

            return sampledValues;
        }

        function formatTimeLabel(date, showDate = false) {
            if (showDate) {
                // Mostrar data + hora para períodos longos
                return date.toLocaleString([], {
                    day: '2-digit',
                    month: '2-digit',
                    year: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } else {
                // Mostrar apenas hora para períodos curtos
                return date.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }


        document.getElementById("delete-btn").addEventListener("click", () => {
            let clientName = document.getElementById("delete-btn").dataset.name ?? null;
            let clientId = document.getElementById("delete-btn").dataset.dedicatedid ?? 0;
            console.log(clientName);
            console.log(clientId);
            if (confirm(`Deseja realmente excluir ${clientName? " o cliente '" + clientName +"'":"este cliente"} da lista de dedicados?`)) {
                if (!clientId) {
                    alert("ID do cliente não localizado")
                    return;
                }
                fetch(`<?= $site_url ?>api/?op=dedicated-delete`, {
                        method: "DELETE",
                        body: JSON.stringify({
                            id: clientId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            alert(data.message);
                        } else {
                            console.error("Resposta", data);
                        }
                        if (data.status === "success") {
                            loadDedicatedList();
                        }
                    })
            }
        })
    </script>
</div>