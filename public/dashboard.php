<?php
// ==================================================================
// PARTE 1: BUSCAR TODOS OS DADOS (PHP)
// ==================================================================

require_once '../config/database.php';

// --- DADOS DE ESTOQUE (KPIs e Gráfico) ---
$kpiEstoque = ['valorTotal' => 0, 'totalItens' => 0, 'produtosDiferentes' => 0];
$nomesProdutos = [];
$quantidadesProdutos = [];

// --- DADOS DE VENDAS (KPIs e Gráfico) ---
$kpiVendas = ['faturamentoTotal' => 0, 'numeroDeVendas' => 0, 'ticketMedio' => 0];
$nomesClientes = [];
$totalVendidoPorCliente = [];

// --- DADOS DE SERVIÇOS (KPIs e Gráfico) ---
$kpiServicos = ['faturamentoTotal' => 0, 'numeroDeServicos' => 0, 'ticketMedio' => 0];
$nomesClientesServicos = [];
$totalServicoPorCliente = [];

$erro_db = null;

try {
    $database = new Database();
    $pdo = $database->getConnection();

    if ($pdo) {
        // 1. BUSCAR DADOS DE ESTOQUE
        $stmtEstoque = $pdo->prepare("SELECT nome, quantidade, preco FROM produtos WHERE ativo = 1 ORDER BY quantidade DESC");
        $stmtEstoque->execute();
        $produtos = $stmtEstoque->fetchAll(PDO::FETCH_ASSOC);
        $kpiEstoque['produtosDiferentes'] = count($produtos);
        foreach ($produtos as $produto) {
            $nomesProdutos[] = $produto['nome'];
            $quantidadesProdutos[] = (int)$produto['quantidade'];
            $kpiEstoque['totalItens'] += (int)$produto['quantidade'];
            $kpiEstoque['valorTotal'] += (float)$produto['preco'] * (int)$produto['quantidade'];
        }

        // 2. BUSCAR DADOS DE VENDAS
        $stmtVendas = $pdo->prepare("SELECT cliente_nome, valor_total FROM vendas");
        $stmtVendas->execute();
        $vendas = $stmtVendas->fetchAll(PDO::FETCH_ASSOC);
        $kpiVendas['numeroDeVendas'] = count($vendas);
        $vendasAgrupadas = [];
        foreach ($vendas as $venda) {
            $kpiVendas['faturamentoTotal'] += (float)$venda['valor_total'];
            $cliente = $venda['cliente_nome'];
            if (!isset($vendasAgrupadas[$cliente])) { $vendasAgrupadas[$cliente] = 0; }
            $vendasAgrupadas[$cliente] += (float)$venda['valor_total'];
        }
        if ($kpiVendas['numeroDeVendas'] > 0) {
            $kpiVendas['ticketMedio'] = $kpiVendas['faturamentoTotal'] / $kpiVendas['numeroDeVendas'];
        }
        arsort($vendasAgrupadas);
        $nomesClientes = array_keys($vendasAgrupadas);
        $totalVendidoPorCliente = array_values($vendasAgrupadas);

        // 3. BUSCAR DADOS DE SERVIÇOS (NOVO)
        $stmtServicos = $pdo->prepare("SELECT cliente_nome, valor FROM servicos");
        $stmtServicos->execute();
        $servicos = $stmtServicos->fetchAll(PDO::FETCH_ASSOC);
        $kpiServicos['numeroDeServicos'] = count($servicos);
        $servicosAgrupados = [];
        foreach ($servicos as $servico) {
            $kpiServicos['faturamentoTotal'] += (float)$servico['valor'];
            $cliente = $servico['cliente_nome'];
            if (!isset($servicosAgrupados[$cliente])) { $servicosAgrupados[$cliente] = 0; }
            $servicosAgrupados[$cliente] += (float)$servico['valor'];
        }
        if ($kpiServicos['numeroDeServicos'] > 0) {
            $kpiServicos['ticketMedio'] = $kpiServicos['faturamentoTotal'] / $kpiServicos['numeroDeServicos'];
        }
        arsort($servicosAgrupados);
        $nomesClientesServicos = array_keys($servicosAgrupados);
        $totalServicoPorCliente = array_values($servicosAgrupados);

    } else {
        $erro_db = "Falha ao obter a conexão com o banco de dados.";
    }

} catch (Exception $e) {
    $erro_db = "Erro ao processar dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* Estilos dos cards (KPIs  ) */
        .kpi-container { display: flex; gap: 20px; justify-content: space-around; margin-bottom: 40px; flex-wrap: wrap; }
        .kpi-card { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); flex-grow: 1; text-align: center; min-width: 250px; }
        .kpi-card i { font-size: 2.5em; margin-bottom: 10px; }
        .kpi-card .kpi-title { font-size: 1em; color: #666; }
        .kpi-card .kpi-value { font-size: 2em; font-weight: bold; color: #333; }
        .icon-money { color: #28a745; } .icon-box { color: #17a2b8; } .icon-list { color: #ffc107; }
        .icon-cash-register { color: #fd7e14; } .icon-receipt { color: #6f42c1; } .icon-tools { color: #3498db; }

        /* Estilos para os botões de controle */
        .chart-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .chart-views-controls button { padding: 10px 20px; font-size: 16px; cursor: pointer; border: 1px solid #ccc; background-color: #f0f0f0; border-radius: 5px; margin: 0 5px; transition: background-color 0.3s, color 0.3s; }
        .chart-views-controls button.active { background-color: #007bff; color: white; border-color: #007bff; }
        
        /* Estilo para o dropdown de ordenação minimalista */
        .dropdown { position: relative; display: inline-block; }
        .dropdown-btn { background-color: #6c757d; color: white; padding: 10px 20px; font-size: 16px; border: none; cursor: pointer; border-radius: 5px; }
        .dropdown-content { display: none; position: absolute; background-color: #f1f1f1; min-width: 160px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1; right: 0; }
        .dropdown-content a { color: black; padding: 12px 16px; text-decoration: none; display: block; cursor: pointer; }
        .dropdown-content a:hover { background-color: #ddd; }
        .dropdown:hover .dropdown-content { display: block; }
        .dropdown:hover .dropdown-btn { background-color: #5a6268; }

        /* Container para os dois gráficos */
        .chart-wrapper { display: flex; gap: 20px; align-items: stretch; height: 400px; }
        .main-chart-container { flex: 3; min-width: 0; }
        .pie-chart-container { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    </style>
</head>
<body class="dashboard-page">
    <div class="sidebar-dashboard">
        <h2>Menu</h2>
        <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Início</a>
        <a href="clientes.php"><i class="fas fa-users"></i> Clientes</a>
        <a href="vendas.php"><i class="fas fa-shopping-cart"></i> Venda</a>
        <a href="servicos.php"><i class="fas fa-tools"></i> Serviços</a>
        <a href="estoque.php"><i class="fas fa-boxes"></i> Estoque</a>
        <div class="sidebar-logout">
            <a href="logout.php" class="btn-logout-dashboard"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>

    <div class="main-dashboard">
        <h1 id="main-title">Visão Geral do Estoque</h1>
        
        <?php if ($erro_db): ?>
            <div style="padding: 15px; background-color: #ffdddd; border-left: 6px solid #f44336; margin: 20px; color: #58151c;">
                <strong>Erro!</strong> <?php echo htmlspecialchars($erro_db); ?>
            </div>
        <?php else: ?>
            <div class="kpi-container">
                <div class="kpi-card" id="kpi-1"></div>
                <div class="kpi-card" id="kpi-2"></div>
                <div class="kpi-card" id="kpi-3"></div>
            </div>
        <?php endif; ?>

        <div style="width: 100%; max-width: 1200px; margin: 40px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div class="chart-controls">
                <div class="chart-views-controls">
                    <button id="btnEstoque" class="active">Visão de Estoque</button>
                    <button id="btnVendas">Vendas por Cliente</button>
                    <button id="btnServicos">Serviços por Cliente</button> <!-- BOTÃO NOVO -->
                </div>
                <div class="dropdown">
                    <button class="dropdown-btn"><i class="fas fa-sort"></i> Ordenar</button>
                    <div class="dropdown-content">
                        <a data-sort="maior">Maior Valor</a>
                        <a data-sort="menor">Menor Valor</a>
                        <a data-sort="nome">Nome (A-Z)</a>
                    </div>
                </div>
            </div>
            <div class="chart-wrapper">
                <div class="main-chart-container"><canvas id="graficoBarras"></canvas></div>
                <div class="pie-chart-container"><canvas id="graficoPizza"></canvas></div>
            </div>
        </div>
    </div>

    <script>
        // ==================================================================
        // PARTE 3: SCRIPT DO GRÁFICO E KPIS DINÂMICOS (JAVASCRIPT)
        // ==================================================================

        function gerarCores(quantidade) {
            const cores = [];
            for (let i = 0; i < quantidade; i++) { const hue = (360 / quantidade) * i; cores.push(`hsla(${hue}, 70%, 60%, 0.7)`); }
            return cores;
        }

        // --- DADOS DE ESTOQUE ---
        const dadosEstoque = {
            labels: <?php echo json_encode($nomesProdutos); ?>, data: <?php echo json_encode($quantidadesProdutos); ?>, cores: gerarCores(<?php echo count($nomesProdutos); ?>),
            label: 'Quantidade em Estoque', title: 'Quantidade de Produtos em Estoque'
        };
        const kpisEstoque = {
            title: 'Visão Geral do Estoque',
            kpi1: { icon: 'fas fa-dollar-sign icon-money', title: 'Valor Total do Estoque', value: '<?php echo 'R$ ' . number_format($kpiEstoque['valorTotal'], 2, ',', '.'); ?>' },
            kpi2: { icon: 'fas fa-box-open icon-box', title: 'Total de Itens no Estoque', value: '<?php echo number_format($kpiEstoque['totalItens'], 0, ',', '.'); ?>' },
            kpi3: { icon: 'fas fa-list-ol icon-list', title: 'Produtos Diferentes', value: '<?php echo $kpiEstoque['produtosDiferentes']; ?>' }
        };

        // --- DADOS DE VENDAS ---
        const dadosVendas = {
            labels: <?php echo json_encode($nomesClientes); ?>, data: <?php echo json_encode($totalVendidoPorCliente); ?>, cores: gerarCores(<?php echo count($nomesClientes); ?>),
            label: 'Total Vendido (R$)', title: 'Total de Vendas por Cliente'
        };
        const kpisVendas = {
            title: 'Visão Geral de Vendas',
            kpi1: { icon: 'fas fa-cash-register icon-cash-register', title: 'Faturamento Total', value: '<?php echo 'R$ ' . number_format($kpiVendas['faturamentoTotal'], 2, ',', '.'); ?>' },
            kpi2: { icon: 'fas fa-receipt icon-receipt', title: 'Total de Vendas', value: '<?php echo $kpiVendas['numeroDeVendas']; ?>' },
            kpi3: { icon: 'fas fa-chart-line icon-money', title: 'Ticket Médio', value: '<?php echo 'R$ ' . number_format($kpiVendas['ticketMedio'], 2, ',', '.'); ?>' }
        };

        // --- DADOS DE SERVIÇOS (NOVO) ---
        const dadosServicos = {
            labels: <?php echo json_encode($nomesClientesServicos); ?>, data: <?php echo json_encode($totalServicoPorCliente); ?>, cores: gerarCores(<?php echo count($nomesClientesServicos); ?>),
            label: 'Total de Serviços (R$)', title: 'Total de Serviços por Cliente'
        };
        const kpisServicos = {
            title: 'Visão Geral de Serviços',
            kpi1: { icon: 'fas fa-tools icon-tools', title: 'Faturamento de Serviços', value: '<?php echo 'R$ ' . number_format($kpiServicos['faturamentoTotal'], 2, ',', '.'); ?>' },
            kpi2: { icon: 'fas fa-handshake icon-receipt', title: 'Total de Serviços', value: '<?php echo $kpiServicos['numeroDeServicos']; ?>' },
            kpi3: { icon: 'fas fa-chart-line icon-money', title: 'Ticket Médio (Serviços)', value: '<?php echo 'R$ ' . number_format($kpiServicos['ticketMedio'], 2, ',', '.'); ?>' }
        };

        // --- ELEMENTOS DO DOM ---
        const mainTitle = document.getElementById('main-title');
        const kpi1Div = document.getElementById('kpi-1');
        const kpi2Div = document.getElementById('kpi-2');
        const kpi3Div = document.getElementById('kpi-3');
        const ctxBarras = document.getElementById('graficoBarras').getContext('2d');
        const ctxPizza = document.getElementById('graficoPizza').getContext('2d');
        const btnEstoque = document.getElementById('btnEstoque');
        const btnVendas = document.getElementById('btnVendas');
        const btnServicos = document.getElementById('btnServicos'); // BOTÃO NOVO
        const dropdownContent = document.querySelector('.dropdown-content');

        let graficoBarras, graficoPizza;
        let dadosAtuais;

        // --- FUNÇÕES DE ATUALIZAÇÃO ---
        function updateKPIs(kpis) {
            mainTitle.textContent = kpis.title;
            kpi1Div.innerHTML = `<i class="${kpis.kpi1.icon}"></i><div class="kpi-title">${kpis.kpi1.title}</div><div class="kpi-value">${kpis.kpi1.value}</div>`;
            kpi2Div.innerHTML = `<i class="${kpis.kpi2.icon}"></i><div class="kpi-title">${kpis.kpi2.title}</div><div class="kpi-value">${kpis.kpi2.value}</div>`;
            kpi3Div.innerHTML = `<i class="${kpis.kpi3.icon}"></i><div class="kpi-title">${kpis.kpi3.title}</div><div class="kpi-value">${kpis.kpi3.value}</div>`;
        }

        function ordenarDados(tipo) {
            let pares = dadosAtuais.labels.map((label, index) => [label, dadosAtuais.data[index], dadosAtuais.cores[index]]);
            switch (tipo) {
                case 'maior': pares.sort((a, b) => b[1] - a[1]); break;
                case 'menor': pares.sort((a, b) => a[1] - b[1]); break;
                case 'nome': pares.sort((a, b) => a[0].localeCompare(b[0])); break;
            }
            dadosAtuais.labels = pares.map(par => par[0]);
            dadosAtuais.data = pares.map(par => par[1]);
            dadosAtuais.cores = pares.map(par => par[2]);
        }

        function atualizarGraficos() {
            if (graficoBarras) graficoBarras.destroy();
            if (graficoPizza) graficoPizza.destroy();

            graficoBarras = new Chart(ctxBarras, {
                type: 'bar', data: { labels: dadosAtuais.labels, datasets: [{ label: dadosAtuais.label, data: dadosAtuais.data, backgroundColor: dadosAtuais.cores, borderColor: dadosAtuais.cores.map(cor => cor.replace('0.7', '1')), borderWidth: 1 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: dadosAtuais.title, font: { size: 18 } }, legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
            graficoPizza = new Chart(ctxPizza, {
                type: 'pie', data: { labels: dadosAtuais.labels, datasets: [{ data: dadosAtuais.data, backgroundColor: dadosAtuais.cores, hoverOffset: 4 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Proporção (%)' }, legend: { display: true, position: 'bottom', align: 'center', labels: { boxWidth: 15, padding: 20 } } } }
            });
        }

        function mudarVisao(tipo) {
            if (tipo === 'estoque') {
                dadosAtuais = { ...dadosEstoque };
                updateKPIs(kpisEstoque);
            } else if (tipo === 'vendas') {
                dadosAtuais = { ...dadosVendas };
                updateKPIs(kpisVendas);
            } else { // 'servicos'
                dadosAtuais = { ...dadosServicos };
                updateKPIs(kpisServicos);
            }
            ordenarDados('maior');
            atualizarGraficos();
            // Gerencia a classe 'active' para os três botões
            btnEstoque.classList.toggle('active', tipo === 'estoque');
            btnVendas.classList.toggle('active', tipo === 'vendas');
            btnServicos.classList.toggle('active', tipo === 'servicos');
        }

        // --- EVENT LISTENERS ---
        btnEstoque.addEventListener('click', () => mudarVisao('estoque'));
        btnVendas.addEventListener('click', () => mudarVisao('vendas'));
        btnServicos.addEventListener('click', () => mudarVisao('servicos')); // EVENTO NOVO
        dropdownContent.addEventListener('click', (e) => {
            if (e.target.tagName === 'A') {
                ordenarDados(e.target.getAttribute('data-sort'));
                atualizarGraficos();
            }
        });

        // --- INICIALIZAÇÃO ---
        mudarVisao('estoque');
    </script>
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>
