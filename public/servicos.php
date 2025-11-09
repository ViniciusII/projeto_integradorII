<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Adapte o caminho se o seu arquivo de conexão for diferente
// Se você moveu o 'conexao.php' para a raiz, o caminho pode ser '../conexao.php'
require_once '../config/database.php'; 

$mensagem = ""; 
$servicos_registrados = []; 
$clientes = []; 

$user_id = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();

// Lógica para REGISTRAR um novo serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $descricao_servico = trim($_POST['descricao_servico']);
        $valor = (float)$_POST['valor'];
        $cliente_id = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
        $cliente_nome = !empty($_POST['cliente_nome']) ? trim($_POST['cliente_nome']) : 'Consumidor';
        $forma_pagamento = $_POST['forma_pagamento'] ?? 'Dinheiro';

        if (empty($descricao_servico)) throw new Exception("A descrição do serviço é obrigatória.");
        if ($valor <= 0) throw new Exception("O valor do serviço deve ser maior que zero.");

        $stmt = $conn->prepare("
            INSERT INTO servicos (descricao_servico, valor, cliente_nome, cliente_id, forma_pagamento, user_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$descricao_servico, $valor, $cliente_nome, $cliente_id, $forma_pagamento, $user_id]);

        $mensagem = "✅ Serviço registrado com sucesso!";
    } catch (Exception $e) {
        $mensagem = "❌ Erro ao registrar o serviço: " . $e->getMessage();
    }
}

// Buscar a lista de clientes para o campo de autocompletar
try {
    $stmtClientes = $conn->prepare("SELECT id, nome FROM clientes WHERE user_id = ?");
    $stmtClientes->execute([$user_id]);
    $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Lida com o erro, se necessário
}


// Buscar o histórico de serviços para exibir na tabela
try {
    $stmtServicos = $conn->prepare("
        SELECT id, descricao_servico, valor, cliente_nome, forma_pagamento, data_servico
        FROM servicos
        WHERE user_id = ?
        ORDER BY data_servico DESC
    ");
    $stmtServicos->execute([$user_id]);
    $servicos_registrados = $stmtServicos->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Lida com o erro, se necessário
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
    <style>
        /* 2. ESTILO PARA CLIENTES INATIVOS */
        .cliente-inativo {
            background-color: #fcf8e3 !important; /* Fundo amarelado suave */
            opacity: 0.8;
        }
        .cliente-inativo td {
            text-decoration: line-through;
            color: #6c757d;
        }
    </style>
</head><body class="vendas-page"> <!-- Pode usar a mesma classe de layout -->

<div class="sidebar-dashboard">
    <h2>Menu</h2>
    <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Início</a>
    <a href="clientes.php"><i class="fas fa-users"></i> Clientes</a>
    <a href="vendas.php"><i class="fas fa-shopping-cart"></i> Venda</a>
    <!-- Adicione o link para a nova página de serviços aqui -->
    <a href="servicos.php" class="active"><i class="fas fa-tools"></i> Serviços</a>
    <a href="estoque.php"><i class="fas fa-boxes"></i> Estoque</a>
    <div class="sidebar-logout">
        <a href="logout.php" class="btn-logout-dashboard"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main-dashboard"> 
    <div class="container" style="margin-top: 60px; padding-bottom: 20px;"> 
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Registrar Novo Serviço</h5>
            </div>
            <div class="card-body">
                <?php if ($mensagem ): ?>
                    <div class="alert <?= strpos($mensagem,'✅')!==false ? 'alert-success' : 'alert-danger' ?>">
                        <?= htmlspecialchars($mensagem) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="descricao_servico" class="form-label">Descrição do Serviço</label>
                        <input type="text" name="descricao_servico" class="form-control" placeholder="Ex: Instalação de porta, reparo elétrico" required>
                    </div>
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor (R$)</label>
                        <input type="number" name="valor" step="0.01" min="0.01" class="form-control" placeholder="Ex: 150.50" required>
                    </div>

                    <div class="mb-3" style="position: relative;">
                        <label for="cliente_nome" class="form-label">Cliente (Opcional)</label>
                        <input type="text" name="cliente_nome" id="cliente_nome" class="form-control" placeholder="Digite o nome do cliente ou deixe em branco para 'Consumidor'">
                        <input type="hidden" name="cliente_id" id="cliente_id">
                        <div id="sugestoes_cliente" style="position:absolute; background:#fff; width:100%; border:1px solid #ccc; z-index:10; display:none;"></div>
                    </div>

                    <div class="mb-3">
                        <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                        <select name="forma_pagamento" class="form-select" required>
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão Débito">Cartão Débito</option>
                            <option value="Cartão Crédito">Cartão Crédito</option>
                            <option value="PIX">PIX</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Registrar Serviço</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Histórico de Serviços</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Descrição do Serviço</th>
                            <th>Valor (R$)</th>
                            <th>Cliente</th>
                            <th>Forma Pagamento</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($servicos_registrados) > 0): ?> 
                            <?php foreach($servicos_registrados as $servico): ?>
                                <tr>
                                    <td><?= $servico['id'] ?></td>
                                    <td><?= htmlspecialchars($servico['descricao_servico']) ?></td>
                                    <td><?= number_format($servico['valor'], 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($servico['cliente_nome']) ?></td>
                                    <td><?= htmlspecialchars($servico['forma_pagamento']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($servico['data_servico'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">Nenhum serviço registrado ainda.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// O JavaScript para autocompletar o cliente é o mesmo da página de vendas
const inputCliente = document.getElementById('cliente_nome');
const inputClienteId = document.getElementById('cliente_id');
const sugestoesCliente = document.getElementById('sugestoes_cliente');

const clientes = <?= json_encode($clientes) ?>;

inputCliente.addEventListener('input', () => {
    const query = inputCliente.value.toLowerCase();
    inputClienteId.value = '';

    const resultados = clientes.filter(c => c.nome.toLowerCase().includes(query));

    if (resultados.length > 0 && query.length > 0) {
        sugestoesCliente.innerHTML = resultados.map(c => 
            `<div style="padding:5px; cursor:pointer;" 
                 onclick="selecionarCliente(${c.id}, '${c.nome.replace(/'/g, "\\'")}')">
                 ${c.nome}
               </div>`).join('');
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

document.addEventListener('click', (e) => {
    if (!sugestoesCliente.contains(e.target) && e.target !== inputCliente) {
        sugestoesCliente.style.display = 'none';
    }
});
</script>
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>
