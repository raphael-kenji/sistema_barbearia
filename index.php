<?php
session_start();

// Se já estiver logado, vai direto para o painel
if (isset($_SESSION['usuario_id'])) {
    header('Location: pages/painel.php');
    exit;
}

// Recupera os valores dos cookies se existirem
$email_salvo = $_COOKIE['cookie_email'] ?? '';
$senha_salva = $_COOKIE['cookie_senha'] ?? '';
$lembrar_checked = (!empty($email_salvo) && !empty($senha_salva)) ? 'checked' : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barbearia Clássica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center min-vh-100 p-3">

    <main class="card card-login bg-dark text-white border-secondary shadow-lg p-4" style="max-width: 420px; width: 100%;">
        
        <header class="text-center mb-4">
            <i class="bi bi-scissors text-warning fs-1"></i>
            <h1 class="h4 fw-bold mt-2">Barbearia Clássica</h1>
            <p class="text-secondary small mb-0">Acesse sua conta para agendar ou gerenciar horários</p>
        </header>

        <!-- Alertas de Erro ou Sucesso -->
        <?php if (isset($_SESSION['erro_login'])): ?>
            <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                <?= htmlspecialchars($_SESSION['erro_login']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['erro_login']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['sucesso_login'])): ?>
            <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                <?= htmlspecialchars($_SESSION['sucesso_login']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['sucesso_login']); ?>
        <?php endif; ?>

        <!-- Formulário de Login -->
        <form action="actions/processa_login.php" method="POST">
            
            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-light">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" 
                           class="form-control bg-dark text-white border-secondary" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($email_salvo); ?>" 
                           placeholder="seu@email.com" 
                           required>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="senha" class="form-label small fw-semibold text-light">Senha</label>
                    <a href="pages/recuperar_senha.php" class="text-warning text-decoration-none small">Esqueceu a senha?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control bg-dark text-white border-secondary" 
                           id="senha" 
                           name="senha" 
                           value="<?= htmlspecialchars($senha_salva); ?>" 
                           placeholder="Sua senha" 
                           required>
                </div>
            </div>

            <!-- Checkbox Lembrar de mim (Cookies) -->
            <div class="form-check mb-3">
                <input class="form-check-input bg-dark border-secondary" 
                       type="checkbox" 
                       id="lembrar" 
                       name="lembrar" 
                       <?= $lembrar_checked; ?>>
                <label class="form-check-label small text-secondary" for="lembrar">
                    Lembrar e-mail e senha
                </label>
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-bold py-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
            </button>
        </form>

        <footer class="text-center mt-4 pt-3 border-top border-secondary">
            <span class="small text-secondary">Ainda não tem conta?</span>
            <a href="pages/cadastro_usuario.php" class="text-warning text-decoration-none small fw-semibold ms-1">Cadastre-se</a>
        </footer>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>