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
    $_SESSION['erro_barbeiro'] = 'Identificador de barbeiro inválido.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    // Verifica se o barbeiro possui agendamentos vinculados
    $check = $pdo->prepare('SELECT COUNT(*) FROM agendamentos WHERE barbeiro_id = :id');
    $check->execute([':id' => $id]);

    if ($check->fetchColumn() > 0) {
        $_SESSION['erro_barbeiro'] = 'Não é possível excluir este barbeiro pois ele possui agendamentos vinculados no histórico.';
        header('Location: ../pages/painel.php');
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id AND tipo = "barbeiro"');
    $stmt->execute([':id' => $id]);

    $_SESSION['sucesso_barbeiro'] = 'Barbeiro removido com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_barbeiro'] = 'Erro ao excluir barbeiro: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}