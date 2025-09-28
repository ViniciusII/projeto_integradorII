<?php
session_start();
require_once "../config/database.php";

$error = '';
$success = '';

try {
    $db = (new Database())->getConnection();
} catch (Exception $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm  = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($username === '' || $password === '' || $email === '') {
        $error = "Preencha todos os campos.";
    } elseif ($password !== $confirm) {
        $error = "As senhas não coincidem.";
    } else {
        try {
            // Verifica se username ou email já existe (ignora maiúsc/minúsc)
            $stmt = $db->prepare("SELECT username, email FROM operators");
            $stmt->execute();
            $exists = false;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (strcasecmp($row['username'], $username) === 0) {
                    $error = "Nome de usuário já existe.";
                    $exists = true;
                    break;
                }
                if (strcasecmp($row['email'], $email) === 0) {
                    $error = "Email já cadastrado.";
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                // Cria hash da senha
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Insere usuário no banco
                $ins = $db->prepare("INSERT INTO operators (username, password, email) VALUES (:username, :password, :email)");
                $ins->execute([
                    ':username' => $username,
                    ':password' => $hash,
                    ':email'    => $email
                ]);

                $success = "Usuário criado com sucesso!";
            }
        } catch (PDOException $e) {
            $error = "Erro ao criar usuário: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Criar Usuário - Awax</title>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
</head>
<body class="registro-page">
    <div class="registro-container">
        <form class="registro-form" method="post" action="registro.php">
            <h2>Criar Usuário</h2>

            <?php 
            if (!empty($error)) {
                echo "<div style='color:red; text-align:center; margin-bottom:10px;'>".htmlspecialchars($error)."</div>";
            }

            if (!empty($success)) {
                echo "<div style='color:green; text-align:center; margin-bottom:10px;'>".htmlspecialchars($success)."</div>";
            }
            ?>

            <div class="input-group">
                <label>Usuário</label>
                <input type="text" name="username" required>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="password" required>
            </div>

            <div class="input-group">
                <label>Confirmar Senha</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn-registro">Criar</button>

            <p class="criar-conta"><a href="login.php">Já tem conta? Entrar</a></p>
        </form>
    </div>
</body>
</html>
