<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: estoque.php?status=erro&msg=ID do produto não fornecido.");
    exit;
}

$produto_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Comando para REATIVAR o produto
    $stmt = $conn->prepare("UPDATE produtos SET ativo = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$produto_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        header("Location: estoque.php?status=sucesso&msg=Produto reativado com sucesso!");
    } else {
        throw new Exception("Produto não encontrado ou permissão negada.");
    }

} catch (Exception $e) {
    header("Location: estoque.php?status=erro&msg=" . urlencode("Erro ao reativar o produto."));
}
exit;
