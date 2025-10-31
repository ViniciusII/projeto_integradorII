<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";
require_once "../src/Produto.php";

$db = (new Database())->getConnection();
$produto = new Produto($db);

$id = $_GET['id'] ?? null;
$mensagem = "";

if (!$id) {
    die("ID do produto não informado.");
}

// Pegar produto e verificar se pertence ao usuário logado
$produtoDados = $produto->pegarProdutoPorId($id);

if (!$produtoDados) {
    die("Produto não encontrado.");
}

if ($produtoDados['user_id'] != $_SESSION['user_id']) {
    die("Você não tem permissão para atualizar este produto.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nova_quantidade = intval($_POST['quantidade'] ?? 0);

    try {
        if ($produto->atualizarEstoque($id, $nova_quantidade)) {
            $mensagem = "Estoque atualizado com sucesso!";
            $produtoDados['quantidade'] = $nova_quantidade;
        } else {
            $mensagem = "Erro ao atualizar estoque.";
        }
    } catch (Exception $e) {
        $mensagem = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Atualizar Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="attestoq-page">
<div class="sidebar-dashboard">
    <h2>Menu</h2>
    <a href="dashboard.php"><i class="fas fa-home"></i> Início</a>
    <a href="clientes.php"><i class="fas fa-users"></i> Clientes</a>
    <a href="vendas.php"><i class="fas fa-shopping-cart"></i> Venda</a>
    <a href="estoque.php"><i class="fas fa-boxes"></i> Estoque</a>
    <div class="sidebar-logout">
        <a href="logout.php" class="btn-logout-dashboard"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="container" style="margin-top: 80px;">
    <div class="card">
        <div class="card-header">
            <h5>Atualizar Estoque do Produto: <?= htmlspecialchars($produtoDados['nome']) ?></h5>
        </div>
        <div class="card-body">
            <?php if ($mensagem): ?>
                <div class="alert alert-info"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nova Quantidade</label>
                    <input type="number" name="quantidade" class="form-control" min="0" value="<?= $produtoDados['quantidade'] ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Atualizar</button>
                <a href="estoque.php" class="btn btn-secondary">Voltar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
