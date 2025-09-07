<?php
require_once "../config/database.php";
require_once "../src/Produto.php";

$db = (new Database())->getConnection();
$produto = new Produto($db);

$id = $_GET['id'] ?? null;
$mensagem = "";

if (!$id) {
    die("ID do produto não informado.");
}

$produtoDados = $produto->pegarProdutoPorId($id);

if (!$produtoDados) {
    die("Produto não encontrado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nova_quantidade = intval($_POST['quantidade'] ?? 0);
    if ($nova_quantidade >= 0) {
        if ($produto->atualizarEstoque($id, $nova_quantidade)) {
            $mensagem = "Estoque atualizado com sucesso!";
            $produtoDados['quantidade'] = $nova_quantidade;
        } else {
            $mensagem = "Erro ao atualizar estoque.";
        }
    } else {
        $mensagem = "Quantidade inválida.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Atualizar Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <span class="navbar-brand">Controle de Estoque</span>
    </div>
</nav>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5>Atualizar Estoque do Produto: <?= htmlspecialchars($produtoDados['nome']) ?></h5>
        </div>
        <div class="card-body">
            <?php if ($mensagem): ?>
                <div class="alert alert-info"><?= $mensagem ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nova Quantidade</label>
                    <input type="number" name="quantidade" class="form-control" min="0" value="<?= $produtoDados['quantidade'] ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Atualizar</button>
                <a href="index.php" class="btn btn-secondary">Voltar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
