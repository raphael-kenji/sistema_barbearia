document.addEventListener('DOMContentLoaded', () => {
    // Preenchimento do modal de edição de Serviços
    document.querySelectorAll('.btn-editar-servico').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_nome').value = this.dataset.nome;
            document.getElementById('edit_descricao').value = this.dataset.descricao;
            document.getElementById('edit_preco').value = this.dataset.preco;
            document.getElementById('edit_duracao').value = this.dataset.duracao;
        });
    });

    // Preenchimento do modal de edição de Barbeiros
    document.querySelectorAll('.btn-editar-barbeiro').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_barb_id').value = this.dataset.id;
            document.getElementById('edit_barb_nome').value = this.dataset.nome;
            document.getElementById('edit_barb_email').value = this.dataset.email;
            document.getElementById('edit_barb_telefone').value = this.dataset.telefone;
            document.getElementById('edit_barb_senha').value = '';
        });
    });
});