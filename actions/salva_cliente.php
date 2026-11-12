<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    $_SESSION['erro_login'] = 'Acesso não autorizado.';
    header('Location: ../pages/painel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/painel.php');
    exit;
}

$nome     = trim($_POST['nome'] ?? '');
$email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$telefone = trim($_POST['telefone'] ?? '');
$senha    = trim($_POST['senha'] ?? '');

if (empty($nome) || !$email || strlen($senha) < 6) {
    $_SESSION['erro_cliente'] = 'Preencha nome, e-mail válido e senha com no mínimo 6 caracteres.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    // Verifica se o e-mail já está cadastrado
    $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $check->execute([':email' => $email]);
    if ($check->fetch()) {
        $_SESSION['erro_cliente'] = 'Este e-mail já está cadastrado no sistema.';
        header('Location: ../pages/painel.php');
        exit;
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = 'INSERT INTO usuarios (nome, email, telefone, senha, tipo) 
            VALUES (:nome, :email, :telefone, :senha, "cliente")';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'     => $nome,
        ':email'    => $email,
        ':telefone' => $telefone,
        ':senha'    => $hash
    ]);

    $_SESSION['sucesso_cliente'] = 'Cliente cadastrado com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_cliente'] = 'Erro ao cadastrar cliente: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}