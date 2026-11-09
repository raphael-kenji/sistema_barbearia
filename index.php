<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barbearia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex align-items-center min-vh-100 py-5">

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                
                <section class="card card-login p-4">
                    <header class="text-center mb-4">
                        <i class="bi bi-scissors text-warning display-4"></i>
                        <h1 class="h3 fw-bold mt-2">Barbearia Clássica</h1>
                        <p class="text-secondary small">Acesse o sistema com suas credenciais</p>
                    </header>

                    <!-- Feedback de erro via sessão (opcional para integração futura) -->
                    <?php if (isset($_SESSION['erro_login'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            <?= htmlspecialchars($_SESSION['erro_login']); ?>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                        <?php unset($_SESSION['erro_login']); ?>
                    <?php endif; ?>

                    <!-- Formulário de autenticação configurado com method="POST" -->
                    <form action="processa_login.php" method="POST" autocomplete="on">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold">E-mail ou Usuário</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="email" 
                                    name="email" 
                                    placeholder="exemplo@barbearia.com" 
                                    required 
                                    autofocus
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="senha" class="form-label small fw-semibold mb-0">Senha</label>
                                <!-- Link para a tela de recuperação de senha exigida no trabalho -->
                                <a href="recuperar_senha.php" class="text-warning text-decoration-none small">Esqueceu a senha?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="senha" 
                                    name="senha" 
                                    placeholder="••••••••" 
                                    required
                                >
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-warning fw-bold py-2">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                            </button>
                        </div>
                    </form>

                    <footer class="mt-4 pt-3 border-top border-secondary text-center small text-secondary">
                        Não tem uma conta? <a href="cadastro_usuario.php" class="text-warning text-decoration-none fw-semibold">Cadastre-se</a>
                    </footer>
                </section>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>