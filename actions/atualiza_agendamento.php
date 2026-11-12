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

$id               = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$servico_id       = filter_input(INPUT_POST, 'servico_id', FILTER_VALIDATE_INT);
$barbeiro_id      = filter_input(INPUT_POST, 'barbeiro_id', FILTER_VALIDATE_INT);
$data_agendamento = trim($_POST['data_agendamento'] ?? '');
$horario          = trim($_POST['horario'] ?? '');
$status           = trim($_POST['status'] ?? 'pendente');
$observacoes      = trim($_POST['observacoes'] ?? '');

if (!$id || !$servico_id || !$barbeiro_id || empty($data_agendamento) || empty($horario)) {
    $_SESSION['erro_agendamento'] = 'Preencha todos os campos obrigatórios do agendamento.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    // Verifica conflito de horário ignorando o próprio agendamento em edição
    $check = $pdo->prepare('SELECT id FROM agendamentos WHERE barbeiro_id = :barbeiro_id AND data_agendamento = :data AND horario = :horario AND status != "cancelado" AND id != :id LIMIT 1');
    $check->execute([
        ':barbeiro_id' => $barbeiro_id,
        ':data'        => $data_agendamento,
        ':horario'     => $horario,
        ':id'          => $id
    ]);

    if ($check->fetch()) {
        $_SESSION['erro_agendamento'] = 'Este barbeiro já possui outro agendamento ativo neste dia e horário.';
        header('Location: ../pages/painel.php');
        exit;
    }

    $sql = 'UPDATE agendamentos 
            SET servico_id = :servico_id, barbeiro_id = :barbeiro_id, data_agendamento = :data, horario = :horario, status = :status, observacoes = :observacoes 
            WHERE id = :id';
    $pdo->prepare($sql)->execute([
        ':servico_id'  => $servico_id,
        ':barbeiro_id' => $barbeiro_id,
        ':data'        => $data_agendamento,
        ':horario'     => $horario,
        ':status'      => $status,
        ':observacoes' => $observacoes,
        ':id'          => $id
    ]);

    $_SESSION['sucesso_agendamento'] = 'Agendamento atualizado com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_agendamento'] = 'Erro ao atualizar agendamento: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}