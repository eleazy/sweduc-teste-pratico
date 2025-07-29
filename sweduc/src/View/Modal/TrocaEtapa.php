<!-- modal de troca de etapa -->
<div id="dialog-troca-turma" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fas fa-users-cog"></i> Troca de etapa</h4>
            </div>
            <div class="modal-body">
                <p class="text-center h4">Trocando <span id="tt-contagem-alunos" class="text-primary"></span> de turma</p>
                <hr>
                <p class="text-center"><strong>Selecione a turma destino</strong></p>
                <div class="tt-normal">
                    <div class="form-group">
                        <label for="">Unidade</label>
                        <select name="unidade" id="tt-unidade" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label for="">Curso</label>
                        <select name="curso" id="tt-curso" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label for="">Serie</label>
                        <select name="serie" id="tt-serie" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label for="">Turma</label>
                        <select name="turma" id="tt-turma" class="form-control"></select>
                    </div>
                </div>
                <div class="tt-autocomplete hidden">
                    <input class="form-control" type="text" name="turma" id="tt-turma-autoc">
                </div>
                <input type="hidden" name="tt-turma" id="tt-turma-autoc-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link tt-alternar-busca">Busca por nome</button>
                <button type="button" class="btn btn-link tt-alternar-busca hidden">Busca normal</button>
                <button type="button" class="btn grey-color" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn primary-color" onclick="trocarTurma()">Trocar</button>
            </div>
        </div>
    </div>
</div>
<!-- fim do modal troca de etapa -->
