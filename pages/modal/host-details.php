<?php
$hostId = $_GET['id'] ?? null;
if (!$hostId) {
    die('ID do Host não informado!');
}
?>
<div>
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-hover: #0b5ed7;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }

        .monitor-card {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .data-card {
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
        }

        .data-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .status-active {
            color: var(--success-color) !important;
        }

        .status-inactive {
            color: var(--danger-color) !important;
        }

        .interface-available {
            color: var(--success-color) !important;
        }

        .interface-unavailable {
            color: var(--danger-color) !important;
        }

        .badge-custom {
            font-size: 0.85rem;
            font-weight: 500;
        }

        .macro-badge {
            font-family: monospace;
            background-color: #f8f9fa;
            color: #212529 !important;
            border: 1px solid #dee2e6;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .chart-unit {
            font-size: 0.8rem;
            color: #6c757d;
            margin-left: 5px;
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
    </style>
    <!-- Cabeçalho -->
    <div class="monitor-card card p-4 mb-4">
        <h2 class="mb-1"><i class="fas fa-server me-2"></i>Detalhes do Host</h2>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <p class="mb-0">
                    <i class="fas fa-id-card me-1"></i> ID: <?= htmlspecialchars($hostId) ?>
                </p>
                <p class="mb-0 mt-1">
                    <i class="fas fa-circle me-1" id="status-icon"></i>
                    Status: <span id="host-status">Carregando...</span>
                </p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary rounded-pill fs-6" id="host-name">
                    ...
                </span>
                <p class="mb-0 mt-1">
                    <i class="fas fa-desktop me-1"></i> Dedicado: <span id="host-dedicated">...</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Informações Básicas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card data-card p-4">
                <h4 class="mb-3"><i class="fas fa-info-circle me-2"></i>Informações Básicas</h4>
                <div class="row" id="basicInfo">
                    <div class="col-12 text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Consumo -->
    <div class="row mb-4" id="consumptionSection" style="display: none;">
        <div class="col-md-12">
            <div class="card data-card p-4">
                <h4 class="mb-3"><i class="fas fa-chart-line me-2"></i>Gráficos de Consumo</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="chart-title mb-0">Período de Análise</h5>
                            <small class="text-muted" id="timeRange"></small>
                        </div>
                    </div>
                </div>
                <div class="row" id="chartsContainer">
                    <!-- Gráficos serão inseridos aqui via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Interfaces -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card data-card p-4">
                <h4 class="mb-3"><i class="fas fa-network-wired me-2"></i>Interfaces</h4>
                <div class="table-responsive">
                    <table class="table table-hover" id="interfacesTable">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>IP</th>
                                <th>Porta</th>
                                <th>Disponibilidade</th>
                                <th>Último Erro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card data-card p-4">
                <h4 class="mb-3"><i class="fas fa-code me-2"></i>Macros</h4>
                <div id="macrosContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card data-card p-4">
                <h4 class="mb-3"><i class="fas fa-code me-2"></i>Consumo</h4>
                <div>
                    <div class="text-center py-3">
                        <div class="form-group">
                            <label for="interface-chart">Interface</label>
                            <select name="interface-chart" class="form-control" id="interface-chart"></select>
                        </div>
                        <div id="consumoContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= $site_url ?>assets/js/chart.js"></script>

    <script>
        loadHostData();

        async function loadHostData() {
            try {
                const response = await fetch(`<?= $site_url ?>api/?op=host-details&hostid=<?= $hostId ?>`);
                const data = await response.json();
                if (data.status === 'success') {
                    updateHostHeader(data.data);
                    updateBasicInfo(data.data);
                    updateInterfacesTable(data.data.interfaces);
                    updateMacros(data.data.macros);

                    // Se existirem dados de gráficos, atualiza
                    if (data.data.graph_data && data.data.graph_data.length > 0) {
                        updateConsumptionCharts(data.data);
                    }
                } else {
                    showAlert('Erro ao carregar dados do host');
                }
            } catch (error) {
                console.error('Erro:', error);
                showAlert('Erro na comunicação com o servidor');
            }
        }

        function updateHostHeader(hostData) {
            document.getElementById("host-name").innerText = hostData.name;
            document.getElementById("host-dedicated").innerText = hostData.dedicated === "true" ? "Sim" : "Não";

            const isActive = hostData.status === '0';
            document.getElementById("host-status").innerText = isActive ? 'Ativo' : 'Inativo';
            document.getElementById("host-status").className = isActive ? 'status-active' : 'status-inactive';
            document.getElementById("status-icon").className = `fas fa-circle me-1 ${isActive ? 'status-active' : 'status-inactive'}`;
        }

        function updateBasicInfo(hostData) {
            const basicInfoHtml = `
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-file-alt fa-2x text-info"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Descrição</h5>
                            <p class="mb-0">${hostData.description || 'Nenhuma descrição fornecida'}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-tags fa-2x text-warning"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Tags</h5>
                            <p class="mb-0">${hostData.tags.length > 0 ? 
                                hostData.tags.map(tag => tag.tag + ': ' + tag.value).join(', ') : 
                                'Nenhuma tag definida'}</p>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('basicInfo').innerHTML = basicInfoHtml;
        }

        function updateInterfacesTable(interfaces) {
            let interfacesHtml = '';

            interfaces.forEach(intf => {
                const isAvailable = intf.available === '1';
                const lastError = intf.error ? `${intf.error} (desde ${new Date(intf.errors_from * 1000).toLocaleString()})` : 'Nenhum erro';

                interfacesHtml += `
                    <tr>
                        <td>${getInterfaceType(intf.type)}</td>
                        <td>${intf.ip || 'N/A'}</td>
                        <td>${intf.port || 'N/A'}</td>
                        <td>
                            <span class="${isAvailable ? 'interface-available' : 'interface-unavailable'}">
                                <i class="fas ${isAvailable ? 'fa-check-circle' : 'fa-times-circle'} me-1"></i>
                                ${isAvailable ? 'Disponível' : 'Indisponível'}
                            </span>
                        </td>
                        <td>${lastError}</td>
                    </tr>
                `;
            });

            document.querySelector('#interfacesTable tbody').innerHTML = interfacesHtml;
        }

        function updateMacros(macros) {
            let macrosHtml = '';

            if (macros.length > 0) {
                macros.forEach(macro => {
                    macrosHtml += `
                        <div class="mb-2">
                            <span class="badge macro-badge me-2">${macro.macro}</span>
                            <span>=</span>
                            <span class="ms-2">${macro.value}</span>
                            ${macro.description ? `<small class="d-block text-muted mt-1">${macro.description}</small>` : ''}
                        </div>
                    `;
                });
            } else {
                macrosHtml = '<p class="text-muted">Nenhuma macro definida para este host.</p>';
            }

            document.getElementById('macrosContainer').innerHTML = macrosHtml;
        }

        function getInterfaceType(typeCode) {
            const types = {
                '1': 'Agent',
                '2': 'SNMP',
                '3': 'IPMI',
                '4': 'JMX'
            };
            return types[typeCode] || `Desconhecido (${typeCode})`;
        }

        function getChartColor(index) {
            const colors = [
                '#0d6efd', // primary
                '#198754', // success
                '#dc3545', // danger
                '#ffc107', // warning
                '#0dcaf0', // info
                '#6f42c1', // purple
                '#fd7e14', // orange
                '#20c997' // teal
            ];
            return colors[index % colors.length];
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


        selectInterfaceList = document.getElementById("interface-chart");
        loadInterfaceChartList();

        function loadInterfaceChartList() {
            fetch(`<?= $site_url ?>api/?op=host-get-interface-list`, {
                    method: "POST",
                    body: JSON.stringify({
                        hostid: "<?= $hostId ?>",
                        all: true
                    })
                })
                .then(response => response.json())
                .then(data => {

                    if (data.status === "success") {
                        selectInterfaceList.innerHTML = "<option value='null'><-- Selecione uma interface --></option>";
                        data.data.forEach(item => {
                            let option = document.createElement("option");
                            option.value = item.graphid;
                            option.innerHTML = item.interface;

                            selectInterfaceList.append(option);
                        })
                    }
                })
        }

        selectInterfaceList.addEventListener("change", () => {
            if (selectInterfaceList.value != "null") {
                loadChartInterface(selectInterfaceList.value);
            }
        })

        function loadChartInterface(graphid) {
            fetch(`<?= $site_url ?>api/?op=host-get-interface-chart`, {
                    method: "POST",
                    body: JSON.stringify({
                        hostid: "<?= $hostId ?>",
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
            // Remove gráfico anterior se existir
            const oldCanvas = document.getElementById('interfaceChartCanvas');
            if (oldCanvas) {
                oldCanvas.remove();
            }

            // Cria container para o novo gráfico
            const container = document.getElementById('consumoContainer');
            const canvas = document.createElement('canvas');
            canvas.id = 'interfaceChartCanvas';
            canvas.height = 300;
            container.appendChild(canvas);

            // Processa os dados
            const values = chartData.values;
            const timestamps = values.map(item => new Date(item.timestamp * 1000));
            const numericValues = values.map(item => parseFloat(item.value));

            // Configuração dos labels otimizados
            const labelInterval = Math.max(1, Math.floor(values.length / 10)); // Mostrar ~10 labels
            const displayLabels = timestamps.map((date, index) =>
                index % labelInterval === 0 ? formatTimeLabel(date) : ''
            );

            // Cria o gráfico
            const ctx = canvas.getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: displayLabels,
                    datasets: [{
                        label: chartData.name,
                        data: numericValues,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Bytes'
                            },
                            ticks: {
                                callback: function(value) {
                                    return formatBytes(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
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
                        }
                    }
                }
            });
        }

        // Funções auxiliares
        function formatTimeLabel(date) {
            return date.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</div>