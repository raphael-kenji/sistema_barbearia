<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// Trava de segurança: impede acesso direto sem autenticação
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro_login'] = 'Faça login para acessar esta página.';
    header('Location: ../index.php');
    exit;
}

// Carregamento de dados para o perfil de administrador
$servicos = [];
if ($_SESSION['usuario_tipo'] === 'admin') {
    $stmt = $pdo->query('SELECT * FROM servicos ORDER BY id DESC');
    $servicos = $stmt->fetchAll();
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

    <!-- Cabeçalho Principal e Navegação Semântica -->
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

        <!-- Área de Mensagens de Notificação/Feedback -->
        <section aria-label="Mensagens do Sistema">
            <?php if (isset($_SESSION['sucesso_servico'])): ?>
                <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                    <?= htmlspecialchars($_SESSION['sucesso_servico']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
                <?php unset($_SESSION['sucesso_servico']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['erro_servico'])): ?>
                <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                    <?= htmlspecialchars($_SESSION['erro_servico']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
                <?php unset($_SESSION['erro_servico']); ?>
            <?php endif; ?>
        </section>

        <!-- Seção Exclusiva: Administrador -->
        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>

            <section aria-labelledby="titulo-gestao-servicos">
                <header class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h4 mb-0 fw-bold" id="titulo-gestao-servicos">Gerenciamento de Serviços</h1>
                    <button type="button" class="btn btn-warning fw-semibold btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoServico">
                        <i class="bi bi-plus-circle me-1"></i> Novo Serviço
                    </button>
                </header>

                <article class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <caption class="visually-hidden">Tabela com a listagem dos serviços disponíveis na barbearia</caption>
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
                                                <!-- Botão Editar (Update) -->
                                                <button type="button" 
                                                        class="btn btn-outline-warning btn-sm me-1 btn-editar-servico"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditaServico"
                                                        data-id="<?= $servico['id']; ?>"
                                                        data-nome="<?= htmlspecialchars($servico['nome']); ?>"
                                                        data-descricao="<?= htmlspecialchars($servico['descricao'] ?? ''); ?>"
                                                        data-preco="<?= $servico['preco']; ?>"
                                                        data-duracao="<?= $servico['duracao_minutos']; ?>"
                                                        title="Editar serviço"
                                                        aria-label="Editar serviço <?= htmlspecialchars($servico['nome']); ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <!-- Botão Excluir (Delete) -->
                                                <a href="../actions/exclui_servico.php?id=<?= $servico['id']; ?>" 
                                                   class="btn btn-outline-danger btn-sm" 
                                                   onclick="return confirm('Deseja realmente excluir este serviço?');"
                                                   title="Excluir serviço"
                                                   aria-label="Excluir serviço <?= htmlspecialchars($servico['nome']); ?>">
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

            <!-- Diálogo Modal: Cadastro de Novo Serviço (Create) -->
            <div class="modal fade" id="modalNovoServico" tabindex="-1" aria-labelledby="modalNovoServicoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <form action="../actions/salva_servico.php" method="POST">
                            <header class="modal-header border-secondary">
                                <h2 class="modal-title fs-5 fw-bold" id="modalNovoServicoLabel">
                                    <i class="bi bi-scissors text-warning me-2"></i>Cadastrar Serviço
                                </h2>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
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

            <!-- Diálogo Modal: Edição de Serviço Existente (Update) -->
            <div class="modal fade" id="modalEditaServico" tabindex="-1" aria-labelledby="modalEditaServicoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <form action="../actions/atualiza_servico.php" method="POST">
                            <!-- ID oculto enviado via POST -->
                            <input type="hidden" id="edit_id" name="id">

                            <header class="modal-header border-secondary">
                                <h2 class="modal-title fs-5 fw-bold" id="modalEditaServicoLabel">
                                    <i class="bi bi-pencil-square text-warning me-2"></i>Editar Serviço
                                </h2>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
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

        <!-- Seção Exclusiva: Barbeiro -->
        <?php elseif ($_SESSION['usuario_tipo'] === 'barbeiro'): ?>
            <section aria-labelledby="titulo-agenda-barbeiro">
                <article class="card p-4 shadow-sm border-0">
                    <header class="mb-2">
                        <h2 class="h5 fw-bold" id="titulo-agenda-barbeiro">
                            <i class="bi bi-calendar-check text-warning me-2"></i>Agenda de Atendimentos
                        </h2>
                    </header>
                    <p class="text-muted mb-0">A sua lista de atendimentos será exibida aqui.</p>
                </article>
            </section>

        <!-- Seção Exclusiva: Cliente -->
        <?php else: ?>
            <section aria-labelledby="titulo-meus-agendamentos">
                <article class="card p-4 shadow-sm border-0">
                    <header class="mb-2">
                        <h2 class="h5 fw-bold" id="titulo-meus-agendamentos">
                            <i class="bi bi-clock-history text-warning me-2"></i>Meus Agendamentos
                        </h2>
                    </header>
                    <p class="text-muted mb-0">Aqui você poderá solicitar e acompanhar seus horários de corte e barba.</p>
                </article>
            </section>
        <?php endif; ?>

    </main>

    <!-- Scripts Bootstrap e Preenchimento Automático do Modal de Edição -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.btn-editar-servico').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('edit_nome').value = this.dataset.nome;
                document.getElementById('edit_descricao').value = this.dataset.descricao;
                document.getElementById('edit_preco').value = this.dataset.preco;
                document.getElementById('edit_duracao').value = this.dataset.duracao;
            });
        });
    </script>
</body>
</html>