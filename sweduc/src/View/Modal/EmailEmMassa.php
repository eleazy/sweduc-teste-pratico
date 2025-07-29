<!-- modal de email em massa -->
<div id="dialog-email-massa" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Email</h4>
            </div>
            <div class="modal-body" style="padding-bottom: 0;">
                <div class="row">
                    <div class="col-lg-12 text-center text-primary">
                        <i class="far fa-envelope fa-5x"></i>
                        <h3 class="text-muted">Enviar Email</h3>
                    </div>
                    <div class="col-lg-12 text-muted" style="border-top: 1px solid #e5e5e5; margin-top: 15px; background-color: #F8FAFB; padding-top: 20px; padding-bottom: 20px;">
                        <form id="email-em-massa-form" class="row">
                            <div class="col-sm-12" style="margin-bottom: 10px;">
                                <label for="acaoEmailAssunto"><span></span>Assunto</label><br />
                                <input type="text" name="assunto" id="acaoEmailAssunto" class="form-control">
                            </div>
                            <div class="col-sm-6">
                                <input type="checkbox" name="aluno" id="acaoEmailAluno" />
                                <label for="acaoEmailAluno"><span></span>Aluno</label><br />
                            </div>
                            <div class="col-sm-6">
                                <input type="checkbox" name="pai" id="acaoEmailPai"  />
                                <label for="acaoEmailPai"><span></span>Pai</label>
                            </div>
                            <div class="col-sm-6">
                                <input type="checkbox" name="mae" id="acaoEmailMae"  />
                                <label for="acaoEmailMae"><span></span>Mãe</label>
                            </div>
                            <div class="col-sm-6">
                                <input type="checkbox" name="respfin" id="acaoEmailRespFin"  />
                                <label for="acaoEmailRespFin"><span></span>Responsável Financeiro</label>
                            </div>
                            <div class="col-sm-6">
                                <input type="checkbox" name="resppedag" id="acaoEmailRespPedag"  />
                                <label for="acaoEmailRespPedag"><span></span>Responsável Pedagógico</label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn grey-color" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn primary-color" data-dismiss="modal" onclick="enviarEmaisEmMassa()">Abrir</button>
            </div>
        </div>
    </div>
</div>
<!-- fim do modal de email em massa -->
