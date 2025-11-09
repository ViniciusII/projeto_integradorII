<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once "../config/database.php";

$db = (new Database())->getConnection();

// A consulta agora busca também a coluna 'ativo'
$stmt = $db->prepare("SELECT id, nome, descricao, quantidade, preco, ativo FROM produtos WHERE user_id = ? ORDER BY nome ASC");
$stmt->execute([$_SESSION['user_id']]);
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
</head><body class="estoque-page">
<div class="sidebar-dashboard">
    <h2>Menu</h2>
    <a href="dashboard.php"><i class="fas fa-home"></i> Início</a>
    <a href="clientes.php"><i class="fas fa-users"></i> Clientes</a>
    <a href="vendas.php"><i class="fas fa-shopping-cart"></i> Venda</a>
    <a href="servicos.php"><i class="fas fa-tools"></i> Serviços</a>
    <a href="estoque.php" class="active"><i class="fas fa-boxes"></i> Estoque</a>
    <div class="sidebar-logout">
        <a href="logout.php" class="btn-logout-dashboard"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="main-dashboard">
    <div class="container" style="margin-top: 60px; padding-bottom: 20px;">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Produtos</h5>
                <a href="add_product.php" class="btn btn-success">+ Adicionar Produto</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Quantidade</th>
                                <th>Preço (R$ )</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <!-- Adiciona a classe 'produto-inativo' se o produto não estiver ativo -->
                                <tr class="<?= $row['ativo'] ? '' : 'produto-inativo' ?>">
                                    <td><?= htmlspecialchars($row['nome']) ?></td>
                                    <td><?= htmlspecialchars($row['descricao']) ?></td>
                                    <td><?= $row['quantidade'] ?></td>
                                    <td><?= number_format($row['preco'], 2, ',', '.') ?></td>
                                    <td>
                                        <!-- Mostra um badge de status -->
                                        <?php if ($row['ativo']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="update_stock.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info" title="Editar/Atualizar"><i class="fas fa-edit"></i></a>
                                        <?php if ($row['ativo']): ?>
                                            <!-- Se estiver ativo, mostra o botão de Desativar -->
                                            <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Desativar" onclick="return confirm('Tem certeza que deseja desativar este produto?');"><i class="fas fa-eye-slash"></i></a>
                                        <?php else: ?>
                                            <!-- Se estiver inativo, mostra o botão de Reativar -->
                                            <a href="reactivate_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success" title="Reativar" onclick="return confirm('Tem certeza que deseja reativar este produto?');"><i class="fas fa-eye"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>
