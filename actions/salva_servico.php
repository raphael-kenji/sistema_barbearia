<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// Trava de segurança: apenas admin autenticado pode cadastrar serviços
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    $_SESSION['erro_login'] = 'Acesso não autorizado.';
    header('Location: ../pages/painel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/painel.php');
    exit;
}

$nome            = trim($_POST['nome'] ?? '');
$descricao       = trim($_POST['descricao'] ?? '');
$preco           = str_replace(',', '.', trim($_POST['preco'] ?? ''));
$duracao_minutos = filter_var($_POST['duracao_minutos'] ?? '', FILTER_VALIDATE_INT);

// Validação dos campos obrigatórios
if (empty($nome) || !is_numeric($preco) || $duracao_minutos === false || $duracao_minutos <= 0) {
    $_SESSION['erro_servico'] = 'Preencha os campos obrigatórios com valores válidos.';
    header('Location: ../pages/painel.php');
    exit;
}

try {
    $sql = 'INSERT INTO servicos (nome, descricao, preco, duracao_minutos) 
            VALUES (:nome, :descricao, :preco, :duracao_minutos)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'            => $nome,
        ':descricao'       => $descricao,
        ':preco'           => $preco,
        ':duracao_minutos' => $duracao_minutos
    ]);

    $_SESSION['sucesso_servico'] = 'Serviço cadastrado com sucesso!';
    header('Location: ../pages/painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_servico'] = 'Erro ao salvar serviço: ' . $e->getMessage();
    header('Location: ../pages/painel.php');
    exit;
}