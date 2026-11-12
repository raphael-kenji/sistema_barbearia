<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    $_SESSION['erro_login'] = 'Acesso não autorizado.';
    header('Location: ../pages/painel.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['erro_cliente'] = 'Identificador de cliente inválido.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    $check = $pdo->prepare('SELECT COUNT(*) FROM agendamentos WHERE cliente_id = :id');
    $check->execute([':id' => $id]);

    if ($check->fetchColumn() > 0) {
        $_SESSION['erro_cliente'] = 'Não é possível excluir este cliente pois ele possui agendamentos vinculados.';
        header('Location: ../pages/painel.php');
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id AND tipo = "cliente"');
    $stmt->execute([':id' => $id]);

    $_SESSION['sucesso_cliente'] = 'Cliente removido com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_cliente'] = 'Erro ao excluir cliente: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}