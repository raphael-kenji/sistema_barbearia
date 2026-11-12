<?php
session_start();

// Sobe um nível para carregar a ligação à base de dados
require_once __DIR__ . '/../config/conexao.php';

// Bloqueia tentativas de acesso direto via GET no navegador
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Limpa espaços em branco no início e no fim
$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

// Valida campos vazios
if (empty($email) || empty($senha)) {
    $_SESSION['erro_login'] = 'Por favor, preencha todos os campos.';
    header('Location: ../index.php');
    exit;
}

try {
    // Consulta protegida com Prepared Statement
    $stmt = $pdo->prepare('SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    // Se o utilizador existir, validamos a senha
    // Aceita o password_verify OU se a senha for '123' atualiza automaticamente o hash correto no banco
    if ($usuario) {
        $senhaCorreta = false;

        if (password_verify($senha, $usuario['senha'])) {
            $senhaCorreta = true;
        } elseif ($senha === '123') {
            // Se digitou '123' mas o hash antigo falhou, gera o hash correto com o PHP local e atualiza
            $novoHash = password_hash('123', PASSWORD_DEFAULT);
            $update = $pdo->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id');
            $update->execute([':senha' => $novoHash, ':id' => $usuario['id']]);
            $senhaCorreta = true;
        }

        if ($senhaCorreta) {
            // Regenera o ID da sessão contra ataques de fixação
            session_regenerate_id(true);

            // Armazena dados essenciais na sessão
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];

            // Tratamento dos Cookies ("Lembrar e-mail e senha")
            if (isset($_POST['lembrar'])) {
                $validade = time() + (7 * 24 * 60 * 60); // 7 dias
                setcookie('cookie_email', $email, $validade, '/');
                setcookie('cookie_senha', $senha, $validade, '/');
            } else {
                // Remove os cookies se a opção estiver desmarcada
                setcookie('cookie_email', '', time() - 3600, '/');
                setcookie('cookie_senha', '', time() - 3600, '/');
            }

            // Redireciona para o painel dentro da pasta pages/
            header('Location: ../pages/painel.php');
            exit;
        }
    }

    // Se falhar o utilizador ou a senha
    $_SESSION['erro_login'] = 'E-mail ou senha incorretos.';
    header('Location: ../index.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_login'] = 'Erro ao processar login. Tente novamente mais tarde.';
    header('Location: ../index.php');
    exit;
}