<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

// Verifica se o ID do produto foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: estoque.php?status=erro&msg=ID do produto não fornecido.");
    exit;
}

$produto_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Prepara o comando para DESATIVAR o produto (exclusão lógica)
    // Em vez de DELETE, usamos UPDATE para mudar o status da coluna 'ativo' para 0 (false)
    $stmt = $conn->prepare("UPDATE produtos SET ativo = 0 WHERE id = ? AND user_id = ?");
    
    $stmt->execute([$produto_id, $user_id]);

    // Verifica se alguma linha foi realmente atualizada
    if ($stmt->rowCount() > 0) {
        header("Location: estoque.php?status=sucesso&msg=Produto desativado com sucesso!");
    } else {
        // Isso pode acontecer se o produto não existir ou não pertencer ao usuário
        throw new Exception("Produto não encontrado ou permissão negada.");
    }

} catch (Exception $e) {
    // Redireciona com uma mensagem de erro genérica
    header("Location: estoque.php?status=erro&msg=" . urlencode("Erro ao desativar o produto: " . $e->getMessage()));
}
exit;
