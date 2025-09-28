<?php
session_start();
require_once "../config/database.php";

$db = (new Database())->getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $stmt = $db->prepare("SELECT * FROM operators WHERE LOWER(username) = LOWER(:username) LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stored = $user['password'];

        // Verifica se o valor armazenado parece ser um hash gerado por password_hash
        $info = password_get_info($stored);

        if ($info['algo'] !== 0) {
            // senha já está em hash -> usar password_verify
            if (password_verify($password, $stored)) {
                // opcional: rehash se o algoritmo mudou/precisa de upgrade
                if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $db->prepare("UPDATE operators SET password = :hash WHERE id = :id");
                    $upd->execute([':hash' => $newHash, ':id' => $user['id']]);
                }

                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Usuário ou senha inválidos.";
            }
            } else {
            if (strcasecmp(trim($password), trim($stored)) === 0) {
                // migra para hash no primeiro login bem-sucedido
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $db->prepare("UPDATE operators SET password = :hash WHERE id = :id");
                $upd->execute([':hash' => $newHash, ':id' => $user['id']]);

                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Usuário ou senha inválidos.";
            }
        }
    } else {
        $error = "Usuário ou senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Awax</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
</head>
<body class="login-page">
    <div class="login-container">
        <form class="login-form" action="login.php" method="POST" autocomplete="off">
            <h2>Gestão Awax</h2>

            <div class="input-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" placeholder="Digite seu usuário" required />
            </div>

            <div class="input-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="Digite sua senha" required />
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message" style="color:red; text-align:center; margin-bottom:10px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-login">Entrar</button>

            <p class="esqueceu-senha"><a href="#">Esqueceu a senha?</a></p>
            <p class="criar-conta"><a href="registro.php">Ainda não tem uma conta? Experimente grátis!</a></p>
        </form>
    </div>
</body>
</html>
