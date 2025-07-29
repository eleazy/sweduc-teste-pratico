<!-- modal de ocorrências -->
<div id="dialog-busca-ocorrencias" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Busca de Ocorrências</h4>
            </div>
            <div class="modal-body" style="padding-bottom: 0;">
                <div class="row">
                    <div class="col-lg-12 text-center text-primary">
                        <i class="fas fa-bullhorn fa-5x"></i>
                        <h3 class="text-muted">Busca de Ocorrências</h3>
                    </div>
                    <div class="col-lg-12 text-muted" style="border-top: 1px solid #e5e5e5; margin-top: 15px; background-color: #F8FAFB; padding-top: 20px; padding-bottom: 20px;">
                        <div class="row">
                            <div class="col-lg-4">
                                <label>Período</label>
                                <input type="text" class="form-control date" id="datadeOcorrencias" value="01/<?= date('m/Y') ?>" />
                            </div>
                            <div class="col-lg-4">
                                <label>&nbsp;</label>
                                <input type="text" class="form-control date" id="dataateOcorrencias" value="<?= date('d/m/Y') ?>" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn grey-color" data-dismiss="modal">Fechar</button>
                <button type="button" id="bt-lista-ocorrencias" class="btn primary-color" data-dismiss="modal">Listar</button>
            </div>
        </div>
    </div>
</div>
<!-- fim do modal de ocorrências -->
