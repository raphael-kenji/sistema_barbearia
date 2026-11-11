<?php
session_start();

// Se já estiver logado, redireciona para o painel
if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Barbearia Clássica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center min-vh-100 p-3">

    <main class="card card-login bg-dark text-white border-secondary shadow-lg p-4" style="max-width: 420px; width: 100%;">
        
        <header class="text-center mb-4">
            <i class="bi bi-key-fill text-warning fs-1"></i>
            <h1 class="h4 fw-bold mt-2">Redefinir Senha</h1>
            <p class="text-secondary small mb-0">Informe o seu e-mail cadastrado e escolha uma nova senha.</p>
        </header>

        <!-- Mensagens de Alerta -->
        <?php if (isset($_SESSION['erro_recuperar'])): ?>
            <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                <?= htmlspecialchars($_SESSION['erro_recuperar']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['erro_recuperar']); ?>
        <?php endif; ?>

        <!-- Formulário de Troca de Senha -->
        <form action="../actions/redefine_senha.php" method="POST">
            
            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-light">E-mail Cadastrado *</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" 
                           class="form-control bg-dark text-white border-secondary" 
                           id="email" 
                           name="email" 
                           placeholder="seu@email.com" 
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label for="nova_senha" class="form-label small fw-semibold text-light">Nova Senha *</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control bg-dark text-white border-secondary" 
                           id="nova_senha" 
                           name="nova_senha" 
                           placeholder="Mínimo 6 caracteres" 
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label for="confirma_senha" class="form-label small fw-semibold text-light">Confirmar Nova Senha *</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input type="password" 
                           class="form-control bg-dark text-white border-secondary" 
                           id="confirma_senha" 
                           name="confirma_senha" 
                           placeholder="Repita a nova senha" 
                           required>
                </div>
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-bold py-2 mt-2">
                <i class="bi bi-check-circle me-1"></i> Atualizar Senha
            </button>
        </form>

        <footer class="text-center mt-4 pt-3 border-top border-secondary">
            <span class="small text-secondary">Lembrou sua senha?</span>
            <a href="../index.php" class="text-warning text-decoration-none small fw-semibold ms-1">Fazer Login</a>
        </footer>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>