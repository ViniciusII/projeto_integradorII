<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/../conexao.php'; 

$user_id = $_SESSION['user_id'];
$q = $_GET['q'] ?? ''; 

$sql = "SELECT id, nome, cpf, email, telefone, data_cadastro FROM clientes WHERE user_id = ?";
$params = [$user_id];

if ($q) {
    $sql .= " AND (nome LIKE ? OR cpf LIKE ? OR email LIKE ?)";
    $q_like = "%" . $q . "%";
    $params = [$user_id, $q_like, $q_like, $q_like]; 
}

$sql .= " ORDER BY nome ASC";

$clientes = [];
$mensagem_erro = null;

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar clientes: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
    </head>
<body class="clientes-page">
    <div class="sidebar-dashboard">
        <h2>Menu</h2>
        <a href="dashboard.php"><i class="fas fa-home"></i> Início</a>
        <a href="clientes.php" class="active"><i class="fas fa-users"></i> Clientes</a>
        <a href="vendas.php"><i class="fas fa-shopping-cart"></i> Venda</a>
        <a href="estoque.php"><i class="fas fa-boxes"></i> Estoque</a>
        <div class="sidebar-logout">
        <a href="logout.php" class="btn-logout-dashboard"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>
    <div class="main-dashboard">
    <?php
    if (isset($_GET['status']) && isset($_GET['msg'])) {
        $status = $_GET['status'];
        $mensagem = htmlspecialchars($_GET['msg']); 
        
        $class = ($status == 'sucesso') ? 'alert-success' : 'alert-error';
        echo "<div class='alert $class'>";
        echo "<p>$mensagem</p>";
        echo "</div>";
    }
    if ($mensagem_erro) {
        echo "<div class='alert alert-error'>";
        echo "<p>❌ $mensagem_erro</p>";
        echo "</div>";
    }
    ?>
    <h1>Gerenciamento de Clientes</h1>

    <div class="register-section">
        <h2>Cadastrar Novo Cliente</h2>
        <form action="cadastrar_cliente.php" method="POST" class="register-form">
            
            <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            
            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" title="Formato: 999.999.999-99" placeholder="Ex: 123.456.789-00" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="telefone">Telefone:</label>
                <input type="tel" id="telefone" name="telefone" pattern="^\(\d{2}\)\s\d{4,5}-\d{4}$" title="Formato: (99) 99999-9999" placeholder="Ex: (11) 98765-4321" required>
            </div>
            
            <button type="submit" class="btn-submit"><i class="fas fa-user-plus"></i> Cadastrar Cliente</button>
        </form>
    </div>
    
    <div class="search-section">
        <h2>Buscar Clientes</h2>
        <form action="clientes.php" method="GET" class="search-form">
            <input type="text" id="search-client" name="q" placeholder="Digite o nome, CPF ou e-mail do cliente..." value="<?= htmlspecialchars($q) ?>" required>
            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Pesquisar</button>
        </form>
    </div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Histórico de Clientes (<?= count($clientes) ?> encontrados)</h5>
    </div>

    <?php if ($mensagem_erro): ?>
        <div class='alert alert-danger mx-3 mt-3'><p>❌ <?= htmlspecialchars($mensagem_erro) ?></p></div>
    <?php endif; ?>

    <?php if (count($clientes) > 0): ?>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Desde</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clientes as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['nome']) ?></td>
                        <td><?= htmlspecialchars($c['cpf']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['telefone']) ?></td>
                        <td><?= date('d/m/Y', strtotime($c['data_cadastro'])) ?></td>
                        <td>
                            <a href="editar_cliente.php?id=<?= $c['id'] ?>" class="btn-action edit" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="excluir_cliente.php?id=<?= $c['id'] ?>" class="btn-action delete" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este cliente?');"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card-body">
            <p class="no-results">
                <?php if ($q): ?>
                    Nenhum cliente encontrado para a pesquisa "<?= htmlspecialchars($q) ?>".
                <?php else: ?>
                    Nenhum cliente registrado ainda.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

    </div> <script>
    function formatarCPF(input) {
        let value = input.value.replace(/\D/g, ''); 
        value = value.substring(0, 11);

        if (value.length > 9) {
            value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
        } else if (value.length > 6) {
            value = value.replace(/^(\d{3})(\d{3})(\d{3})$/, '$1.$2.$3');
        } else if (value.length > 3) {
            value = value.replace(/^(\d{3})(\d{3})$/, '$1.$2');
        }
        
        input.value = value;
    }

    function formatarTelefone(input) {
        let value = input.value.replace(/\D/g, '');
        
        value = value.substring(0, 11);

        if (value.length > 10) { // Celular: 11 dígitos
            value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
        } else if (value.length > 6) { // Fixo: 10 dígitos
            value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{1,})$/, '($1) $2');
        } else if (value.length > 0) {
            value = value.replace(/^(\d{1,2})$/, '($1');
        }
        
        input.value = value;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputCPF = document.getElementById('cpf');
        const inputTelefone = document.getElementById('telefone');
        if (inputCPF) {
            inputCPF.addEventListener('input', () => formatarCPF(inputCPF));
        }
        if (inputTelefone) {
            inputTelefone.addEventListener('input', () => formatarTelefone(inputTelefone));
        }
    });
</script>
</body>
</html>