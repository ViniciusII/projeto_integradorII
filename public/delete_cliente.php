<?php
session_start();
// Redireciona se o usuário não estiver logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Inclui a conexão com o banco de dados
require_once '../config/database.php';

// Verifica se o ID do cliente foi passado pela URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: clientes.php?status=erro&msg=ID do cliente não fornecido.");
    exit;
}

$cliente_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Prepara o comando para DESATIVAR o cliente (exclusão lógica)
    // Em vez de DELETE, usamos UPDATE para mudar o status da coluna 'ativo' para 0 (false)
    $stmt = $conn->prepare("UPDATE clientes SET ativo = 0 WHERE id = ? AND user_id = ?");
    
    // Executa o comando com os parâmetros seguros
    $stmt->execute([$cliente_id, $user_id]);

    // Verifica se alguma linha foi realmente atualizada
    if ($stmt->rowCount() > 0) {
        // Se funcionou, redireciona de volta para a lista de clientes com mensagem de sucesso
        header("Location: clientes.php?status=sucesso&msg=Cliente desativado com sucesso!");
    } else {
        // Isso pode acontecer se o cliente não existir ou não pertencer ao usuário
        throw new Exception("Cliente não encontrado ou permissão negada.");
    }

} catch (Exception $e) {
    // Em caso de qualquer erro, redireciona com uma mensagem de erro
    header("Location: clientes.php?status=erro&msg=" . urlencode("Erro ao desativar o cliente."));
}
exit;
