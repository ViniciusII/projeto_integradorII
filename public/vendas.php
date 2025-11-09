<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Adapte o caminho se o seu arquivo de conexão for diferente
require_once '../config/database.php'; 

$mensagem = ""; 
$vendas = []; 
$produtos = [];
$clientes = []; 

$user_id = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $produto_id = (int)$_POST['produto_id']; 
        $quantidade = (int)$_POST['quantidade'];
        $cliente_id = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
        $cliente_nome = !empty($_POST['cliente_nome']) ? trim($_POST['cliente_nome']) : 'Consumidor';
        $forma_pagamento = $_POST['forma_pagamento'] ?? 'Dinheiro';
        
        $stmt = $conn->prepare("SELECT nome, preco, quantidade FROM produtos WHERE id = ? AND user_id = ?");
        $stmt->execute([$produto_id, $user_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) throw new Exception("Produto não encontrado. Selecione um produto válido.");
        if ($quantidade <= 0) throw new Exception("Quantidade inválida.");
        if ($quantidade > $produto['quantidade']) throw new Exception("Estoque insuficiente. Disponível: {$produto['quantidade']}.");

        $valor_total = $produto['preco'] * $quantidade;
        
        $conn->beginTransaction();
        
        $stmtInsert = $conn->prepare("
            INSERT INTO vendas (produto_id, quantidade, valor_total, cliente_nome, cliente_id, forma_pagamento, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtInsert->execute([$produto_id, $quantidade, $valor_total, $cliente_nome, $cliente_id, $forma_pagamento, $user_id]);
        
        $stmtUpdate = $conn->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ? AND user_id = ?");
        $stmtUpdate->execute([$quantidade, $produto_id, $user_id]);
        
        $conn->commit();

        $mensagem = "✅ Venda registrada com sucesso!";
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $mensagem = "❌ Erro ao registrar a venda: " . $e->getMessage();
    }
}

// Buscar dados para os formulários e histórico
$stmtProdutos = $conn->prepare("SELECT id, nome, preco, quantidade FROM produtos WHERE user_id = ? AND ativo = 1");
$stmtProdutos->execute([$user_id]);
$produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);

$stmtClientes = $conn->prepare("SELECT id, nome FROM clientes WHERE user_id = ?");
$stmtClientes->execute([$user_id]);
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

$stmtVendas = $conn->prepare("
    SELECT v.id, p.nome as produto, v.quantidade, v.valor_total, v.cliente_nome, v.forma_pagamento, v.data_venda
    FROM vendas v
    JOIN produtos p ON v.produto_id = p.id
    WHERE v.user_id = ?
    ORDER BY v.data_venda DESC
");
$stmtVendas->execute([$user_id]);
$vendas = $stmtVendas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venda</title>

    <!-- FONTES E ÍCONES (AGORA CORRETO) -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
</head>
<body class="vendas-page">

<div class="sidebar-dashboard">
    <h2>Menu</h2>
    <a href="dashboard.php"><i class="fas fa-home"></i> Início</a>
    <a href="clientes.php"><i class="fas fa-users"></i> Clientes</a>
    <a href="vendas.php" class="active"><i class="fas fa-shopping-cart"></i> Venda</a>
    <a href="servicos.php"><i class="fas fa-tools"></i> Serviços</a>
    <a href="estoque.php"><i class="fas fa-boxes"></i> Estoque</a>
    <div class="sidebar-logout">
        <a href="logout.php" class="btn-logout-dashboard"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main-dashboard"> 
    <div class="container" style="margin-top: 60px; padding-bottom: 20px;"> 
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Registrar Nova Venda</h5>
            </div>
            <div class="card-body">
                <?php if ($mensagem  ): ?>
                    <div class="alert <?= strpos($mensagem,'✅')!==false ? 'alert-success' : 'alert-danger' ?>">
                        <?= htmlspecialchars($mensagem) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3" style="position: relative;">
                        <label for="produto_nome" class="form-label">Produto</label>
                        <input type="text" id="produto_nome" class="form-control" placeholder="Digite o nome do produto" required>
                        <input type="hidden" name="produto_id" id="produto_id_hidden" required> 
                        <div id="sugestoes_produto" style="position:absolute; background:#fff; width:100%; border:1px solid #ccc; z-index:10; display:none;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" name="quantidade" min="1" class="form-control" required>
                    </div>
                    <div class="mb-3" style="position: relative;">
                        <label for="cliente_nome" class="form-label">Cliente</label>
                        <input type="text" name="cliente_nome" id="cliente_nome" class="form-control" placeholder="Digite o nome do cliente">
                        <input type="hidden" name="cliente_id" id="cliente_id">
                        <div id="sugestoes_cliente" style="position:absolute; background:#fff; width:100%; border:1px solid #ccc; z-index:10; display:none;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                        <select name="forma_pagamento" class="form-select" required>
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão Débito">Cartão Débito</option>
                            <option value="Cartão Crédito">Cartão Crédito</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Registrar Venda</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Histórico de Vendas</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th><th>Produto</th><th>Quantidade</th><th>Valor Total (R$)</th><th>Cliente</th><th>Forma Pagamento</th><th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($vendas) > 0): ?> 
                            <?php foreach($vendas as $v): ?>
                                <tr>
                                    <td><?= $v['id'] ?></td>
                                    <td><?= htmlspecialchars($v['produto']) ?></td>
                                    <td><?= $v['quantidade'] ?></td>
                                    <td><?= number_format($v['valor_total'],2,',','.') ?></td>
                                    <td><?= htmlspecialchars($v['cliente_nome']) ?></td>
                                    <td><?= htmlspecialchars($v['forma_pagamento']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($v['data_venda'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">Nenhuma venda registrada ainda.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 

<script>
// --- SCRIPT DE AUTOCOMPLETAR PRODUTO ---
const inputProduto = document.getElementById('produto_nome');
const inputProdutoId = document.getElementById('produto_id_hidden');
const sugestoesProduto = document.getElementById('sugestoes_produto');
const produtos = <?= json_encode($produtos) ?>;

inputProduto.addEventListener('input', () => {
    const query = inputProduto.value.toLowerCase();
    inputProdutoId.value = '';
    const resultados = produtos.filter(p => p.nome.toLowerCase().includes(query));

    if (resultados.length > 0 && query.length > 0) {
        sugestoesProduto.innerHTML = resultados.map(p => {
            const estoque = `(Estoque: ${p.quantidade})`;
            const preco = `R$ ${parseFloat(p.preco).toFixed(2).replace('.', ',')}`;
            return `<div style="padding:5px; cursor:pointer;" onclick="selecionarProduto(${p.id}, '${p.nome.replace(/'/g, "\\'")}')">${p.nome} - ${preco} ${estoque}</div>`;
        }).join('');
        sugestoesProduto.style.display = 'block';
    } else {
        sugestoesProduto.style.display = 'none';
    }
});

function selecionarProduto(id, nome) {
    inputProduto.value = nome;
    inputProdutoId.value = id;
    sugestoesProduto.style.display = 'none';
}

// --- SCRIPT DE AUTOCOMPLETAR CLIENTE ---
const inputCliente = document.getElementById('cliente_nome');
const inputClienteId = document.getElementById('cliente_id');
const sugestoesCliente = document.getElementById('sugestoes_cliente');
const clientes = <?= json_encode($clientes) ?>;

inputCliente.addEventListener('input', () => {
    const query = inputCliente.value.toLowerCase();
    inputClienteId.value = '';
    const resultados = clientes.filter(c => c.nome.toLowerCase().includes(query));

    if (resultados.length > 0 && query.length > 0) {
        sugestoesCliente.innerHTML = resultados.map(c => `<div style="padding:5px; cursor:pointer;" onclick="selecionarCliente(${c.id}, '${c.nome.replace(/'/g, "\\'")}')">${c.nome}</div>`).join('');
        sugestoesCliente.style.display = 'block';
    } else {
        sugestoesCliente.style.display = 'none';
    }
});

function selecionarCliente(id, nome) {
    inputCliente.value = nome;
    inputClienteId.value = id;
    sugestoesCliente.style.display = 'none';
}

// --- FECHAR SUGESTÕES AO CLICAR FORA ---
document.addEventListener('click', (e) => {
    if (!sugestoesProduto.contains(e.target) && e.target !== inputProduto) {
        sugestoesProduto.style.display = 'none';
    }
    if (!sugestoesCliente.contains(e.target) && e.target !== inputCliente) {
        sugestoesCliente.style.display = 'none';
    }
});
</script>

<script src="../assets/js/dark-mode.js"></script>

</body>
</html>
