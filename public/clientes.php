<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php'; 

$user_id = $_SESSION['user_id'];
$q = $_GET['q'] ?? ''; 

$database = new Database();
$conn = $database->getConnection();

// 1. A CONSULTA AGORA BUSCA A COLUNA 'ativo'
$sql = "SELECT id, nome, cpf, email, telefone, data_cadastro, ativo FROM clientes WHERE user_id = ?";
$params = [$user_id];

if ($q) {
    $sql .= " AND (nome LIKE ? OR cpf LIKE ? OR email LIKE ?)";
    $q_like = "%" . $q . "%";
    $params = [$user_id, $q_like, $q_like, $q_like]; 
}

$sql .= " ORDER BY nome ASC";

$clientes = [];
$mensagem_erro = null;

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar clientes: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
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
</head>
<body class="clientes-page">
    <div class="sidebar-dashboard">
        <h2>Menu</h2>
        <a href="dashboard.php"><i class="fas fa-home"></i> Início</a>
        <a href="clientes.php" class="active"><i class="fas fa-users"></i> Clientes</a>
        <a href="vendas.php"><i class="fas fa-shopping-cart"></i> Venda</a>
        <a href="servicos.php"><i class="fas fa-tools"></i> Serviços</a>
        <a href="estoque.php"><i class="fas fa-boxes"></i> Estoque</a>
        <div class="sidebar-logout">
            <a href="logout.php" class="btn-logout-dashboard"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>
    <div class="main-dashboard">
    <?php
    // Bloco de mensagens de sucesso/erro (mantido )
    if (isset($_GET['status']) && isset($_GET['msg'])) {
        $status = $_GET['status'];
        $mensagem = htmlspecialchars($_GET['msg']); 
        $class = ($status == 'sucesso') ? 'alert-success' : 'alert-danger';
        echo "<div class='alert $class'><p>$mensagem</p></div>";
    }
    ?>
    <h1>Gerenciamento de Clientes</h1>

    <!-- Formulário de Cadastro (mantido) -->
    <div class="register-section">
        <h2>Cadastrar Novo Cliente</h2>
        <form action="cadastrar_cliente.php" method="POST" class="register-form">
            <div class="form-group"><label for="nome">Nome Completo:</label><input type="text" id="nome" name="nome" required></div>
            <div class="form-group"><label for="cpf">CPF:</label><input type="text" id="cpf" name="cpf" placeholder="Ex: 123.456.789-00" required></div>
            <div class="form-group"><label for="email">E-mail:</label><input type="email" id="email" name="email" required></div>
            <div class="form-group"><label for="telefone">Telefone:</label><input type="tel" id="telefone" name="telefone" placeholder="Ex: (11) 98765-4321" required></div>
            <button type="submit" class="btn-submit"><i class="fas fa-user-plus"></i> Cadastrar Cliente</button>
        </form>
    </div>
    
    <!-- Formulário de Busca (mantido) -->
    <div class="search-section">
        <h2>Buscar Clientes</h2>
        <form action="clientes.php" method="GET" class="search-form">
            <input type="text" id="search-client" name="q" placeholder="Digite o nome, CPF ou e-mail..." value="<?= htmlspecialchars($q) ?>" required>
            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Pesquisar</button>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Histórico de Clientes (<?= count($clientes) ?> encontrados)</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Cliente</th>
                        <th>Contato</th>
                        <th>Desde</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clientes) > 0): ?>
                        <?php foreach($clientes as $c): ?>
                        <!-- 3. ADICIONA A CLASSE 'cliente-inativo' SE NECESSÁRIO -->
                        <tr class="<?= $c['ativo'] ? '' : 'cliente-inativo' ?>">
                            <td>
                                <strong><?= htmlspecialchars($c['nome']) ?></strong>  

                                <small>CPF: <?= htmlspecialchars($c['cpf']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($c['email']) ?>  

                                <small><?= htmlspecialchars($c['telefone']) ?></small>
                            </td>
                            <td><?= date('d/m/Y', strtotime($c['data_cadastro'])) ?></td>
                            <td>
                                <!-- 4. EXIBE O BADGE DE STATUS -->
                                <?php if ($c['ativo']): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- 5. LÓGICA DOS BOTÕES DE AÇÃO -->
                                <?php if ($c['ativo']): ?>
                                    <!-- Se estiver ativo, mostra Editar e Desativar -->
                                    <a href="editar_cliente.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="delete_cliente.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning" title="Desativar" onclick="return confirm('Tem certeza que deseja desativar este cliente?');"><i class="fas fa-user-slash"></i></a>
                                <?php else: ?>
                                    <!-- Se estiver inativo, mostra apenas Reativar -->
                                    <a href="reactivate_cliente.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-success" title="Reativar" onclick="return confirm('Tem certeza que deseja reativar este cliente?');"><i class="fas fa-user-check"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Nenhum cliente encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 
<script>
    // Seu script de formatação de CPF/Telefone aqui (mantido igual)
</script>
<!-- NOSSO NOVO SCRIPT (GLOBAL, PARA O TEMA) -->
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>
