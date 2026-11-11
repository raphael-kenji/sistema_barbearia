<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro_login'] = 'Faça login para realizar esta ação.';
    header('Location: ../index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$novo_status = trim($_GET['status'] ?? 'cancelado');

if (!$id || !in_array($novo_status, ['cancelado', 'concluido'])) {
    $_SESSION['erro_agendamento'] = 'Ação ou agendamento inválido.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    // Cliente só pode cancelar os próprios agendamentos; admin e barbeiro podem gerir
    if ($_SESSION['usuario_tipo'] === 'cliente') {
        $stmt = $pdo->prepare('UPDATE agendamentos SET status = "cancelado" WHERE id = :id AND cliente_id = :cliente_id');
        $stmt->execute([
            ':id'         => $id,
            ':cliente_id' => $_SESSION['usuario_id']
        ]);
    } else {
        $stmt = $pdo->prepare('UPDATE agendamentos SET status = :status WHERE id = :id');
        $stmt->execute([
            ':status' => $novo_status,
            ':id'     => $id
        ]);
    }

    $_SESSION['sucesso_agendamento'] = 'Status do agendamento atualizado com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_agendamento'] = 'Erro ao atualizar agendamento: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}