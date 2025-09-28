<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
</head>
<body class="clientes-page">
    <div class="sidebar-dashboard">
        <h2>Menu</h2>
        <a href="dashboard.php"><i class="fas fa-home"></i> Início</a>
        <a href="clientes.php"><i class="fas fa-users"></i> Clientes</a>
        <a href="vendas.php"><i class="fas fa-shopping-cart"></i> Venda</a>
        <a href="estoque.php"><i class="fas fa-boxes"></i> Estoque</a>
        <div class="sidebar-logout">
        <a href="logout.php" class="btn-logout-dashboard"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>
    <!-- Conteúdo principal -->
    <div class="main-dashboard">
    <?php
    if (isset($_GET['status']) && isset($_GET['msg'])) {
        $status = $_GET['status'];
        $mensagem = htmlspecialchars($_GET['msg']); 
        
        // Define a classe CSS para o estilo (cor)
        $class = ($status == 'sucesso') ? 'alert-success' : 'alert-error';
        
        // Exibe a caixa de alerta
        echo "<div class='alert $class'>";
        echo "<p>$mensagem</p>";
        echo "</div>";
    }
    ?>
    <h1>Gerenciamento de Clientes</h1>

    <div class="search-section">
        <h2>Buscar Clientes</h2>
        <form action="clientes.php" method="GET" class="search-form">
            <input type="text" id="search-client" name="q" placeholder="Digite o nome, CPF ou e-mail do cliente..." required>
            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Pesquisar</button>
        </form>
    </div>

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

    <div class="client-list">
        </div>
</div>
</div> <script>
    // 1. Função para formatar o CPF
    function formatarCPF(input) {
        let value = input.value.replace(/\D/g, ''); // Remove tudo que não é dígito
        
        // Aplica a máscara: 999.999.999-99
        if (value.length > 9) {
            value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
        } else if (value.length > 6) {
            value = value.replace(/^(\d{3})(\d{3})(\d{3})$/, '$1.$2.$3');
        } else if (value.length > 3) {
            value = value.replace(/^(\d{3})(\d{3})$/, '$1.$2');
        }
        
        input.value = value;
    }

    // 2. Função para formatar o Telefone/Celular
    function formatarTelefone(input) {
        let value = input.value.replace(/\D/g, ''); // Remove tudo que não é dígito
        
        // Aplica a máscara: (99) 99999-9999 (para celular com 9 dígitos) ou (99) 9999-9999
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
        } else if (value.length > 6) {
            value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{1,})$/, '($1) $2');
        } else if (value.length > 0) {
            value = value.replace(/^(\d{1,2})$/, '($1');
        }
        
        input.value = value;
    }

    // 3. Adicionar os eventos de input aos campos
    document.addEventListener('DOMContentLoaded', function() {
        const inputCPF = document.getElementById('cpf');
        const inputTelefone = document.getElementById('telefone');

        // Garante que o evento de formatação seja chamado a cada digitação
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
</body>
</body>
</html>
