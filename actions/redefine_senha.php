<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/recuperar_senha.php');
    exit;
}

$email            = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$nova_senha       = trim($_POST['nova_senha'] ?? '');
$confirma_senha   = trim($_POST['confirma_senha'] ?? '');

// Validações básicas dos campos
if (!$email || empty($nova_senha) || empty($confirma_senha)) {
    $_SESSION['erro_recuperar'] = 'Preencha todos os campos corretamente.';
    header('Location: ../pages/recuperar_senha.php');
    exit;
}

if ($nova_senha !== $confirma_senha) {
    $_SESSION['erro_recuperar'] = 'As senhas não coincidem.';
    header('Location: ../pages/recuperar_senha.php');
    exit;
}

if (strlen($nova_senha) < 6) {
    $_SESSION['erro_recuperar'] = 'A nova senha deve ter no mínimo 6 caracteres.';
    header('Location: ../pages/recuperar_senha.php');
    exit;
}

try {
    // 1. Verifica se o e-mail realmente existe no sistema
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        $_SESSION['erro_recuperar'] = 'E-mail não encontrado no sistema.';
        header('Location: ../pages/recuperar_senha.php');
        exit;
    }

    // 2. Gera o novo hash seguro e atualiza no banco
    $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    $updateStmt = $pdo->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id');
    $updateStmt->execute([
        ':senha' => $novo_hash,
        ':id'    => $usuario['id']
    ]);

    // Redireciona para o login com mensagem de sucesso
    $_SESSION['sucesso_login'] = 'Senha redefinida com sucesso! Faça login com a nova senha.';
    header('Location: ../index.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_recuperar'] = 'Erro ao redefinir a senha: ' . $e->getMessage();
    header('Location: ../pages/recuperar_senha.php');
    exit;
}