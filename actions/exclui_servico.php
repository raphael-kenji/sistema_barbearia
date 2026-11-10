<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// Trava de segurança: apenas admin pode excluir
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    $_SESSION['erro_login'] = 'Acesso não autorizado.';
    header('Location: ../pages/painel.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['erro_servico'] = 'Identificador de serviço inválido.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM servicos WHERE id = :id');
    $stmt->execute([':id' => $id]);

    $_SESSION['sucesso_servico'] = 'Serviço removido com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_servico'] = 'Erro ao remover serviço: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}