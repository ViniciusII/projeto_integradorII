<?php
require_once "../config/database.php";
require_once "../src/Produto.php";

$db = (new Database())->getConnection();
$produto = new Produto($db);

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produto->nome = $_POST["nome"] ?? "";
    $produto->descricao = $_POST["descricao"] ?? "";
    $produto->quantidade = intval($_POST["quantidade"] ?? 0);
    $produto->preco = floatval(str_replace(',', '.', $_POST["preco"] ?? 0));

    if ($produto->adicionar()) {
        $mensagem = "Produto adicionado com sucesso!";
    } else {
        $mensagem = "Erro ao adicionar produto.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="adcprod-page">
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

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5>Adicionar Novo Produto</h5>
        </div>
        <div class="card-body">
            <?php if ($mensagem): ?>
                <div class="alert alert-info"><?= $mensagem ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantidade</label>
                    <input type="number" name="quantidade" class="form-control" required min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Preço (ex: 10,50)</label>
                    <input type="text" name="preco" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Salvar</button>
                <a href="estoque.php" class="btn btn-secondary">Voltar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
