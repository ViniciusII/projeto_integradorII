```php
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
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if ($email === '') {
        $error = "Informe o email.";
    } else {
        try {
            // Verifica se o email existe no banco
            $stmt = $db->prepare("SELECT id FROM operators WHERE email = :email");
            $stmt->execute([":email" => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Aqui seria feito o envio real do email
                $success = "Um link de recuperação foi enviado para o seu email.";
            } else {
                $error = "Email não encontrado.";
            }
        } catch (PDOException $e) {
            $error = "Erro: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Recuperar Senha</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
</head>
<body class="registro-page">
    <div class="registro-container">
        <form class="registro-form" method="post" action="recuperar.php">
            <h2>Recuperar Senha</h2>

            <?php 
            if (!empty($error)) {
                echo "<div style='color:red; text-align:center; margin-bottom:10px;'>".htmlspecialchars($error)."</div>";
            }

            if (!empty($success)) {
                echo "<div style='color:green; text-align:center; margin-bottom:10px;'>".htmlspecialchars($success)."</div>";
            }
            ?>

            <div class="input-group">
                <label>Email cadastrado</label>
                <input type="email" name="email" required>
            </div>

            <button type="submit" class="btn-registro">Recuperar</button>

            <p class="criar-conta"><a href="login.php">Voltar para Login</a></p>
        </form>
    </div>
</body>
</html>

