<?php
session_start();

// Trava de segurança: barra quem não passou pelo login
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro_login'] = 'Faça login para aceder a esta área.';
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Barbearia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-4 py-2">
        <span class="navbar-brand mb-0 h1">Barbearia Clássica</span>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">
                Olá, <strong><?= htmlspecialchars($_SESSION['usuario_nome']); ?></strong> (<?= htmlspecialchars($_SESSION['usuario_tipo']); ?>)
            </span>
            <!-- Chama o logout subindo um nível e entrando em actions -->
            <a href="../actions/logout.php" class="btn btn-outline-danger btn-sm">Sair</a>
        </div>
    </nav>

    <main class="container my-5">
        <div class="card p-4 shadow-sm">
            <h2 class="h4">Autenticação bem-sucedida!</h2>
            <p class="text-muted mb-0">Área protegida carregada a partir da pasta <code>pages/</code>.</p>
        </div>
    </main>

</body>
</html>