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

$id       = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nome     = trim($_POST['nome'] ?? '');
$email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$telefone = trim($_POST['telefone'] ?? '');
$senha    = trim($_POST['senha'] ?? '');

if (!$id || empty($nome) || !$email) {
    $_SESSION['erro_cliente'] = 'Preencha corretamente o nome e o e-mail do cliente.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1');
    $check->execute([':email' => $email, ':id' => $id]);
    if ($check->fetch()) {
        $_SESSION['erro_cliente'] = 'Este e-mail já está em uso por outro utilizador.';
        header('Location: ../pages/painel.php');
        exit;
    }

    if (!empty($senha)) {
        if (strlen($senha) < 6) {
            $_SESSION['erro_cliente'] = 'A nova senha deve ter no mínimo 6 caracteres.';
            header('Location: ../pages/painel.php');
            exit;
        }
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql  = 'UPDATE usuarios SET nome = :nome, email = :email, telefone = :telefone, senha = :senha WHERE id = :id AND tipo = "cliente"';
        $params = [':nome' => $nome, ':email' => $email, ':telefone' => $telefone, ':senha' => $hash, ':id' => $id];
    } else {
        $sql  = 'UPDATE usuarios SET nome = :nome, email = :email, telefone = :telefone WHERE id = :id AND tipo = "cliente"';
        $params = [':nome' => $nome, ':email' => $email, ':telefone' => $telefone, ':id' => $id];
    }

    $pdo->prepare($sql)->execute($params);

    $_SESSION['sucesso_cliente'] = 'Dados do cliente atualizados com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_cliente'] = 'Erro ao atualizar cliente: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}