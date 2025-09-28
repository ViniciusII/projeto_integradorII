<?php
require_once "../config/database.php";
require_once "../src/Produto.php";

$db = (new Database())->getConnection();
$produto = new Produto($db);

$stmt = $produto->listar();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Controle de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
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
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Lista de Produtos</h5>
            <a href="add_product.php" class="btn btn-success">+ Adicionar Produto</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Quantidade</th>
                        <th>Preço (R$)</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nome']) ?></td>
                            <td><?= htmlspecialchars($row['descricao']) ?></td>
                            <td><?= $row['quantidade'] ?></td>
                            <td><?= number_format($row['preco'], 2, ',', '.') ?></td>
                            <td>
                                <a href="update_stock.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Atualizar Estoque</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
