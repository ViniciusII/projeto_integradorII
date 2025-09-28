<?php
// 1. Inclui o arquivo de conexão
// O caminho é corrigido: Sobe um nível (..) e entra na pasta config
include_once '../config/database.php'; 

// Verifica se a requisição é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Inicializa a Conexão com o Banco de Dados
    $database = new Database();
    $pdo = $database->getConnection(); 

    // 3. Coleta e Limpa os Dados do Formulário
    $nome_sujo     = trim($_POST['nome']);
    $cpf_sujo      = trim($_POST['cpf']);
    $email_sujo    = trim($_POST['email']);
    $telefone_sujo = trim($_POST['telefone']);

    // Limpeza Profunda (Remoção da máscara)
    $cpf_limpo      = preg_replace('/\D/', '', $cpf_sujo);
    $telefone_limpo = preg_replace('/\D/', '', $telefone_sujo); 
    $nome           = filter_var($nome_sujo, FILTER_SANITIZE_SPECIAL_CHARS);
    $email          = filter_var($email_sujo, FILTER_SANITIZE_EMAIL);

    // 4. Validação Básica
    if (empty($nome) || empty($cpf_limpo) || empty($email) || empty($telefone_limpo)) {
        header("Location: clientes.php?status=erro&msg=Preencha todos os campos obrigatórios.");
        exit;
    }

    // 5. Inserção Segura no Banco de Dados (Prepared Statement)
    try {
        $sql = "INSERT INTO clientes (nome, cpf, email, telefone) 
                VALUES (:nome, :cpf, :email, :telefone)";

        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cpf', $cpf_limpo);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone_limpo);
        
        $stmt->execute();

        // 6. Redirecionamento de Sucesso (clientes.php está na mesma pasta)
        header("Location: clientes.php?status=sucesso&msg=Cliente $nome cadastrado com sucesso!");
        exit;

    } catch (PDOException $e) {
        // Tratamento de erro (continua o mesmo)
        if ($e->getCode() == '23000') { 
            header("Location: clientes.php?status=erro&msg=Erro: CPF ou E-mail já existe em nosso cadastro.");
        } else {
            error_log("Erro de Cadastro de Cliente: " . $e->getMessage());
            header("Location: clientes.php?status=erro&msg=Erro interno. Por favor, tente novamente.");
        }
        exit;
    }
} else {
    // Acesso direto sem POST
    header("Location: clientes.php");
    exit;
}
?>