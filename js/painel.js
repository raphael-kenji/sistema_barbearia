document.addEventListener('DOMContentLoaded', () => {
    // 1. Edição de Serviços
    document.querySelectorAll('.btn-editar-servico').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_nome').value = this.dataset.nome;
            document.getElementById('edit_descricao').value = this.dataset.descricao;
            document.getElementById('edit_preco').value = this.dataset.preco;
            document.getElementById('edit_duracao').value = this.dataset.duracao;
        });
    });

    // 2. Edição de Barbeiros
    document.querySelectorAll('.btn-editar-barbeiro').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_barb_id').value = this.dataset.id;
            document.getElementById('edit_barb_nome').value = this.dataset.nome;
            document.getElementById('edit_barb_email').value = this.dataset.email;
            document.getElementById('edit_barb_telefone').value = this.dataset.telefone;
            document.getElementById('edit_barb_senha').value = '';
        });
    });

    // 3. Edição de Clientes
    document.querySelectorAll('.btn-editar-cliente').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_cli_id').value = this.dataset.id;
            document.getElementById('edit_cli_nome').value = this.dataset.nome;
            document.getElementById('edit_cli_email').value = this.dataset.email;
            document.getElementById('edit_cli_telefone').value = this.dataset.telefone;
            document.getElementById('edit_cli_senha').value = '';
        });
    });

    // 4. Edição de Agendamentos
    document.querySelectorAll('.btn-editar-agendamento').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_ag_id').value = this.dataset.id;
            document.getElementById('edit_ag_servico').value = this.dataset.servico;
            document.getElementById('edit_ag_barbeiro').value = this.dataset.barbeiro;
            document.getElementById('edit_ag_data').value = this.dataset.data;
            document.getElementById('edit_ag_horario').value = this.dataset.horario;
            document.getElementById('edit_ag_status').value = this.dataset.status;
            document.getElementById('edit_ag_observacoes').value = this.dataset.observacoes;
        });
    });
});