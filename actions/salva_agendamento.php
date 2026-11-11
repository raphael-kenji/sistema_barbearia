<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// Apenas usuários logados podem agendar
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro_login'] = 'Faça login para realizar um agendamento.';
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/painel.php');
    exit;
}

$cliente_id  = $_SESSION['usuario_id'];
$barbeiro_id = filter_input(INPUT_POST, 'barbeiro_id', FILTER_VALIDATE_INT);
$servico_id  = filter_input(INPUT_POST, 'servico_id', FILTER_VALIDATE_INT);
$data        = trim($_POST['data_agendamento'] ?? '');
$horario     = trim($_POST['horario'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

// Validação dos campos obrigatórios
if (!$barbeiro_id || !$servico_id || empty($data) || empty($horario)) {
    $_SESSION['erro_agendamento'] = 'Preencha todos os campos obrigatórios.';
    header('Location: ../pages/painel.php');
    exit;
}

// Impede agendamento em datas passadas
if ($data < date('Y-m-d')) {
    $_SESSION['erro_agendamento'] = 'Não é possível agendar em datas passadas.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    // Regra de Negócio: Verifica se o barbeiro já tem agendamento no mesmo dia e horário
    $checkStmt = $pdo->prepare('
        SELECT id FROM agendamentos 
        WHERE barbeiro_id = :barbeiro_id 
          AND data_agendamento = :data 
          AND horario = :horario 
          AND status != "cancelado"
        LIMIT 1
    ');
    $checkStmt->execute([
        ':barbeiro_id' => $barbeiro_id,
        ':data'        => $data,
        ':horario'     => $horario
    ]);

    if ($checkStmt->fetch()) {
        $_SESSION['erro_agendamento'] = 'Este barbeiro já possui um agendamento marcado para este dia e horário.';
        header('Location: ../pages/painel.php');
        exit;
    }

    // Insere o agendamento
    $sql = 'INSERT INTO agendamentos (cliente_id, barbeiro_id, servico_id, data_agendamento, horario, observacoes) 
            VALUES (:cliente_id, :barbeiro_id, :servico_id, :data, :horario, :observacoes)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cliente_id'  => $cliente_id,
        ':barbeiro_id' => $barbeiro_id,
        ':servico_id'  => $servico_id,
        ':data'        => $data,
        ':horario'     => $horario,
        ':observacoes' => $observacoes
    ]);

    $_SESSION['sucesso_agendamento'] = 'Agendamento solicitado com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_agendamento'] = 'Erro ao realizar agendamento: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}