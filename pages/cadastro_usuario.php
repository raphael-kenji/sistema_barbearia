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
    <title>Cadastro - Barbearia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="d-flex align-items-center min-vh-100 py-5">

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                
                <section class="card card-login p-4">
                    <header class="text-center mb-4">
                        <i class="bi bi-person-plus text-warning display-4"></i>
                        <h1 class="h3 fw-bold mt-2">Criar Nova Conta</h1>
                        <p class="text-secondary small">Cadastre-se para agendar seus atendimentos</p>
                    </header>

                    <!-- Feedback de erro -->
                    <?php if (isset($_SESSION['erro_cadastro'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            <?= htmlspecialchars($_SESSION['erro_cadastro']); ?>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                        <?php unset($_SESSION['erro_cadastro']); ?>
                    <?php endif; ?>

                    <form action="../actions/processa_cadastro.php" method="POST">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label small fw-semibold">Nome Completo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="nome" name="nome" placeholder="Seu nome" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold">E-mail</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="seu@email.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="telefone" class="form-label small fw-semibold">Telefone / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="18999998888">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="senha" class="form-label small fw-semibold">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-secondary">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="senha" name="senha" placeholder="••••••••" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirma_senha" class="form-label small fw-semibold">Confirmar Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-secondary">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" placeholder="••••••••" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-warning fw-bold py-2">
                                <i class="bi bi-check-circle me-1"></i> Finalizar Cadastro
                            </button>
                        </div>
                    </form>

                    <footer class="mt-4 pt-3 border-top border-secondary text-center small text-secondary">
                        Já tem uma conta? <a href="../index.php" class="text-warning text-decoration-none fw-semibold">Faça Login</a>
                    </footer>
                </section>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>