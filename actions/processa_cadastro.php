<?php
session_start();

// Carrega a conexão com a base de dados
require_once __DIR__ . '/../config/conexao.php';

// Bloqueia acesso direto via GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/cadastro_usuario.php');
    exit;
}

// Sanitização básica dos dados recebidos
$nome           = trim($_POST['nome'] ?? '');
$email          = trim($_POST['email'] ?? '');
$telefone       = trim($_POST['telefone'] ?? '');
$senha          = trim($_POST['senha'] ?? '');
$confirma_senha = trim($_POST['confirma_senha'] ?? '');

// 1. Validação de preenchimento obrigatório
if (empty($nome) || empty($email) || empty($senha) || empty($confirma_senha)) {
    $_SESSION['erro_cadastro'] = 'Preencha todos os campos obrigatórios.';
    header('Location: ../pages/cadastro_usuario.php');
    exit;
}

// 2. Validação de e-mail válido
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro_cadastro'] = 'Formato de e-mail inválido.';
    header('Location: ../pages/cadastro_usuario.php');
    exit;
}

// 3. Validação de igualdade das senhas
if ($senha !== $confirma_senha) {
    $_SESSION['erro_cadastro'] = 'As senhas digitadas não coincidem.';
    header('Location: ../pages/cadastro_usuario.php');
    exit;
}

// 4. Regra de tamanho mínimo de senha
if (strlen($senha) < 3) {
    $_SESSION['erro_cadastro'] = 'A senha deve ter no mínimo 3 caracteres.';
    header('Location: ../pages/cadastro_usuario.php');
    exit;
}

try {
    // 5. Verifica se o e-mail já está registado na base de dados
    $stmtCheck = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $stmtCheck->execute([':email' => $email]);

    if ($stmtCheck->fetch()) {
        $_SESSION['erro_cadastro'] = 'Este e-mail já está em uso.';
        header('Location: ../pages/cadastro_usuario.php');
        exit;
    }

    // 6. Criptografia segura da senha com BCRYPT
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // 7. Inserção na tabela (tipo padrão sempre 'cliente')
    $sql = 'INSERT INTO usuarios (nome, email, telefone, senha, tipo) 
            VALUES (:nome, :email, :telefone, :senha, :tipo)';
    
    $stmtInsert = $pdo->prepare($sql);
    $stmtInsert->execute([
        ':nome'     => $nome,
        ':email'    => $email,
        ':telefone' => $telefone,
        ':senha'    => $senhaHash,
        ':tipo'     => 'cliente'
    ]);

    // Redireciona para o login com mensagem de sucesso
    $_SESSION['sucesso_login'] = 'Conta criada com sucesso! Faça login para continuar.';
    header('Location: ../index.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_cadastro'] = 'Erro ao cadastrar: ' . $e->getMessage();
    header('Location: ../pages/cadastro_usuario.php');
    exit;
}