<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// Apenas o administrador pode cadastrar barbeiros
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

if (empty($nome) || !$email || empty($senha)) {
    $_SESSION['erro_barbeiro'] = 'Preencha nome, e-mail válido e senha.';
    header('Location: ../pages/painel.php');
    exit;
}

if (strlen($senha) < 6) {
    $_SESSION['erro_barbeiro'] = 'A senha deve ter no mínimo 6 caracteres.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    // Verifica se o e-mail já está em uso
    $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $check->execute([':email' => $email]);
    if ($check->fetch()) {
        $_SESSION['erro_barbeiro'] = 'Este e-mail já está cadastrado no sistema.';
        header('Location: ../pages/painel.php');
        exit;
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = 'INSERT INTO usuarios (nome, email, telefone, senha, tipo) 
            VALUES (:nome, :email, :telefone, :senha, "barbeiro")';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'     => $nome,
        ':email'    => $email,
        ':telefone' => $telefone,
        ':senha'    => $hash
    ]);

    $_SESSION['sucesso_barbeiro'] = 'Barbeiro cadastrado com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_barbeiro'] = 'Erro ao cadastrar barbeiro: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}