<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

include_once '../config/database.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $database = new Database();
    $pdo = $database->getConnection(); 

    $nome_sujo      = trim($_POST['nome']);
    $cpf_sujo       = trim($_POST['cpf']);
    $email_sujo     = trim($_POST['email']);
    $telefone_sujo  = trim($_POST['telefone']);

    $cpf_limpo      = preg_replace('/\D/', '', $cpf_sujo);
    $telefone_limpo = preg_replace('/\D/', '', $telefone_sujo); 
    $nome           = filter_var($nome_sujo, FILTER_SANITIZE_SPECIAL_CHARS);
    $email          = filter_var($email_sujo, FILTER_SANITIZE_EMAIL);

    if (empty($nome) || empty($cpf_limpo) || empty($email) || empty($telefone_limpo)) {
        header("Location: clientes.php?status=erro&msg=Preencha todos os campos obrigatórios.");
        exit;
    }

    try {
        $sql = "INSERT INTO clientes (nome, cpf, email, telefone, user_id) 
                VALUES (:nome, :cpf, :email, :telefone, :user_id)"; 

        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cpf', $cpf_limpo);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone_limpo);
        $stmt->bindParam(':user_id', $user_id); 
        
        $stmt->execute();

        header("Location: clientes.php?status=sucesso&msg=Cliente $nome cadastrado com sucesso!");
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { 
            header("Location: clientes.php?status=erro&msg=Erro: CPF ou E-mail já existe em nosso cadastro.");
        } else {
            error_log("Erro de Cadastro de Cliente: " . $e->getMessage());
            header("Location: clientes.php?status=erro&msg=Erro interno. Por favor, tente novamente.");
        }
        exit;
    }
} else {
    header("Location: clientes.php");
    exit;
}
?>