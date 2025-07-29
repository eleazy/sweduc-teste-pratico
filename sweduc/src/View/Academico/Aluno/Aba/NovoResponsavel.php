<?php $this->insert('Academico/Aluno/Aba/ModalSelectResponsavel') ?>

<div role="tabpanel" class="tab-pane" id="tab_cadastro_responsavel">
    <div class="mb-5 container-fluid">
        <h2 class="section-forms" style="margin-top: 0;">
            Associar responsável ao aluno
        </h2>
        <div class="flex gap-8">
            <div id="abreModalResponsavel" class="btn btn-primary">
                <i class="fa fa-plus"></i> Associar responsável
            </div>
            <p id="selected-responsavel" class="mt-2" style="font-weight: semi-bold; font-size: large;"></p>
        </div>
        <input type="hidden" id="resp_pessoa_id" name="resp_pessoa_id" value="-1" />

        <div class="form-group assoc hidden" style="margin-top: 20px;">
            <label for="assoc_idparentesco" class="col-sm-2 control-label">Parentesco</label>
            <div class="col-sm-3">
                <select id="assoc_idparentesco" name="assoc_idparentesco" class="form-control">
                    <option value="-1" disabled selected>Escolha o parentesco...</option>
                    <?php
                    $query = "SELECT * FROM parentescos ORDER BY id ASC";
                    $result = mysql_query($query);
                    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        ?>
                        <option value="<?= $row['id'] ?>">
                            <?= $row['parentesco'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <section class="assoc hidden">
            <h2 class="section-forms" style="margin-top: 0;">
                Definições do perfil
            </h2>

            <div class="form-group">
                <label for="assoc_respfinanceiro" class="col-sm-2 control-label">Responsável financeiro?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="assoc_respfinanceiro" id="assoc_respfsim" value="1" />
                    <label for="assoc_respfsim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="assoc_respfinanceiro" id="assoc_respfnao" value="0" />
                    <label for="assoc_respfnao"><span></span>Não</label>
                </div>
            </div>

            <div class="form-group">
                <label for="assoc_respfinanceiro2" class="col-sm-2 control-label">Segundo responsável financeiro?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="assoc_respfinanceiro2" id="assoc_respf2sim" value="1" />
                    <label for="assoc_respf2sim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="assoc_respfinanceiro2" id="assoc_respf2nao" value="0" />
                    <label for="assoc_respf2nao"><span></span>Não</label>
                </div>
            </div>

            <div class="form-group">
                <label for="assoc_resppedagogico" class="col-sm-2 control-label">Responsável pedagógico?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="assoc_resppedagogico" id="assoc_resppsim" value="1" />
                    <label for="assoc_resppsim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="assoc_resppedagogico" id="assoc_resppnao" value="0" />
                    <label for="assoc_resppnao"><span></span>Não</label>
                </div>
            </div>

            <div class="form-group">
                <label for="assoc_autorizado" class="col-sm-2 control-label">Autorizado a retirar o aluno da escola?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="assoc_autorizado" id="assoc_autorizadosim" value="1" />
                    <label for="assoc_autorizadosim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="assoc_autorizado" id="assoc_autorizadonao" value="0" />
                    <label for="assoc_autorizadonao"><span></span>Não</label>
                </div>
            </div>

            <div class="form-group">
                <label for="assoc_visualiza_financeiro" class="col-sm-2 control-label">Acesso ao financeiro?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="assoc_visualiza_financeiro" id="assoc_visualiza_financeiro_sim" value="1" />
                    <label for="assoc_visualiza_financeiro_sim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="assoc_visualiza_financeiro" id="assoc_visualiza_financeiro_nao" value="0" />
                    <label for="assoc_visualiza_financeiro_nao"><span></span>Não</label>
                </div>
            </div>

            <div class="form-group">
                <label for="assoc_visualiza_pedagogico" class="col-sm-2 control-label">Acesso ao pedagógico?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="assoc_visualiza_pedagogico" id="assoc_visualiza_pedagogico_sim" value="1" />
                    <label for="assoc_visualiza_pedagogico_sim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="assoc_visualiza_pedagogico" id="assoc_visualiza_pedagogico_nao" value="0" />
                    <label for="assoc_visualiza_pedagogico_nao"><span></span>Não</label>
                </div>
            </div>

        </section>

        <div class="assoc hidden">
            <button id="criarNovo" class="btn btn-primary" style="margin-top: 20px;">
                <i class="fa fa-plus"></i> Criar novo responsável
            </button>
        </div>
    </div>

    <div class="mb-5 container-fluid novoresp">
        <h2 class="section-forms" style="margin-top: 20px;">
            Dados para o cadastro do responsável
        </h2>

        <div class="form-group">
            <label for="idparentesco" class="col-sm-2 control-label">Parentesco</label>
            <div class="col-sm-3">
                <select id="idparentesco" name="idparentesco" class="form-control">
                    <option value="-1" disabled selected>Escolha o parentesco...</option>
                    <?php
                    $query = "SELECT * FROM parentescos ORDER BY id ASC";
                    $result = mysql_query($query);
                    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        ?>
                        <option value="<?= $row['id'] ?>">
                            <?= $row['parentesco'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="resp_nome" class="col-sm-2 control-label">Nome</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="resp_nome" id="resp_nome" value="" />
            </div>
        </div>

        <div class="form-group">
            <label for="resp_idsexo" class="col-sm-2 control-label">Sexo</label>
            <div class="col-sm-2">
                <select class="form-control" name="resp_idsexo" id="resp_idsexo">
                    <?php
                    $query1 = "SELECT id, sexo FROM sexo ORDER BY id ASC";
                    $result1 = mysql_query($query1);
                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                        ?>
                        <option value="<?= $row1['id'] ?>">
                            <?= $row1['sexo'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="resp_idestadocivil" class="col-sm-2 control-label">Estado Civil</label>
            <div class="col-sm-2">
                <select class="form-control" name="resp_idestadocivil" id="resp_idestadocivil">
                    <?php
                    $query1 = "SELECT id, estadocivil FROM estadocivil ORDER BY id ASC";
                    $result1 = mysql_query($query1);
                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                        ?>
                        <option value="<?= $row1['id'] ?>">
                            <?= $row1['estadocivil'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="resp_cpf" class="col-sm-2 control-label">CPF</label>
            <div class="col-sm-2">
                <input type="text" class="form-control cpf" name="resp_cpf" id="resp_cpf" value="" />
            </div>
        </div>

        <div class="form-group">
            <label for="resp_rg" class="col-sm-2 control-label">RG</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" name="resp_rg" id="resp_rg" value="" />
            </div>
        </div>

        <div class="form-group">
            <label for="resp_orgaoexp" class="col-sm-2 control-label">Órgão Expedidor</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" name="resp_orgaoexp" id="resp_orgaoexp" value="" />
            </div>
        </div>

        <div class="form-group">
            <label for="resp_rg_data_expedicao" class="col-sm-2 control-label">Data de expedição</label>
            <div class="col-sm-2">
                <input type="text" class="form-control date" name="resp_rg_data_expedicao" id="resp_rg_data_expedicao" value="" />
            </div>
        </div>

        <div class="form-group">
            <label for="resp_idpaisnascimento" class="col-sm-2 control-label">Nacionalidade</label>
            <div class="col-sm-3">
                <select name="resp_idpaisnascimento" id="resp_idpaisnascimento" class="form-control">
                    <option value="0"> - SELECIONE - </option>
                    <?php
                    $query1 = "SELECT * FROM paises ORDER BY nom_pais ASC";
                    $result1 = mysql_query($query1);
                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                        ?>
                        <option value="<?= $row1['cod_pais'] ?>"
                            <?php
                            if (($row1['nom_pais'] == 'BRASIL')) {
                                echo 'selected';
                            }
                            ?>>
                            <?= $row1['nom_pais'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="resp_datanascimento" class="col-sm-2 control-label">Data de Nascimento</label>
            <div class="col-sm-2">
                <input type="date" class="form-control" name="resp_datanascimento" id="resp_datanascimento" value="" />
            </div>
        </div>

        <div class="form-group">
            <label for="resp_idestadonascimento" class="col-sm-2 control-label">UF(Naturalidade)</label>
            <div class="col-sm-2">
                <select class="form-control" name="resp_idestadonascimento" id="resp_idestadonascimento">
                    <option value="0"> - </option>
                    <?php
                    $query1 = "SELECT id, sgl_estado FROM estados JOIN paises ON paises.cod_pais = estados.cod_pais WHERE nom_pais = 'BRASIL' ORDER BY sgl_estado ASC";
                    $result1 = mysql_query($query1);
                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                        ?>
                        <option value="<?= $row1['id'] ?>">
                            <?= $row1['sgl_estado'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <a name="#femails"></a>
            <label for="registromec" class="col-sm-2 control-label">
                E-mails:
                <a href="#femails" onclick="$('#formemails').append('<input type=\'text\'  placeholder=\'Digite o email do contato\' style=\'margin-top: 10px; margin-bottom: 10px; \' name=\'resp_emails[]\' class=\'form-control\' ></a>');"> [ + ]</a>
            </label>

            <div class="col-sm-4" id="formemails">
                <input type="text" name="resp_emails[]" placeholder="Digite o email do contato" class="form-control" />
            </div>
        </div>

        <div class="form-group">
            <a name="#ftels"></a>
            <label for="registromec" class="col-sm-2 control-label">
                Telefones <a href="#ftels" onClick="$('#formtels').append($('#form__telefone').html());"> [ + ]</a>
            </label>

            <div class="col-sm-6" id="formtels">
                <div class="tel-form">
                    <div class="col-sm-6" style="padding-left: 0;">
                        <input type="text" name="resp_telefone[]" class="form-control" />
                    </div>
                    <div class="col-sm-6">
                        <select name="resp_tipotelefone[]" class="form-control">
                            <option value="-1">Não informado</option>
                            <?php
                            $query1 = "SELECT * FROM tipotel ORDER BY id ASC";
                            $result1 = mysql_query($query1);
                            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                                ?>
                                <option value="<?php echo  $row1['id'] ?>"><?php echo  $row1['tipotel'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <section>
            <h2 class="section-forms" style="margin-top: 0;">
                Dados de profissão
            </h2>

            <div class="form-group">
                <label for="resp_profissao" class="col-sm-2 control-label">Profissão</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" name="resp_profissao" id="resp_profissao" size="60" value="" />
                </div>
            </div>

            <div class="form-group">
                <label for="resp_empresa" class="col-sm-2 control-label">Empresa</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" name="resp_empresa" id="resp_empresa" size="60" value="" />
                </div>
            </div>

            <div class="form-group">
                <label for="resp_trabalho_cep" class="col-sm-2 control-label">CEP</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control cep" name="resp_trabalho_cep" id="resp_trabalho_cep" value="" />
                </div>
            </div>

            <div class="form-group">
                <label for="resp_trabalho_bairro" class="col-sm-2 control-label">Bairro</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" name="resp_trabalho_bairro" id="resp_trabalho_bairro" value="" />
                    <input type="hidden" value="1" name="idpais" />
                </div>
            </div>

            <div class="form-group">
                <label for="resp_trabalho_logradouro" class="col-sm-2 control-label">Endereço</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="resp_trabalho_logradouro" id="resp_trabalho_logradouro" value="" />
                </div>
            </div>

            <div class="form-group">
                <label for="resp_trabalho_numero" class="col-sm-2 control-label">Número</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" name="resp_trabalho_numero" id="resp_trabalho_numero" value="" />
                </div>
            </div>

            <div class="form-group">
                <label for="resp_trabalho_complemento" class="col-sm-2 control-label">Complemento</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" name="resp_trabalho_complemento" id="resp_trabalho_complemento" value="" />
                </div>
            </div>

            <div class="form-group">
                <label for="resp_trabalho_tel" class="col-sm-2 control-label">Tel.</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" name="resp_trabalho_tel" id="resp_trabalho_tel" value="" />
                </div>
            </div>
        </section>

        <section>
            <h2 class="section-forms" style="margin-top: 0;">
                Dados residenciais
            </h2>

            <div class="form-group">
                <div class="form-group">
                    <label for="resp_pais" class="col-sm-2 control-label">País</label>
                    <div class="col-sm-3">
                        <select name="resp_pais" id="resp_pais" class="form-control">
                            <option value="0"> - SELECIONE - </option>
                            <?php
                            $query1 = "SELECT * FROM paises ORDER BY nom_pais ASC";
                            $result1 = mysql_query($query1);
                            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) : ?>
                                <option value="<?= $row1['cod_pais'] ?>"
                                    <?php
                                    if (($row1['nom_pais'] == 'BRASIL')) {
                                        echo 'selected';
                                    }
                                    ?>>
                                    <?= $row1['nom_pais'] ?>
                                </option>
                            <?php endwhile ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="resp_cep" class="col-sm-2 control-label">CEP</label>
                    <div class="col-sm-2">
                        <input type="text" class="form-control cep" name="resp_cep" id="resp_cep" value="" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="resp_uf" class="col-sm-2 control-label">UF</label>
                    <div class="col-sm-2">
                        <select class="form-control" name="resp_uf" id="resp_uf">
                            <option value="0"></option>
                            <?php
                            $query1 = "SELECT id, sgl_estado FROM estados JOIN paises ON paises.cod_pais = estados.cod_pais WHERE nom_pais = 'BRASIL' ORDER BY sgl_estado ASC";
                            $result1 = mysql_query($query1);
                            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) : ?>
                                <option value="<?= $row1['id'] ?>">
                                    <?= $row1['sgl_estado'] ?>
                                </option>
                            <?php endwhile ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="resp_cidade" class="col-sm-2 control-label">Município</label>
                    <div class="col-sm-2">
                        <select class="form-control" name="resp_cidade" id="resp_cidade">
                            <option value="0"></option>
                            <?php
                            if (!empty($rowAA['idcidade'])) {
                                $query1 = "SELECT * FROM cidades WHERE cod_estado = '{$rowAA['idestado']}' ORDER BY nom_cidade ASC";
                                $result1 = mysql_query($query1);
                                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) : ?>
                                    <option value="<?= $row1['id'] ?>">
                                        <?= $row1['nom_cidade'] ?>
                                    </option>
                                <?php endwhile;
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="resp_bairro" class="col-sm-2 control-label">Bairro</label>
                    <div class="col-sm-2">
                        <input type="text" class="form-control" name="resp_bairro" id="resp_bairro" value="" />
                        <input type="hidden" value="1" name="idpais" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="resp_logradouro" class="col-sm-2 control-label">Endereço</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="resp_logradouro" id="resp_logradouro" value="" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="resp_numero" class="col-sm-2 control-label">Número</label>
                    <div class="col-sm-2">
                        <input type="text" class="form-control" name="resp_numero" id="resp_numero" value="" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="resp_complemento" class="col-sm-2 control-label">Complemento</label>
                    <div class="col-sm-2">
                        <input type="text" class="form-control" name="resp_complemento" id="resp_complemento" value="" />
                    </div>
                </div>
        </section>

        <section>
            <h2 class="section-forms" style="margin-top: 0;">
                Definições do perfil
            </h2>

            <div class="form-group">
                <label for="respfinanceiro" class="col-sm-2 control-label">Responsável financeiro?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="respfinanceiro" id="respfsim" value="1" />
                    <label for="respfsim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="respfinanceiro" id="respfnao" value="0" />
                    <label for="respfnao"><span></span>Não</label>
                </div>
            </div>

            <div class="form-group">
                <label for="respfinanceiro2" class="col-sm-2 control-label">Segundo responsável financeiro?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="respfinanceiro2" id="respf2sim" value="1" />
                    <label for="respf2sim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="respfinanceiro2" id="respf2nao" value="0" />
                    <label for="respf2nao"><span></span>Não</label>
                </div>
            </div>

            <div class="form-group">
                <label for="resppedagogico" class="col-sm-2 control-label">Responsável pedagógico?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="resppedagogico" id="resppsim" value="1" />
                    <label for="resppsim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="resppedagogico" id="resppnao" value="0" />
                    <label for="resppnao"><span></span>Não</label>
                </div>
            </div>

            <div class="form-group">
                <label for="autorizado" class="col-sm-2 control-label">Autorizado a retirar o aluno da escola?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="autorizado" id="autorizadosim" value="1" />
                    <label for="autorizadosim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="autorizado" id="autorizadonao" value="0" />
                    <label for="autorizadonao"><span></span>Não</label>
                </div>
            </div>

            <div class="form-group">
                <label for="visualiza_financeiro" class="col-sm-2 control-label">Acesso ao financeiro?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="visualiza_financeiro" id="visualiza_financeiro_sim" value="1" />
                    <label for="visualiza_financeiro_sim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="visualiza_financeiro" id="visualiza_financeiro_nao" value="0" />
                    <label for="visualiza_financeiro_nao"><span></span>Não</label>
                </div>
            </div>

            <div class="form-group">
                <label for="visualiza_pedagogico" class="col-sm-2 control-label">Acesso ao pedagógico?</label>
                <div class="col-sm-2" style="padding-top: 6px;">
                    <input type="radio" class="hidden" name="visualiza_pedagogico" id="visualiza_pedagogico_sim" value="1" />
                    <label for="visualiza_pedagogico_sim"><span></span>Sim</label>
                    <input type="radio" class="hidden" name="visualiza_pedagogico" id="visualiza_pedagogico_nao" value="0" />
                    <label for="visualiza_pedagogico_nao"><span></span>Não</label>
                </div>
            </div>

        </section>
    </div>
</div>

<script type="text/javascript">
    $('.date').mask("#9/@9/&999", {
        placeholder: 'dd/mm/yyyy'
    }).datepicker({
        changeMonth: true,
        changeYear: true,
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        dateFormat: "dd/mm/yy"
    });

    $("#resp_cpf").blur(function() {
        if (!(checaCPF($("#resp_cpf").val()))) {
            criaAlerta("error", "CPF inválido. Redigite!");
            $("#resp_cpf").val("");
        } else {
            $.ajax({
                url: "dao/alunos.php",
                type: 'POST',
                context: $('#resp_cpf'),
                data: {
                    action: "buscaCPFResp",
                    cpf: $(this).val()
                },
                beforeSend: bloqueiaUI,
                complete: $.unblockUI,
                success: function(data) {
                    if (data > 0) {
                        criaAlerta('error', 'Já existe um cadastro com esse CPF');
                    }
                }
            });
        }
    });

    var cidade = "";

    $("#idparentesco").change(function() {
        if ($("#idparentesco :selected").val() == 0) {
            //dados
            const nomeAluno = document.getElementById('nome').value;
            $('#resp_nome').val(nomeAluno);

            const alunoSexo = document.querySelector('[name="idsexo"]').value;
            $('#resp_idsexo').val(alunoSexo);

            const alunoEstadoCivil = document.querySelector('#idestadocivil').value;
            $('#resp_idestadocivil').val(alunoEstadoCivil);

            const alunoCpf = document.querySelector('#cpf').value;
            $('#resp_cpf').val(alunoCpf);

            const alunoRg = document.querySelector('#rg').value;
            $('#resp_rg').val(alunoRg);

            const alunoOrgaoExpedidor = document.querySelector('#orgaoexp').value;
            $('#resp_orgaoexp').val(alunoOrgaoExpedidor);

            const alunoDataExpedicao = document.querySelector('#rg_data_expedicao').value;
            const maskedDataExpedicao = alunoDataExpedicao.split('-').reverse().join('/');
            $('#resp_rg_data_expedicao').val(maskedDataExpedicao);

            const alunoPaisNasc = document.querySelector('#idpaisnascimento').value;
            $('#resp_idpaisnascimento').val(alunoPaisNasc);

            const alunoDataNasc = document.querySelector('#datanascimento').value;
            $('#resp_datanascimento').val(alunoDataNasc);

            const alunoEstadoNasc = document.querySelector('#idestadonascimento').value;
            $('#resp_idestadonascimento').val(alunoEstadoNasc);

            const alunoEmails = document.querySelectorAll('[name="emails[]"]');
            alunoEmails.forEach(email => {
                if (email.value.trim() !== "") {
                    adicionaEmail(email.value);
                }
            });

            // const alunoTelefones = document.querySelectorAll('[name="telefone[]"]');
            // alunoTelefones.forEach(tel => {
            //     if (tel.value.trim() !== "") {
            //         let tipo = tel.closest('tr').querySelector('select[name="tipotelefone[]"]')
            //         if (tipo == null) {
            //             tipo = 0;
            //         } else {
            //             tipo = tipo.value;
            //         }
            //         adicionaTelefone(tel.value, tipo);
            //     }
            // });

            // profissao
            const alunoProfissao = document.querySelector('#profissao-aluno').value;
            $('#resp_profissao').val(alunoProfissao);

            // emdereço
            const alunoCep = document.querySelector('#cep').value;
            $('#resp_cep').val(alunoCep);

            const alunoUf = document.querySelector('#idestado').value;
            $('#resp_uf').val(alunoUf).change();

            const alunoCidade = document.querySelector('#idcidade').value;
            cidade = alunoCidade;
            $('#resp_cidade').val(alunoCidade).change();

            const alunoBairro = document.querySelector('#bairro').value;
            $('#resp_bairro').val(alunoBairro);

            const alunoLogradouro = document.querySelector('#logradouro').value;
            $('#resp_logradouro').val(alunoLogradouro);

            const alunoNumero = document.querySelector('#numero').value;
            $('#resp_numero').val(alunoNumero);

            const alunoComplemento = document.querySelector('#complemento').value;
            $('#resp_complemento').val(alunoComplemento);
        }
    });

    function adicionaEmail(email) {
        let emailInput = $('#formemails input').last()
        emailInput.val(email)
        emailInput.after('<input type=\'text\'  placeholder=\'Digite o email do contato\' style=\'margin-top: 10px; margin-bottom: 10px; \' name=\'resp_emails[]\' class=\'form-control\'>');
    }

    function adicionaTelefone(tel, tipo) {
        let telForm = $('#formtels .tel-form').last()
        telForm.find('input').val(tel)
        telForm.find('select').val(tipo)
        telForm.after($('#form__telefone').html())
    }

    function validaFormulario() {
        if ($("#idparentesco :selected").val() == -1) {
            exibirErroDeValidacao($("#idparentesco"), "Escolha o parentesco!");
            return false;
        }

        if ($.trim($("#resp_nome").val()) == "") {
            exibirErroDeValidacao($("#resp_nome"), "Insira o nome do responsável!");
            return false;
        }

        if (!(checaCPF($("#resp_cpf").val()))) {
            exibirErroDeValidacao($("#resp_cpf"), "CPF inválido");
            return false;
        }

        return true;
    }

    function atualizaListaCidades() {
        return $.ajax({
            url: "dao/estados.php",
            type: "POST",
            data: {
                action: "recebeCidades",
                idestado: $('#resp_uf').val()
            },
            context: jQuery('#resp_cidade'),
            success: function(data) {
                $('#resp_cidade').html(data);
                if (cidade != "")
                    $("select#resp_cidade option").each(function() {
                        this.selected = ($.trim(this.text) == cidade);
                    });
                cidade = "";
            }
        });
    }

    $("#resp_uf").change(atualizaListaCidades);

    $("#resp_cep").change(function() {
        $.ajax({
            url: 'https://viacep.com.br/ws/' + $('#resp_cep').val() + '/json/',
            type: 'GET',
            success: function(data) {
                if (data.hasOwnProperty('erro')) {
                    return;
                }

                $('#resp_uf option:contains(' + data.uf + ')').attr('selected', 'selected')
                $.when(atualizaListaCidades()).done(function() {
                    $('#resp_cidade option:contains(' + data.localidade + ')').attr('selected', 'selected').change()
                })
                $('#resp_bairro').val(data.bairro)
                $('#resp_logradouro').val(data.logradouro)
            }
        })
    });

    $("#resp_trabalho_cep").change(function() {
        $.ajax({
            url: 'https://viacep.com.br/ws/' + $('#resp_trabalho_cep').val() + '/json/',
            type: 'GET',
            success: function(data) {
                if (data.hasOwnProperty('erro')) {
                    return;
                }

                $('#resp_trabalho_uf option:contains(' + data.uf + ')').attr('selected', 'selected')
                $.when(atualizaListaCidades()).done(function() {
                    $('#resp_trabalho_cidade option:contains(' + data.localidade + ')').attr('selected', 'selected').change()
                })
                $('#resp_trabalho_bairro').val(data.bairro)
                $('#resp_trabalho_logradouro').val(data.logradouro)
            }
        })
    });

    $('#abreModalResponsavel').on('click', function() {
        const modal = document.getElementById('modal-selecionar-responsavel');
        modal.classList.toggle('hidden');
        //window.clickedResponsavel = this.closest('td').id.split('_')[1];
        d.querySelector('#searchRespInput').value = '';
        buscarResponsaveis(true);
        //d.querySelector('#searchRespInput').focus();
    });

    $('#criarNovo').on('click', function() {
        const input = d.querySelector('#resp_pessoa_id');
        input.value = "-1";
        const p = d.querySelector('#selected-responsavel');
        p.textContent = "";

        const assocs = d.querySelectorAll('.assoc');
        assocs.forEach(assoc => {
            assoc.classList.add('hidden');
        });

        const novoRespForm = d.querySelector('.novoresp');
        if (novoRespForm) {
            novoRespForm.classList.remove('hidden');
        }
    });
</script>
