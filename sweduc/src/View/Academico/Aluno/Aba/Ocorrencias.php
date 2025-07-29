<div role="tabpanel" class="tab-pane" id="tab_ocorrencias">
    <?php
    $query1alocor = "SELECT *, DATE_FORMAT(datahora,'%H:%i') AS 'horaoco' FROM alunos_ocorrencias WHERE id='$ocorrenciaId'";
    $result1alocor = mysql_query($query1alocor);
    $row1alocor = mysql_fetch_array($result1alocor, MYSQL_ASSOC);
    ?>
    <form id="ocorrencia">
    <div class="row">
        <div class="col-lg-5">
            <label for="idocorrencia">Ocorrência</label>
            <input type="hidden" id="idalunoocorrencia" value="<?=$ocorrenciaId?>">
            <select id="idocorrencia" name="idocorrencia"  class="form-element" required>
                <?php
                $query1 = "SELECT * FROM ocorrencias ORDER BY ocorrencia ASC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    ?>
                <option
                    value="<?=$row1['id']?>"
                    <?=$this->selected($row1alocor && $row1alocor['idocorrencia'] == $row1['id'])?>
                >
                    <?=$row1['ocorrencia']?>
                </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-lg-3">
            <label for="iddepartamento">Departamento</label>
            <select id="iddepartamento" name="iddepartamento"  class="form-element">
                <option value="0"> - </option>
                <?php
                $query1 = "SELECT * FROM departamentos ORDER BY departamento ASC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    ?>
                    <option
                        value="<?=$row1['id']?>"
                        <?=$this->selected($row1alocor && $row1alocor['iddepartamento'] == $row1['id']) ?>
                    >
                        <?=$row1['departamento']?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-lg-4">
            <label for="iddisciplina">Disciplina</label>
            <select id="iddisciplina" name="iddisciplina"  class="form-element">
                <option value="0"> - </option>
                <?php
                $query1 = "SELECT * FROM disciplinas ORDER BY disciplina ASC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    ?>
                <option
                    value="<?=$row1['id']?>"
                    <?=$this->selected($row1alocor && $row1alocor['iddisciplina'] == $row1['id'])?>
                >
                    <?=$row1['disciplina']?>
                </option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="col-lg-4">
            <label for="idprofessor">Professor</label>
            <select id="idprofessor" name="idprofessor" class="form-element">
                <option value="0"> - </option>
                <?php
                $query1 = "SELECT funcionarios.id, pessoas.nome FROM funcionarios, pessoas WHERE funcionarios.idunidade=$idfuncionariounidade AND funcionarios.idpessoa=pessoas.id AND professor=1 ORDER BY nome ASC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    ?>
                <option
                    value="<?=$row1['id']?>"
                    <?=$this->selected($row1alocor && $row1alocor['idfuncionario'] == $row1['id'])?>
                >
                    <?=$row1['nome']?>
                </option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="col-lg-12">
            <label for="assuntoocorrencia">
                Assunto
            </label>

            <textarea
                id="assuntoocorrencia"
                class="form-element"
                rows="5"
            ><?=str_replace('<br />', "\n", $row1alocor ? $row1alocor['assunto'] : '')?></textarea>
        </div>
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="col-lg-2">
            <label for="dataocorrencia">Data</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                <input type="date" id="dataocorrencia" aria-describedby="dataocorrencia" class="form-element dataHoraBorder" value="<?=date('Y-m-d', strtotime($row1alocor['datahora'] ?? 'now'))?>" />
            </div>
        </div>
        <div class="col-lg-2 clockpicker" data-autoclose="true">
            <label for="horaocorrencia">Hora</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="far fa-clock"></i></span>
                <input type="text" id="horaocorrencia" data-mask="99:99" aria-describedby="horaocorrencia" class="form-element dataHoraBorder" value="<?php echo $row1alocor && $row1alocor['horaoco'] ? $row1alocor['horaoco'] : date('H:i') ?>" />
            </div>
        </div>
        <div class="col-lg-8">
            <label style="display: block;">&nbsp;</label>
            <?php if ($usuario->autorizado('academico-alunos-editar')) { ?>
                <button id="addocorrencia" class="btn green-color" type="submit"><?=!empty($ocorrenciaId) ? 'Atualizar' : 'Salvar'?></button>
                <button id="limparocorrencia" class="btn danger-color" type="button" onclick="limpaFormOcorrencia()">Limpar</button>
            <?php } ?>
        </div>
    </div>
    </form>
    <div class="hr-line-dashed"></div>
    <h3 class="section-forms">Ocorrências do aluno</h3>
    <div class="row" style="margin-top: 10px;">
        <div class="col-lg-12">
            <table class="new-table table-striped" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="table-header-repeat line-left-2"><b>Data/Hora</b></th>
                        <th class="table-header-repeat line-left-2"><b>Departamento</b></th>
                        <th class="table-header-repeat line-left-2"><b>Disciplina</b></th>
                        <th class="table-header-repeat line-left-2"><b>Professor</b></th>
                        <th class="table-header-repeat line-left-2"><b>Ocorrência</b></th>
                        <th class="table-header-repeat line-left-2"><b>Assunto</b></th>
                        <th class="table-header-repeat line-left-2" style="width: 10%;"><b>Opções</b></th>
                    </tr>
                </thead>
                <tbody id="table-ocorrencias"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Carrega lista de ocorrências
exibeOcorrencias()

function editarOcorrencias(idaluno, nummatricula, idpessoa, idunidade, turmamatricula, idalunos_ocorrencias) {
    const matriculaId = $("#idmatricula").val();

    sweduc.carregarUrl(
        'alunos_cadastra.php' +
        '?matriculaId=' + matriculaId +
        '&ocorrenciaId=' + idalunos_ocorrencias +
        '&aba=but_ocorrencias'
    ).then(function () {
        document.getElementById('idocorrencia').focus();
    });
}

$("#ocorrencia").submit(function (e) {
    e.preventDefault();
    $.ajax({
        url: "dao/alunos.php",
        type: "POST",
        data: {action: "addocorrencia", idalunoocorrencia: $("#idalunoocorrencia").val(), nummatricula: $("#nummatricula").val(), idaluno: $("#idaluno").val(), idpermissoes: $("#idpermissoes").val(), iddepartamento: $("#iddepartamento :selected").val(), idpessoalogin: $("#idpessoalogin").val(), idocorrencia: $("#idocorrencia :selected").val(), data: $("#dataocorrencia").val(), hora: $("#horaocorrencia").val(), idunidade: $("#idunidadecadastraFEITA").val(), idprofessor: $("#idprofessor :selected").val(), iddisciplina: $("#iddisciplina :selected").val(), assunto: $("#assuntoocorrencia").val()},
        context: jQuery('#table-ocorrencias'),
        beforeSend: bloqueiaUI,
        complete: $.unblockUI,
        success: function () {
            criaAlerta('success', 'Ocorrência adicionada com sucesso');
            limpaFormOcorrencia();
            exibeOcorrencias()
        },
        error: function() {
            criaAlerta('error', 'Ocorreu um erro ao tentar cadastrar a ocorrência. Tente novamente');
        }
    });
});

function exibeOcorrencias() {
    $('#table-ocorrencias').children().remove()
    $.getJSON("dao/ocorrencias.php", {
        action: "ocorrenciasJson",
        idaluno: $("#idaluno").val()
    }, function(data) {
        var items = [];
        $.each(data, function(key, ocorrencia) {
            items.push("<tr id='row_"+ocorrencia.id+"'>")
            items.push("<td>" + ocorrencia.dthora + "</td>")
            items.push("<td>" + ocorrencia.departamento + "</td>")
            items.push("<td>" + ocorrencia.disciplina + "</td>")
            items.push("<td>" + ocorrencia.professor + "</td>")
            items.push("<td>" + ocorrencia.ocorrencia + "</td>")
            items.push("<td>" + ocorrencia.assunto.replace(/\n/g, "<br>") + "</td>")
            items.push('<td>')
            items.push('<button type="button" class="btn primary-color" onclick="editarOcorrencias(' + $("#idaluno").val() + "," + $("#nummatricula").val() + "," + $("#idpessoa").val() + "," + $("#idunidade").val() + "," + $("#turmamatriculadoantiga").val() + ", " + ocorrencia.id + ');"><i class="fa fa-edit"></i></button>')
            items.push('<button type="button" class="btn danger-color" onclick="removeLigacao(\'alunos_ocorrencias\',' + ocorrencia.id + ')"><i class="fa fa-trash-alt"></i></button>')
            items.push('</td></tr>')
        });

        $('#table-ocorrencias').append(items.join(""))
    });
}

function limpaFormOcorrencia() {
    $("#idalunoocorrencia").val(0);
    $("#idocorrencia").val(0);
    $("#idprofessor").val(0);
    $("#iddisciplina").val(0);
    $("#iddepartamento").val(0);
    $("#dataocorrencia").val("");
    $("#horaocorrencia").val("");
    $("#assuntoocorrencia").val("");
    $("#dataocorrencia").val("<?=date('d/m/Y')?>");
    $("#horaocorrencia").val("<?=date('H:i')?>");
    $("#addocorrencia").text("Salvar");
}
</script>
