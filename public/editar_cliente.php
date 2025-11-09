<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

// 1. VERIFICAR SE O ID FOI FORNECIDO
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: clientes.php?status=erro&msg=ID do cliente não fornecido.");
    exit;
}

$cliente_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$cliente = null;
$mensagem_erro = '';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // 2. BUSCAR OS DADOS ATUAIS DO CLIENTE NO BANCO
    $stmt = $conn->prepare("SELECT id, nome, cpf, email, telefone FROM clientes WHERE id = ? AND user_id = ?");
    $stmt->execute([$cliente_id, $user_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se o cliente não for encontrado, redireciona
    if (!$cliente) {
        throw new Exception("Cliente não encontrado ou não pertence a este usuário.");
    }

} catch (Exception $e) {
    $mensagem_erro = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
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
        <div class="container mt-5">
            <h1>Editar Cliente</h1>

            <?php if ($mensagem_erro ): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($mensagem_erro) ?></div>
                <a href="clientes.php" class="btn btn-secondary">Voltar para a Lista</a>
            <?php elseif ($cliente): ?>
                <!-- 3. FORMULÁRIO PRÉ-PREENCHIDO COM OS DADOS DO CLIENTE -->
                <div class="register-section">
                    <form action="atualizar_cliente.php" method="POST" class="register-form">
                        <!-- Campo oculto para enviar o ID do cliente -->
                        <input type="hidden" name="id" value="<?= $cliente['id'] ?>">

                        <div class="form-group">
                            <label for="nome">Nome Completo:</label>
                            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cpf">CPF:</label>
                            <input type="text" id="cpf" name="cpf" value="<?= htmlspecialchars($cliente['cpf']) ?>" placeholder="Ex: 123.456.789-00" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mail:</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefone">Telefone:</label>
                            <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>" placeholder="Ex: (11) 98765-4321" required>
                        </div>
                        
                        <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Salvar Alterações</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Inclua seu script de formatação de CPF/Telefone se tiver um -->
    <script src="../assets/js/dark-mode.js"></script>
</body>
</html>
