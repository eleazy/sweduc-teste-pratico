<!-- modal de mensagem -->
<div id="dialog-enviar-mensagem-aluno" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Mensagem</h4>
            </div>
            <div class="modal-body" style="padding-bottom: 0;">
                <div class="text-center text-primary">
                    <i class="glyphicon glyphicon-comment fa-5x"></i>
                    <h3 class="text-muted">Enviar Mensagem</h3>
                </div>
                <!-- DIV ESCONDIDA NO LISTAR.PHP -->
                <div id="div-campo-envia-para" class="text-muted" style="border-top: 1px solid #e5e5e5; margin-top: 15px; background-color: #F8FAFB; padding-top: 20px; padding-bottom: 0px;">
                    <label for='assunto-campo' class='form-label'>Destinatários:</label>
                    <div>
                        <input type="checkbox" name="aluno" id="alunoMensagem" checked />
                        <label for="alunoMensagem"><span></span>Aluno</label><br />
                    </div>
                    <div>
                        <input type="checkbox" name="respfin" id="respfinMensagem" checked />
                        <label for="respfinMensagem"><span></span>Responsável Financeiro</label>
                    </div>
                    <div>
                        <input type="checkbox" name="resppedag" id="resppedagMensagem" checked />
                        <label for="resppedagMensagem"><span></span>Responsável Pedagógico</label>
                    </div>
                </div>
                <!-- FIM DIV ESCONDIDA NO LISTAR.PHP -->
                <div class="text-muted" style="border-top: 1px solid #e5e5e5; margin-top: 0px; background-color: #F8FAFB; padding-top: 10px; padding-bottom: 20px;">
                    <div style="margin-top: 10px;">
                        <div class="green-border-focus">
                            <label for='mensagem-predefinida' class='form-label'>Mensagens Predefinidas:</label>
                            <select name="mensagem-predefinida" id="mensagem-predefinida" class="form-element">
                                <option
                                    selected
                                    value="0"
                                >
                                    SEM PREDEFINIÇÃO
                                </option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 10px;" id="div-assunto-campo">
                        <div class="green-border-focus">
                            <label for='assunto-campo' class='form-label'>Assunto:</label>
                            <input type='text' class='form-control' id='assunto-campo' aria-describedby='assuntoHelp' placeholder='Assunto da mensagem.' MAXLENGTH = '200'>
                        </div>
                    </div>
                    <div style="margin-top: 15px;" id="div-mensagem-campo">
                        <div class="green-border-focus">
                            <label for='mensagem-campo' class='form-label'>Mensagem:</label>
                            <textarea class='form-control' aria-label='With textarea' id='mensagem-campo' rows='10' placeholder='Mensagem a ser enviada.' MAXLENGTH = '700'></textarea>
                        </div>
                    </div>
                    <div style="margin-top: 15px;">
                        <div class="">
                            <input type="checkbox" name="mostraCampoFormularioMensagem" id="mostraCampoFormularioMensagem" />
                            <label for="mostraCampoFormularioMensagem"><span></span>Anexar link para formulário?</label>
                        </div>
                    </div>
                    <div style="margin-top: 5px;" id="div-formulario-campo">
                        <div class="row" style="margin-top: 5px;" id="div-formulario-campo">
                            <div class="col-lg-12 green-border-focus">
                                <label for='formulario-campo' class='form-label'>Link para o formulário:</label>
                                <input type='text' class='form-control' id='formulario-campo' aria-describedby='assuntoHelp' placeholder='Cole aqui o link para o formulário pego no Google Forms.' MAXLENGTH = '200'>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 15px;">
                        <div class="">
                            <button id="adicionar-anexo" type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-anexo">Adicionar Anexo</button>
                        </div>
                    </div>
                    <div style="margin-top: 5px;" id="div-anexo-campo">
                        <div class="row" style="margin-top: 5px;">
                            <div class="col-lg-12 green-border-focus">
                                <label for='anexo-campo' class='form-label'>Anexo:</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn grey-color" data-dismiss="modal">Fechar</button>
                <button type="button" id="bt-envia-mensagem" class="btn primary-color" data-dismiss="modal" onclick="enviarMensagens()">Enviar</button>
            </div>
        </div>
    </div>
</div>
<!-- fim do modal mensagem -->
