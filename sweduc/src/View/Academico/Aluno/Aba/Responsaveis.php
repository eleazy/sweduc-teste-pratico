<div role="tabpanel" class="tab-pane" id="tab_responsaveis">
    <h3 class="section-forms">
        Responsáveis
    </h3>

    <div class="flex -m-2">
        <div class="ml-auto p-2">
            <button
                id="associaresponsavel"
                type="button"
                class="btn btn-block grey-color"
            >
                Associar responsável
            </button>
        </div>

        <div class="p-2">
            <button
                id="cadastranovoresponsavel"
                type="button"
                class="btn btn-block grey-color editaresponsavel"
            >
                Cadastrar responsável
            </button>
        </div>
    </div>

    <div class="associaResp" style="display: none;">
        <div class="row" style="margin-top: 20px;">
            <div class="form-group">
                <label for="responsavel" class="col-sm-2 control-label">Responsáveis Cadastrados</label>
                <div class="col-sm-4">
                    <input type="text" id="responsavel" class="form-element">
                    <input type="hidden" id="resp" name="resp">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label for="paren" class="col-sm-2 control-label">
                    Parentesco
                </label>
                <div class="col-sm-3">
                    <select id="paren" name="paren"  class="form-element">
                        <option value="-1"> - </option>
                        <?php
                        $query1 = "SELECT * FROM parentescos ORDER BY id ASC";
                        $result1 = mysql_query($query1);
                        while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                            ?>
                            <option value="<?php echo  $row1['id'] ?>"><?php echo  $row1['parentesco'] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label class="col-sm-2 control-label">Autoriza retirar o aluno da escola?</label>
                <div class="col-sm-3" style="padding-top: 5px;">
                    <input type="radio" class="hidden" name="autorizado" id="autorizadosim" value="1" />
                    <label for="autorizadosim"><span></span>SIM</label>&nbsp;
                    <input type="radio" class="hidden" name="autorizado" id="autorizadonao" value="0" checked="checked" />
                    <label for="autorizadonao"><span></span>NÃO</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group" disabled>
                <?php
                    $result = mysql_query("SELECT  1 FROM responsaveis, pessoas WHERE responsaveis.idpessoa = pessoas.id AND idaluno =$idaluno AND respfin=1");
                    $possui_responsavel_financeiro = !empty($idaluno) && mysql_num_rows($result);
                ?>
                <label class="col-sm-2 control-label">Responsável financeiro?</label>
                <div class="col-sm-3" style="padding-top: 5px;">
                    <input type="radio" class="hidden" name="respfinanceiro" id="respfsim" value="1" <?=$possui_responsavel_financeiro ? 'disabled' : '' ?>/>
                    <label for="respfsim"><span></span>SIM</label>&nbsp;

                    <input type="radio" class="hidden" name="respfinanceiro" id="respfnao" value="0" checked="checked" />
                    <label for="respfnao"><span></span>NÃO</label>
                    <?php if ($possui_responsavel_financeiro) { ?>
                        <br>
                        <small><i class="fas fa-exclamation mr-05-rem"></i>Não é possível selecionar mais de um responsável financeiro</small>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label class="col-sm-2 control-label">Segundo responsável financeiro?</label>
                <div class="col-sm-3" style="padding-top: 5px;">
                    <input type="radio" class="hidden" name="respfinanceiro2" id="respf2sim" value="1" />
                    <label for="respf2sim"><span></span>SIM</label>&nbsp;
                    <input type="radio" class="hidden" name="respfinanceiro2" id="respf2nao" value="0" checked="checked" />
                    <label for="respf2nao"><span></span>NÃO</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label class="col-sm-2 control-label">Responsável pedagógico?</label>
                <div class="col-sm-3" style="padding-top: 5px;">
                    <input type="radio" class="hidden" name="resppedagogico" id="resppsim" value="1" />
                    <label for="resppsim"><span></span>SIM</label>&nbsp;
                    <input type="radio" class="hidden" name="resppedagogico" id="resppnao" value="0" checked="checked" />
                    <label for="resppnao"><span></span>NÃO</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label class="col-sm-2 control-label">Acesso ao financeiro?</label>
                <div class="col-sm-3" style="padding-top: 5px;">
                    <input type="radio" class="hidden" name="visualiza_financeiro" id="visualiza_financeiro_sim" value="1" checked="checked" />
                    <label for="visualiza_financeiro_sim"><span></span>SIM</label>&nbsp;
                    <input type="radio" class="hidden" name="visualiza_financeiro" id="visualiza_financeiro_nao" value="0"/>
                    <label for="visualiza_financeiro_nao"><span></span>NÃO</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label class="col-sm-2 control-label">Acesso ao pedagógico?</label>
                <div class="col-sm-3" style="padding-top: 5px;">
                    <input type="radio" class="hidden" name="visualiza_pedagogico" id="visualiza_pedagogico_sim" value="1" checked="checked"/>
                    <label for="visualiza_pedagogico_sim"><span></span>SIM</label>&nbsp;
                    <input type="radio" class="hidden" name="visualiza_pedagogico" id="visualiza_pedagogico_nao" value="0"/>
                    <label for="visualiza_pedagogico_nao"><span></span>NÃO</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
            <?php if (in_array($alunos[0], $arraydo2)) { ?>
                <input type="button" name="addresp" id="addresp" class="btn green-color" value="Adicionar na lista" />
            <?php } ?>
            </div>
        </div>
    </div>

    <div class="my-2">
        <table class="new-table table-striped" style="width: 100%;">
            <thead>
                <tr>
                    <th class="table-header-repeat line-left-2"><b>Nome</b></th>
                    <th class="table-header-repeat line-left-2"><b>Parentesco</b></th>
                    <th class="table-header-repeat line-left-2"><b>Telefones</b></th>
                    <th class="table-header-repeat line-left-2"><b>Email</b></th>
                    <th class="table-header-repeat line-left-2"><b>Opções</b></th>
                </tr>
            </thead>

            <tbody>
                <?php
                $query1 = "SELECT
                        *,
                        responsaveis.id as rid,
                        pessoas.id as pid,
                        usuarios.id as usuario_id
                    FROM responsaveis
                    JOIN pessoas ON responsaveis.idpessoa=pessoas.id
                    JOIN parentescos ON responsaveis.idparentesco=parentescos.id
                    LEFT JOIN usuarios ON usuarios.idpessoa=pessoas.id
                    WHERE idaluno=$idaluno
                    ORDER BY responsaveis.id ASC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    ?>
                <tr id="responsaveis<?php echo $row1['rid'] ?>">
                    <td>
                        <?php echo $row1['nome'] ?>
                        <ul class="list-unstyled">
                            <li>
                                Autoriza Retirar aluno da escola? <b><?php echo $row1['autorizado'] == 1 ? 'Sim' : 'Não' ?></b>
                            </li>
                            <li>
                                Responsável financeiro? <b><?php echo $row1['respfin'] == 1 ? 'Sim' : 'Não' ?></b>
                            </li>
                            <li>
                                Segundo responsável financeiro? <b><?php echo $row1['respfin2'] == 1 ? 'Sim' : 'Não' ?></b>
                            </li>
                            <li>
                                Responsável pedagógico? <b><?php echo $row1['resppedag'] == 1 ? 'Sim' : 'Não' ?></b>
                            </li>
                            <li>
                                Acesso ao financeiro? <b><?php echo $row1['visualiza_financeiro'] == 1 ? 'Sim' : 'Não' ?></b>
                            </li>
                            <li>
                                Acesso ao pedagógico? <b><?php echo $row1['visualiza_pedagogico'] == 1 ? 'Sim' : 'Não' ?></b>
                            </li>
                        </ul>
                    </td>
                    <td><?php echo $row1['parentesco'] ?></td>
                    <td>
                        <?php
                        $query1t = "SELECT telefone FROM telefones WHERE idpessoa=" . $row1['pid'];
                        $result1t = mysql_query($query1t);
                        while ($rowt = mysql_fetch_array($result1t, MYSQL_ASSOC)) {
                            echo $rowt['telefone'] . "<br />";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $query1e = "SELECT email FROM emails WHERE idpessoa=" . $row1['pid'];
                        $result1e = mysql_query($query1e);
                        while ($rowe = mysql_fetch_array($result1e, MYSQL_ASSOC)) {
                            echo $rowe['email'] . "<br />";
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <div class="flex flex-wrap flex-col justify-center">
                            <?php if ($usuario->autorizado('academico-responsaveis-editar')) : ?>
                                <div class="p-1">
                                    <button
                                        type="button"
                                        data-toggle="tooltip"
                                        data-placement="bottom"
                                        title="Editar"
                                        class="btn primary-color editaresponsavel block w-full"
                                        data-responsavel-id="<?=$row1['rid']?>"
                                    >
                                        <i class="fa fa-edit"></i>
                                        <span class="hidden md:inline ml-1">Editar</span>
                                    </button>
                                </div>
                            <?php endif ?>

                            <?php if ($usuario->autorizado('academico-responsaveis-excluir')) : ?>
                                <div class="p-1">
                                    <button
                                        type="button"
                                        data-toggle="tooltip"
                                        data-placement="bottom"
                                        title="Excluir"
                                        id="X<?=$row1['rid']?>"
                                        class="btn danger-color block w-full"
                                        onclick="removeLigacao('responsaveis', <?=$row1['rid']?>);"
                                    >
                                        <i class="fa fa-trash-alt"></i>
                                        <span class="hidden md:inline ml-1">Desvíncular</span>
                                    </button>
                                </div>
                            <?php endif ?>

                            <?php if ($usuario->autorizado('sistema-autenticacao-personificar-alunos-responsaveis')) : ?>
                                <div class="p-1">
                                    <a
                                        href="/impersonate?usuarioId=<?=$row1['usuario_id']?>"
                                        data-toggle="tooltip"
                                        data-placement="bottom"
                                        title="Entrar na conta"
                                        class="btn green-color block w-full"
                                    >
                                        <i class="far fa-eye"></i>
                                        <span class="hidden md:inline ml-1">Entrar como</span>
                                    </a>
                                </div>
                            <?php endif ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(".editaresponsavel").click(function () {
    const matriculaId = document.getElementById('idmatricula').value;
    const responsavelId = this.dataset.responsavelId || null;

    sweduc.carregarUrl(
        'alunos_responsaveis.php'
        + '?responsavelId=' + responsavelId
        + '&matriculaId=' + matriculaId,
        document.getElementById('conteudo')
    );
});

$("#responsavel").autocomplete ({
    minLength: 3,
    source: '/academico/responsaveis-autocomplete',
    focus: function(event, ui) {
        $("#responsavel").val(ui.item.label);
        $("input[name=resp]").val(ui.item.value);
        return false;
    },
    select: function(event, ui) {
        $("#responsavel").val(ui.item.label);
        $("input[name=resp]").val(ui.item.value);
        return false;
    }
});
</script>
