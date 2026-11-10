<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// Trava de segurança: apenas admin pode atualizar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    $_SESSION['erro_login'] = 'Acesso não autorizado.';
    header('Location: ../pages/painel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/painel.php');
    exit;
}

$id              = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nome            = trim($_POST['nome'] ?? '');
$descricao       = trim($_POST['descricao'] ?? '');
$preco           = str_replace(',', '.', trim($_POST['preco'] ?? ''));
$duracao_minutos = filter_var($_POST['duracao_minutos'] ?? '', FILTER_VALIDATE_INT);

// Validação dos dados
if (!$id || empty($nome) || !is_numeric($preco) || $duracao_minutos === false || $duracao_minutos <= 0) {
    $_SESSION['erro_servico'] = 'Preencha os campos com valores válidos para atualizar.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    $sql = 'UPDATE servicos 
            SET nome = :nome, descricao = :descricao, preco = :preco, duracao_minutos = :duracao 
            WHERE id = :id';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'      => $nome,
        ':descricao' => $descricao,
        ':preco'     => $preco,
        ':duracao'   => $duracao_minutos,
        ':id'        => $id
    ]);

    $_SESSION['sucesso_servico'] = 'Serviço atualizado com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_servico'] = 'Erro ao atualizar serviço: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}