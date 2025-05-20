<?php
$result = $_SESSION['install_result'] ?? null;
$missingPackages = $_SESSION['missing_packages'] ?? [];


// Verificar se há pacotes faltantes
$composerCommand = '';
if (!empty($missingPackages)) {
    $composerCommand = 'composer require ' . implode(' ', array_keys($missingPackages));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação Concluída</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-box {
            background-color: #e2e3e5;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0069d9;
        }
    </style>
</head>

<body>
    <h1>Resultado da Instalação</h1>

    <?php if ($result['status'] === 'success'): ?>
        <div class="success">
            <h2>✅ Instalação concluída com sucesso!</h2>
            <p><?= htmlspecialchars($result['message']) ?></p>
        </div>
    <?php elseif ($result['status'] === 'warning'): ?>
        <div class="warning">
            <h2>⚠️ Instalação concluída com avisos</h2>
            <p><?= htmlspecialchars($result['message']) ?></p>
        </div>
    <?php else: ?>
        <div class="error">
            <h2>❌ Erro na instalação</h2>
            <p><?= htmlspecialchars($result['message']) ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($result['root_account'])): ?>
        <div class="info-box">
            <h2>Conta Root Criada</h2>
            <p><strong>Usuário:</strong> <?= htmlspecialchars($result['root_account']['user']) ?></p>
            <p><strong>Senha:</strong> <?= htmlspecialchars($result['root_account']['pass']) ?></p>
            <p><em>Esta senha foi gerada automaticamente e salva no arquivo config.json</em></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($missingPackages)): ?>
        <div class="warning">
            <h2>Pacotes Composer Faltantes</h2>
            <p>Os seguintes pacotes não puderam ser instalados automaticamente:</p>
            <ul>
                <?php foreach ($missingPackages as $package => $description): ?>
                    <li><strong><?= htmlspecialchars($package) ?></strong>: <?= htmlspecialchars($description) ?></li>
                <?php endforeach; ?>
            </ul>

            <h3>Como resolver:</h3>
            <p>Execute o seguinte comando no terminal, no diretório do seu projeto:</p>
            <pre id="composerCommand"><?= htmlspecialchars($composerCommand) ?></pre>

            <button onclick="copyToClipboard()">Copiar Comando</button>
            <span id="copyStatus" style="margin-left: 10px;"></span>

            <p>Após executar o comando, atualize esta página para verificar se os pacotes foram instalados corretamente.</p>
        </div>
    <?php endif; ?>

    <?php if ($result['database_initialized'] ?? false): ?>
        <div class="info-box">
            <h2>Banco de Dados</h2>
            <p>As tabelas do banco de dados foram criadas com sucesso.</p>
        </div>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <a href="<?= $site_url ?>"><button>Acessar o Sistema</button></a>
    </div>

    <script>
        function copyToClipboard() {
            const command = document.getElementById('composerCommand').textContent;
            navigator.clipboard.writeText(command).then(() => {
                document.getElementById('copyStatus').textContent = 'Copiado!';
                setTimeout(() => {
                    document.getElementById('copyStatus').textContent = '';
                }, 2000);
            }).catch(err => {
                document.getElementById('copyStatus').textContent = 'Erro ao copiar';
            });
        }
    </script>
</body>

</html>