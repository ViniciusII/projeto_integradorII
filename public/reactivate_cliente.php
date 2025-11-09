<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: clientes.php?status=erro&msg=ID do cliente não fornecido.");
    exit;
}

$cliente_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Comando para REATIVAR o cliente
    $stmt = $conn->prepare("UPDATE clientes SET ativo = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$cliente_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        header("Location: clientes.php?status=sucesso&msg=Cliente reativado com sucesso!");
    } else {
        throw new Exception("Cliente não encontrado ou permissão negada.");
    }

} catch (Exception $e) {
    header("Location: clientes.php?status=erro&msg=" . urlencode("Erro ao reativar o cliente."));
}
exit;
