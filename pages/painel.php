<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// Trava de segurança: impede acesso direto sem autenticação
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro_login'] = 'Faça login para acessar esta página.';
    header('Location: ../index.php');
    exit;
}

$usuario_id   = $_SESSION['usuario_id'];
$usuario_tipo = $_SESSION['usuario_tipo'];

// Carrega os dados de acordo com o perfil
$servicos     = [];
$barbeiros    = [];
$agendamentos = [];

if ($usuario_tipo === 'admin') {
    // Lista de serviços para o CRUD do Admin
    $stmt = $pdo->query('SELECT * FROM servicos ORDER BY id DESC');
    $servicos = $stmt->fetchAll();

    // Lista de barbeiros cadastrados
    $stmtB = $pdo->query('SELECT id, nome, email, telefone FROM usuarios WHERE tipo = "barbeiro" ORDER BY id DESC');
    $barbeiros = $stmtB->fetchAll();

    // Visão geral de todos os agendamentos para o Admin
    $sqlAg = 'SELECT a.*, s.nome AS servico_nome, s.preco, c.nome AS cliente_nome, b.nome AS barbeiro_nome 
              FROM agendamentos a
              JOIN servicos s ON a.servico_id = s.id
              JOIN usuarios c ON a.cliente_id = c.id
              JOIN usuarios b ON a.barbeiro_id = b.id
              ORDER BY a.data_agendamento DESC, a.horario ASC';
    $agendamentos = $pdo->query($sqlAg)->fetchAll();

} elseif ($usuario_tipo === 'barbeiro') {
    // Agenda de atendimentos exclusiva deste barbeiro
    $sqlAg = 'SELECT a.*, s.nome AS servico_nome, s.preco, c.nome AS cliente_nome, c.telefone AS cliente_telefone
              FROM agendamentos a
              JOIN servicos s ON a.servico_id = s.id
              JOIN usuarios c ON a.cliente_id = c.id
              WHERE a.barbeiro_id = :barbeiro_id
              ORDER BY a.data_agendamento DESC, a.horario ASC';
    $stmt = $pdo->prepare($sqlAg);
    $stmt->execute([':barbeiro_id' => $usuario_id]);
    $agendamentos = $stmt->fetchAll();

} else {
    // Perfil Cliente: carregar lista dos seus agendamentos
    $sqlAg = 'SELECT a.*, s.nome AS servico_nome, s.preco, b.nome AS barbeiro_nome 
              FROM agendamentos a
              JOIN servicos s ON a.servico_id = s.id
              JOIN usuarios b ON a.barbeiro_id = b.id
              WHERE a.cliente_id = :cliente_id
              ORDER BY a.data_agendamento DESC, a.horario ASC';
    $stmt = $pdo->prepare($sqlAg);
    $stmt->execute([':cliente_id' => $usuario_id]);
    $agendamentos = $stmt->fetchAll();

    // Carrega serviços e barbeiros disponíveis para o formulário de agendamento
    $servicos = $pdo->query('SELECT id, nome, preco, duracao_minutos FROM servicos ORDER BY nome ASC')->fetchAll();
    $stmtB = $pdo->prepare('SELECT id, nome FROM usuarios WHERE tipo = "barbeiro" ORDER BY nome ASC');
    $stmtB->execute();
    $barbeiros = $stmtB->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Barbearia Clássica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">

    <!-- Cabeçalho Principal -->
    <header>
        <nav class="navbar navbar-dark bg-dark px-4 shadow-sm" aria-label="Navegação do Painel">
            <div class="container-fluid px-0">
                <span class="navbar-brand fw-bold mb-0">
                    <i class="bi bi-scissors text-warning me-2"></i>Barbearia Clássica
                </span>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-light small">
                        Olá, <strong><?= htmlspecialchars($_SESSION['usuario_nome']); ?></strong> 
                        <span class="badge bg-secondary"><?= htmlspecialchars($_SESSION['usuario_tipo']); ?></span>
                    </span>
                    <a href="../actions/logout.php" class="btn btn-outline-danger btn-sm" role="button">
                        <i class="bi bi-box-arrow-right"></i> Sair
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Conteúdo Central -->
    <main class="container my-4">

        <!-- Mensagens de Feedback -->
        <section aria-label="Mensagens do Sistema">
            <?php foreach (['sucesso_servico', 'erro_servico', 'sucesso_agendamento', 'erro_agendamento', 'sucesso_barbeiro', 'erro_barbeiro'] as $msgKey): ?>
                <?php if (isset($_SESSION[$msgKey])): ?>
                    <div class="alert alert-<?= strpos($msgKey, 'sucesso') !== false ? 'success' : 'danger'; ?> alert-dismissible fade show small py-2" role="alert">
                        <?= htmlspecialchars($_SESSION[$msgKey]); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                    <?php unset($_SESSION[$msgKey]); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

        <!-- VISÃO: ADMINISTRADOR -->
        <?php if ($usuario_tipo === 'admin'): ?>

            <!-- 1. Gestão de Serviços -->
            <section class="mb-5" aria-labelledby="titulo-gestao-servicos">
                <header class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h4 mb-0 fw-bold" id="titulo-gestao-servicos">Gerenciamento de Serviços</h1>
                    <button type="button" class="btn btn-warning fw-semibold btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoServico">
                        <i class="bi bi-plus-circle me-1"></i> Novo Serviço
                    </button>
                </header>

                <article class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <caption class="visually-hidden">Tabela de serviços cadastrados</caption>
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Serviço</th>
                                    <th scope="col">Descrição</th>
                                    <th scope="col">Preço</th>
                                    <th scope="col">Duração</th>
                                    <th scope="col" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($servicos)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Nenhum serviço cadastrado até o momento.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($servicos as $servico): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($servico['id']); ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($servico['nome']); ?></td>
                                            <td class="text-muted small"><?= htmlspecialchars($servico['descricao'] ?? '-'); ?></td>
                                            <td>R$ <?= number_format($servico['preco'], 2, ',', '.'); ?></td>
                                            <td><?= htmlspecialchars($servico['duracao_minutos']); ?> min</td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-outline-warning btn-sm me-1 btn-editar-servico"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditaServico"
                                                        data-id="<?= $servico['id']; ?>"
                                                        data-nome="<?= htmlspecialchars($servico['nome']); ?>"
                                                        data-descricao="<?= htmlspecialchars($servico['descricao'] ?? ''); ?>"
                                                        data-preco="<?= $servico['preco']; ?>"
                                                        data-duracao="<?= $servico['duracao_minutos']; ?>"
                                                        title="Editar serviço">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="../actions/exclui_servico.php?id=<?= $servico['id']; ?>" 
                                                   class="btn btn-outline-danger btn-sm" 
                                                   onclick="return confirm('Deseja realmente excluir este serviço?');"
                                                   title="Excluir serviço">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <!-- 2. Gestão da Equipa (Barbeiros) -->
            <section class="mb-5" aria-labelledby="titulo-gestao-barbeiros">
                <header class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0 fw-bold" id="titulo-gestao-barbeiros">Equipa de Barbeiros</h2>
                    <button type="button" class="btn btn-warning fw-semibold btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoBarbeiro">
                        <i class="bi bi-person-plus me-1"></i> Novo Barbeiro
                    </button>
                </header>

                <article class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <caption class="visually-hidden">Tabela de barbeiros cadastrados</caption>
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nome</th>
                                    <th scope="col">E-mail</th>
                                    <th scope="col">Telefone</th>
                                    <th scope="col" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($barbeiros)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Nenhum barbeiro cadastrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($barbeiros as $barbeiro): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($barbeiro['id']); ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($barbeiro['nome']); ?></td>
                                            <td><?= htmlspecialchars($barbeiro['email']); ?></td>
                                            <td><?= htmlspecialchars($barbeiro['telefone'] ?? '-'); ?></td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-outline-warning btn-sm me-1 btn-editar-barbeiro"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditaBarbeiro"
                                                        data-id="<?= $barbeiro['id']; ?>"
                                                        data-nome="<?= htmlspecialchars($barbeiro['nome']); ?>"
                                                        data-email="<?= htmlspecialchars($barbeiro['email']); ?>"
                                                        data-telefone="<?= htmlspecialchars($barbeiro['telefone'] ?? ''); ?>"
                                                        title="Editar barbeiro">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="../actions/exclui_barbeiro.php?id=<?= $barbeiro['id']; ?>" 
                                                   class="btn btn-outline-danger btn-sm" 
                                                   onclick="return confirm('Deseja realmente excluir este barbeiro?');"
                                                   title="Excluir barbeiro">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <!-- 3. Agendamentos Gerais para o Admin -->
            <section aria-labelledby="titulo-todos-agendamentos">
                <header class="mb-3">
                    <h2 class="h4 mb-0 fw-bold" id="titulo-todos-agendamentos">Todos os Agendamentos</h2>
                </header>
                <article class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <caption class="visually-hidden">Tabela geral de agendamentos</caption>
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Data / Hora</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Barbeiro</th>
                                    <th scope="col">Serviço</th>
                                    <th scope="col">Valor</th>
                                    <th scope="col">Observações</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($agendamentos)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">Nenhum agendamento registrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($agendamentos as $ag): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($ag['id']); ?></td>
                                            <td><?= date('d/m/Y', strtotime($ag['data_agendamento'])); ?> às <?= substr($ag['horario'], 0, 5); ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($ag['cliente_nome']); ?></td>
                                            <td><?= htmlspecialchars($ag['barbeiro_nome']); ?></td>
                                            <td><?= htmlspecialchars($ag['servico_nome']); ?></td>
                                            <td>R$ <?= number_format($ag['preco'], 2, ',', '.'); ?></td>
                                            <td class="text-muted small"><?= !empty($ag['observacoes']) ? htmlspecialchars($ag['observacoes']) : '<span class="text-secondary">-</span>'; ?></td>
                                            <td>
                                                <span class="badge bg-<?= $ag['status'] === 'concluido' ? 'success' : ($ag['status'] === 'cancelado' ? 'danger' : 'warning text-dark'); ?>">
                                                    <?= ucfirst($ag['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <!-- Modal: Cadastro de Novo Barbeiro -->
            <div class="modal fade" id="modalNovoBarbeiro" tabindex="-1" aria-labelledby="modalNovoBarbeiroLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <form action="../actions/salva_barbeiro.php" method="POST">
                            <header class="modal-header border-secondary">
                                <h2 class="modal-title fs-5 fw-bold" id="modalNovoBarbeiroLabel">
                                    <i class="bi bi-person-plus text-warning me-2"></i>Cadastrar Barbeiro
                                </h2>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </header>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="barb_nome" class="form-label small fw-semibold text-light">Nome Completo *</label>
                                    <input type="text" class="form-control bg-dark text-white border-secondary" id="barb_nome" name="nome" placeholder="Ex: João Silva" required>
                                </div>
                                <div class="mb-3">
                                    <label for="barb_email" class="form-label small fw-semibold text-light">E-mail de Acesso *</label>
                                    <input type="email" class="form-control bg-dark text-white border-secondary" id="barb_email" name="email" placeholder="joao@barbearia.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="barb_telefone" class="form-label small fw-semibold text-light">Telefone / WhatsApp</label>
                                    <input type="text" class="form-control bg-dark text-white border-secondary" id="barb_telefone" name="telefone" placeholder="18999990000">
                                </div>
                                <div class="mb-3">
                                    <label for="barb_senha" class="form-label small fw-semibold text-light">Senha Provisória *</label>
                                    <input type="password" class="form-control bg-dark text-white border-secondary" id="barb_senha" name="senha" placeholder="Mínimo 6 caracteres" required>
                                </div>
                            </div>
                            <footer class="modal-footer border-secondary">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-warning btn-sm fw-bold">Cadastrar Barbeiro</button>
                            </footer>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal: Edição de Barbeiro -->
            <div class="modal fade" id="modalEditaBarbeiro" tabindex="-1" aria-labelledby="modalEditaBarbeiroLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <form action="../actions/atualiza_barbeiro.php" method="POST">
                            <input type="hidden" id="edit_barb_id" name="id">
                            <header class="modal-header border-secondary">
                                <h2 class="modal-title fs-5 fw-bold" id="modalEditaBarbeiroLabel">
                                    <i class="bi bi-person-gear text-warning me-2"></i>Editar Barbeiro
                                </h2>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </header>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="edit_barb_nome" class="form-label small fw-semibold text-light">Nome Completo *</label>
                                    <input type="text" class="form-control bg-dark text-white border-secondary" id="edit_barb_nome" name="nome" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_barb_email" class="form-label small fw-semibold text-light">E-mail de Acesso *</label>
                                    <input type="email" class="form-control bg-dark text-white border-secondary" id="edit_barb_email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_barb_telefone" class="form-label small fw-semibold text-light">Telefone / WhatsApp</label>
                                    <input type="text" class="form-control bg-dark text-white border-secondary" id="edit_barb_telefone" name="telefone">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_barb_senha" class="form-label small fw-semibold text-light">Nova Senha <span class="text-secondary fw-normal">(deixe em branco para manter a atual)</span></label>
                                    <input type="password" class="form-control bg-dark text-white border-secondary" id="edit_barb_senha" name="senha" placeholder="Mínimo 6 caracteres">
                                </div>
                            </div>
                            <footer class="modal-footer border-secondary">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-warning btn-sm fw-bold">Salvar Alterações</button>
                            </footer>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal: Cadastro de Serviço -->
            <div class="modal fade" id="modalNovoServico" tabindex="-1" aria-labelledby="modalNovoServicoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <form action="../actions/salva_servico.php" method="POST">
                            <header class="modal-header border-secondary">
                                <h2 class="modal-title fs-5 fw-bold" id="modalNovoServicoLabel">Cadastrar Serviço</h2>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </header>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="nome" class="form-label small fw-semibold text-light">Nome do Serviço *</label>
                                    <input type="text" class="form-control bg-dark text-white border-secondary" id="nome" name="nome" placeholder="Ex: Barboterapia" required>
                                </div>
                                <div class="mb-3">
                                    <label for="descricao" class="form-label small fw-semibold text-light">Descrição</label>
                                    <textarea class="form-control bg-dark text-white border-secondary" id="descricao" name="descricao" rows="2" placeholder="Breve detalhe do atendimento"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label for="preco" class="form-label small fw-semibold text-light">Preço (R$) *</label>
                                        <input type="number" step="0.01" class="form-control bg-dark text-white border-secondary" id="preco" name="preco" placeholder="0.00" required>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="duracao_minutos" class="form-label small fw-semibold text-light">Duração (minutos) *</label>
                                        <input type="number" class="form-control bg-dark text-white border-secondary" id="duracao_minutos" name="duracao_minutos" value="30" step="5" required>
                                    </div>
                                </div>
                            </div>
                            <footer class="modal-footer border-secondary">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-warning btn-sm fw-bold">Salvar Serviço</button>
                            </footer>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal: Edição de Serviço -->
            <div class="modal fade" id="modalEditaServico" tabindex="-1" aria-labelledby="modalEditaServicoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <form action="../actions/atualiza_servico.php" method="POST">
                            <input type="hidden" id="edit_id" name="id">
                            <header class="modal-header border-secondary">
                                <h2 class="modal-title fs-5 fw-bold" id="modalEditaServicoLabel">Editar Serviço</h2>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </header>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="edit_nome" class="form-label small fw-semibold text-light">Nome do Serviço *</label>
                                    <input type="text" class="form-control bg-dark text-white border-secondary" id="edit_nome" name="nome" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_descricao" class="form-label small fw-semibold text-light">Descrição</label>
                                    <textarea class="form-control bg-dark text-white border-secondary" id="edit_descricao" name="descricao" rows="2"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label for="edit_preco" class="form-label small fw-semibold text-light">Preço (R$) *</label>
                                        <input type="number" step="0.01" class="form-control bg-dark text-white border-secondary" id="edit_preco" name="preco" required>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="edit_duracao" class="form-label small fw-semibold text-light">Duração (minutos) *</label>
                                        <input type="number" class="form-control bg-dark text-white border-secondary" id="edit_duracao" name="duracao_minutos" step="5" required>
                                    </div>
                                </div>
                            </div>
                            <footer class="modal-footer border-secondary">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-warning btn-sm fw-bold">Salvar Alterações</button>
                            </footer>
                        </form>
                    </div>
                </div>
            </div>

        <!-- VISÃO: BARBEIRO -->
        <?php elseif ($usuario_tipo === 'barbeiro'): ?>

            <section aria-labelledby="titulo-agenda-barbeiro">
                <header class="mb-3">
                    <h1 class="h4 mb-0 fw-bold" id="titulo-agenda-barbeiro">
                        <i class="bi bi-calendar-check text-warning me-2"></i>Minha Agenda de Atendimentos
                    </h1>
                </header>

                <article class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <caption class="visually-hidden">Tabela de agendamentos do barbeiro</caption>
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Data / Hora</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Telefone</th>
                                    <th scope="col">Serviço</th>
                                    <th scope="col">Valor</th>
                                    <th scope="col">Observações</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($agendamentos)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">Você não possui atendimentos marcados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($agendamentos as $ag): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($ag['data_agendamento'])); ?> às <?= substr($ag['horario'], 0, 5); ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($ag['cliente_nome']); ?></td>
                                            <td><?= htmlspecialchars($ag['cliente_telefone'] ?? '-'); ?></td>
                                            <td><?= htmlspecialchars($ag['servico_nome']); ?></td>
                                            <td>R$ <?= number_format($ag['preco'], 2, ',', '.'); ?></td>
                                            <td class="text-muted small"><?= !empty($ag['observacoes']) ? htmlspecialchars($ag['observacoes']) : '<span class="text-secondary">-</span>'; ?></td>
                                            <td>
                                                <span class="badge bg-<?= $ag['status'] === 'concluido' ? 'success' : ($ag['status'] === 'cancelado' ? 'danger' : 'warning text-dark'); ?>">
                                                    <?= ucfirst($ag['status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($ag['status'] === 'pendente'): ?>
                                                    <a href="../actions/cancela_agendamento.php?id=<?= $ag['id']; ?>&status=concluido" 
                                                       class="btn btn-outline-success btn-sm me-1" 
                                                       title="Marcar como concluído"
                                                       onclick="return confirm('Confirmar atendimento como concluído?');">
                                                        <i class="bi bi-check-lg"></i>
                                                    </a>
                                                    <a href="../actions/cancela_agendamento.php?id=<?= $ag['id']; ?>&status=cancelado" 
                                                       class="btn btn-outline-danger btn-sm" 
                                                       title="Cancelar atendimento"
                                                       onclick="return confirm('Deseja realmente cancelar este atendimento?');">
                                                        <i class="bi bi-x-lg"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

        <!-- VISÃO: CLIENTE -->
        <?php else: ?>

            <section aria-labelledby="titulo-meus-agendamentos">
                <header class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h4 mb-0 fw-bold" id="titulo-meus-agendamentos">
                        <i class="bi bi-clock-history text-warning me-2"></i>Meus Agendamentos
                    </h1>
                    <button type="button" class="btn btn-warning fw-semibold btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoAgendamento">
                        <i class="bi bi-calendar-plus me-1"></i> Agendar Horário
                    </button>
                </header>

                <article class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <caption class="visually-hidden">Tabela de agendamentos do cliente</caption>
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Data / Hora</th>
                                    <th scope="col">Serviço</th>
                                    <th scope="col">Valor</th>
                                    <th scope="col">Barbeiro</th>
                                    <th scope="col">Observações</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($agendamentos)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">Você ainda não tem agendamentos marcados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($agendamentos as $ag): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($ag['data_agendamento'])); ?> às <?= substr($ag['horario'], 0, 5); ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($ag['servico_nome']); ?></td>
                                            <td>R$ <?= number_format($ag['preco'], 2, ',', '.'); ?></td>
                                            <td><?= htmlspecialchars($ag['barbeiro_nome']); ?></td>
                                            <td class="text-muted small"><?= !empty($ag['observacoes']) ? htmlspecialchars($ag['observacoes']) : '<span class="text-secondary">-</span>'; ?></td>
                                            <td>
                                                <span class="badge bg-<?= $ag['status'] === 'concluido' ? 'success' : ($ag['status'] === 'cancelado' ? 'danger' : 'warning text-dark'); ?>">
                                                    <?= ucfirst($ag['status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($ag['status'] === 'pendente'): ?>
                                                    <a href="../actions/cancela_agendamento.php?id=<?= $ag['id']; ?>&status=cancelado" 
                                                       class="btn btn-outline-danger btn-sm"
                                                       onclick="return confirm('Deseja realmente cancelar seu agendamento?');"
                                                       title="Cancelar">
                                                        <i class="bi bi-x-circle"></i> Cancelar
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <!-- Modal: Novo Agendamento (Cliente) -->
            <div class="modal fade" id="modalNovoAgendamento" tabindex="-1" aria-labelledby="modalNovoAgendamentoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <form action="../actions/salva_agendamento.php" method="POST">
                            <header class="modal-header border-secondary">
                                <h2 class="modal-title fs-5 fw-bold" id="modalNovoAgendamentoLabel">
                                    <i class="bi bi-calendar-plus text-warning me-2"></i>Agendar Horário
                                </h2>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </header>
                            <div class="modal-body">
                                
                                <div class="mb-3">
                                    <label for="servico_id" class="form-label small fw-semibold text-light">Serviço *</label>
                                    <select class="form-select bg-dark text-white border-secondary" id="servico_id" name="servico_id" required>
                                        <option value="" disabled selected>Selecione um serviço</option>
                                        <?php foreach ($servicos as $srv): ?>
                                            <option value="<?= $srv['id']; ?>">
                                                <?= htmlspecialchars($srv['nome']); ?> - R$ <?= number_format($srv['preco'], 2, ',', '.'); ?> (<?= $srv['duracao_minutos']; ?> min)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="barbeiro_id" class="form-label small fw-semibold text-light">Barbeiro *</label>
                                    <select class="form-select bg-dark text-white border-secondary" id="barbeiro_id" name="barbeiro_id" required>
                                        <option value="" disabled selected>Selecione o barbeiro</option>
                                        <?php foreach ($barbeiros as $brb): ?>
                                            <option value="<?= $brb['id']; ?>"><?= htmlspecialchars($brb['nome']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label for="data_agendamento" class="form-label small fw-semibold text-light">Data *</label>
                                        <input type="date" class="form-control bg-dark text-white border-secondary" id="data_agendamento" name="data_agendamento" min="<?= date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="horario" class="form-label small fw-semibold text-light">Horário *</label>
                                        <select class="form-select bg-dark text-white border-secondary" id="horario" name="horario" required>
                                            <option value="" disabled selected>Horário</option>
                                            <option value="09:00:00">09:00</option>
                                            <option value="10:00:00">10:00</option>
                                            <option value="11:00:00">11:00</option>
                                            <option value="13:00:00">13:00</option>
                                            <option value="14:00:00">14:00</option>
                                            <option value="15:00:00">15:00</option>
                                            <option value="16:00:00">16:00</option>
                                            <option value="17:00:00">17:00</option>
                                            <option value="18:00:00">18:00</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="observacoes" class="form-label small fw-semibold text-light">Observações</label>
                                    <textarea class="form-control bg-dark text-white border-secondary" id="observacoes" name="observacoes" rows="2" placeholder="Alguma preferência especial?"></textarea>
                                </div>

                            </div>
                            <footer class="modal-footer border-secondary">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-warning btn-sm fw-bold">Confirmar Agendamento</button>
                            </footer>
                        </form>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/painel.js"></script>
</body>
</html>