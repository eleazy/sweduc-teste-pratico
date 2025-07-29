<!-- modal de email -->
<div id="dialog-email-aluno" class="modal fade" tabindex="-1" role="dialog">
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
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="checkbox" name="aluno" id="alunoMail" value="1" />
                                <label for="alunoMail"><span></span>Aluno</label><br />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="checkbox" name="pai" id="paiMail" value="1"  />
                                <label for="paiMail"><span></span>Pai</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="checkbox" name="mae" id="maeMail" value="1"  />
                                <label for="maeMail"><span></span>Mãe</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="checkbox" name="respfin" id="respfinMail" value="1"  />
                                <label for="respfinMail"><span></span>Responsável Financeiro</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="checkbox" name="resppedag" id="resppedagMail" value="1"  />
                                <label for="resppedagMail"><span></span>Responsável Pedagógico</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn grey-color" data-dismiss="modal">Fechar</button>
                <button type="button" id="bt-envia-email" class="btn primary-color" data-dismiss="modal">Abrir</button>
            </div>
        </div>
    </div>
</div>
<!-- fim do modal email -->
