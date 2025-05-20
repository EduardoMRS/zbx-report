<?php
$dedicatedId = $_GET['dedicatedId'] ?? null;
if (!$dedicatedId) {
    die('ID do Cliente não informado!');
}

// Busca os dados do cliente dedicado
$dedicated = mysql_select_in_array("dedicado", "id='$dedicatedId'") ?? null;
if (!$dedicated) {
    die('Cliente não encontrado!');
}

$hostid = $dedicated['hostid'];
$clientName = htmlspecialchars($dedicated['name']);

// Calcula datas padrão (últimos 30 dias)
$defaultStart = date('Y-m-d', strtotime('-30 days'));
$defaultEnd = date('Y-m-d');
?>

<div class="modal-page">
    <style>
        .calendar-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .date-picker-container {
            display: flex;
            gap: 20px;
            margin-bottom: 8px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .date-picker {
            flex: 1;
            min-width: 200px;
        }

        .chart-container {
            margin-top: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .client-header {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .date-input {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 100%;
        }

        .action-buttons {
            width: 100%;
            display: flex;
            justify-content: center;
            align-content: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
            gap: 6px;
        }

        .btn-download {
            background-color: #28a745;
            color: white;
        }

        .btn-download:hover {
            background-color: #218838;
            color: white;
        }
    </style>

    <div class="calendar-container">
        <div class="client-header">
            <h3><i class="fas fa-chart-line me-2"></i> Relatório Histórico</h3>
            <p class="mb-0"><i class="fas fa-user-tie me-2"></i> Cliente: <?= $clientName ?></p>
            <p class="mb-0"><i class="fas fa-server me-2"></i> Host ID: <?= $hostid ?></p>
            <p class="mb-0"><i class="fas fa-network-wired me-2"></i> Interface: <?= htmlspecialchars($dedicated['graphid']) ?></p>
        </div>

        <div class="date-picker-container">
            <div class="date-picker">
                <label for="startDate" class="form-label">Data Inicial</label>
                <input type="date" id="startDate" class="date-input" value="<?= $defaultStart ?>">
            </div>

            <div class="date-picker">
                <label for="endDate" class="form-label">Data Final</label>
                <input type="date" id="endDate" class="date-input" value="<?= $defaultEnd ?>">
            </div>
        </div>
        <div class="action-buttons">
            <button id="loadChartBtn" class="btn btn-primary">
                <i class="fas fa-sync-alt me-2"></i>Carregar Gráfico
            </button>
            <button id="downloadReportBtn" class="btn btn-download" disabled>
                <i class="fas fa-file-pdf me-2"></i>Baixar Relatório
            </button>
            <button id="sendMailBtn" class="btn btn-warning" disabled>
                <i class="fas fa-file-pdf me-2"></i>Enviar Relatório
            </button>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> Selecione um período para visualizar o histórico de consumo.
        </div>

        <div class="chart-container">
            <canvas id="historyChart" height="400"></canvas>
        </div>
    </div>

    <script src="<?= $site_url ?>assets/js/chart.js"></script>
    <script>
        historyChart = null;

        document.getElementById('loadChartBtn').addEventListener('click', function() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!startDate || !endDate) {
                alert('Por favor, selecione ambas as datas');
                return;
            }

            // Converte datas para timestamp Unix
            const startTimestamp = Math.floor(new Date(startDate).getTime() / 1000);
            const endTimestamp = Math.floor(new Date(endDate).getTime() / 1000) + 86399; // Fim do dia

            if (startTimestamp >= endTimestamp) {
                alert('A data final deve ser posterior à data inicial');
                return;
            }

            loadHistoryChart(startTimestamp, endTimestamp);
        });

        function loadHistoryChart(startTimestamp, endTimestamp) {
            const btn = document.getElementById('loadChartBtn');
            const downloadBtn = document.getElementById('downloadReportBtn');
            const sendMailBtn = document.getElementById('sendMailBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...';
            btn.disabled = true;
            downloadBtn.disabled = true;
            sendMailBtn.disabled = true;

            fetch(`<?= $site_url ?>api/?op=host-get-interface-chart`, {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        hostid: "<?= $hostid ?>",
                        graphid: "<?= $dedicated['graphid'] ?>",
                        time_from: startTimestamp,
                        time_till: endTimestamp
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        renderHistoryChart(data.data);
                        // Habilita o botão de download após carregar o gráfico
                        downloadBtn.disabled = false;
                        sendMailBtn.disabled = false;
                    } else {
                        alert('Erro ao carregar dados: ' + (data.message || 'Dados não encontrados'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar dados do gráfico');
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        function renderHistoryChart(chartData) {
            const ctx = document.getElementById('historyChart').getContext('2d');

            // Destrói o gráfico anterior se existir
            if (historyChart) {
                historyChart.destroy();
            }

            // Downsample mantendo a relação entre timestamps e valores
            const downsampledData = downsampleData(chartData.values, 2000);

            // Formata os dados para o Chart.js
            const labels = downsampledData.map(item => {
                const date = new Date(item.timestamp * 1000);
                return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR');
            });

            const dataValues = downsampledData.map(item => parseFloat(item.value));

            historyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: chartData.name,
                        data: dataValues,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true,
                        pointRadius: 2
                    }]
                },
                options: {
                    pointBorderWidth: 0,
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            beginAtZero: true,
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
                                    return formatBytes(context.raw);
                                }
                            }
                        },
                        zoom: {
                            zoom: {
                                wheel: {
                                    enabled: true
                                },
                                pinch: {
                                    enabled: true
                                },
                                mode: 'xy'
                            },
                            pan: {
                                enabled: true,
                                mode: 'xy'
                            }
                        }
                    }
                }
            });
        }

        document.getElementById('downloadReportBtn').addEventListener('click', function() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!startDate || !endDate) {
                alert('Por favor, selecione ambas as datas');
                return;
            }

            // Converte datas para timestamp Unix
            const startTimestamp = Math.floor(new Date(startDate).getTime() / 1000);
            const endTimestamp = Math.floor(new Date(endDate).getTime() / 1000) + 86399; // Fim do dia

            if (startTimestamp >= endTimestamp) {
                alert('A data final deve ser posterior à data inicial');
                return;
            }

            downloadReport(startTimestamp, endTimestamp);
        });

        document.getElementById('sendMailBtn').addEventListener('click', function() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!startDate || !endDate) {
                alert('Por favor, selecione ambas as datas');
                return;
            }

            // Converte datas para timestamp Unix
            const startTimestamp = Math.floor(new Date(startDate).getTime() / 1000);
            const endTimestamp = Math.floor(new Date(endDate).getTime() / 1000) + 86399; // Fim do dia

            if (startTimestamp >= endTimestamp) {
                alert('A data final deve ser posterior à data inicial');
                return;
            }

            if (!confirm('Você tem certeza que deseja enviar o relatório por e-mail?')) {
                return;
            }

            fetch(`<?= $site_url ?>api/?op=dedicated-sent-report`, {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        dedicatedId: "<?= $dedicatedId ?>",
                        time_from: startTimestamp,
                        time_till: endTimestamp
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status != "success") {
                        alert('Erro ao enviar relatório: ' + (data.message || 'Erro desconhecido'));
                    } else {
                        alert(data.message || 'Relatório enviado com sucesso!');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao enviar relatório por e-mail');
                });
        });

        function downloadReport(startTimestamp, endTimestamp) {
            const btn = document.getElementById('downloadReportBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Gerando PDF...';
            btn.disabled = true;

            // Cria um formulário dinâmico para enviar via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `<?= $site_url ?>api/?op=host-load-interface-history`;
            form.target = '_blank'; // Abre em nova aba

            // Adiciona campos ao formulário
            const dedicatedIdInput = document.createElement('input');
            dedicatedIdInput.type = 'hidden';
            dedicatedIdInput.name = 'dedicatedId';
            dedicatedIdInput.value = '<?= $dedicatedId ?>';
            form.appendChild(dedicatedIdInput);

            const timeFromInput = document.createElement('input');
            timeFromInput.type = 'hidden';
            timeFromInput.name = 'time_from';
            timeFromInput.value = startTimestamp;
            form.appendChild(timeFromInput);

            const timeTillInput = document.createElement('input');
            timeTillInput.type = 'hidden';
            timeTillInput.name = 'time_till';
            timeTillInput.value = endTimestamp;
            form.appendChild(timeTillInput);

            // Adiciona o formulário ao body e submete
            document.body.appendChild(form);
            form.submit();

            // Remove o formulário após o envio
            setTimeout(() => {
                document.body.removeChild(form);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 1000);
        }

        // Modifique a função downsampleData para manter a estrutura dos objetos originais
        function downsampleData(values, maxPoints = 1000) {
            if (values.length <= maxPoints) return values;

            const step = Math.ceil(values.length / maxPoints);
            const sampledValues = [];

            for (let i = 0; i < values.length; i += step) {
                sampledValues.push({
                    timestamp: values[i].timestamp,
                    value: values[i].value
                });
            }

            return sampledValues;
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        startTimestamp = Math.floor(new Date('<?= $defaultStart ?>').getTime() / 1000);
        endTimestamp = Math.floor(new Date('<?= $defaultEnd ?>').getTime() / 1000) + 86399;
        loadHistoryChart(startTimestamp, endTimestamp);
    </script>
</div>