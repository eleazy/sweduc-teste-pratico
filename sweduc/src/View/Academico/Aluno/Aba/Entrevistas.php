<div role="tabpanel" class="tab-pane" id="tab_entrevistas">
    <?php
    if ($idpermissoes > 0) {
        $queryEnt = "SELECT
                *,
                DATE(datahora) AS 'data',
                DATE(datahorarealizada) AS 'datarealizada',
                TIME(datahora) AS 'hora',
                TIME(datahorarealizada) AS 'horarealizada'
            FROM alunos_entrevistas
            WHERE id='$identrevista'";
        $resultEnt = mysql_query($queryEnt);
        $rowEnt = mysql_fetch_array($resultEnt, MYSQL_ASSOC);

        $datamarcada = $rowEnt['data'];
        $horamarcada = $rowEnt['hora'];
        $datarealizada = $rowEnt['datarealizada'];
        $horarealizada = $rowEnt['horarealizada'];

        $query = "SELECT pessoas.nome, funcionarios.id as fid
            FROM funcionarios, pessoas
            WHERE funcionarios.iddepartamento='{$rowEnt['iddepartamento']}'
            AND funcionarios.idpessoa=pessoas.id
            AND funcionarios.id='{$rowEnt['idfuncionario']}'
            GROUP BY pessoas.id";

        $result = mysql_query($query);
        $responsavelEscola = mysql_fetch_array($result, MYSQL_ASSOC);
        ?>
    <div class="row">
        <div class="col-lg-3">
            <label for="entrevistadepartamento">Departamento</label>
            <select id="entrevistadepartamento" name="entrevistadepartamento" class="form-element">
                <option>Selecione um departamento...</option>
                <?php
                $query1 = "SELECT * FROM departamentos ORDER BY departamento ASC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    ?>
                <option
                    value="<?=$row1['id']?>"
                    <?=$this->selected($rowEnt && $rowEnt['iddepartamento'] == $row1['id'])?>
                >
                    <?=$row1['departamento']?>
                </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-lg-3">
            <label for="entrevistaRespEscola">Responsável pela Escola</label>
            <select id="entrevistaRespEscola" name="entrevistaRespEscola"  class="form-element">
                <?php if ($responsavelEscola) : ?>
                    <option value="<?=$responsavelEscola['fid']?>">
                        <?=$responsavelEscola['nome']?>
                    </option>
                <?php else : ?>
                    <option value="-1">
                        Escolha o departamento primeiro...
                    </option>
                <?php endif ?>
            </select>
        </div>
        <div class="col-lg-3">
            <label for="entrevistaRespAluno">Responsável pelo Aluno</label>
            <select id="entrevistaRespAluno" name="entrevistaRespAluno"  class="form-element">
                <option value="0" <?=$this->selected($rowEnt['idresponsavel'] == '0')?> >
                    Escolha um respons&aacute;vel
                </option>

                <option value="-2" <?=$this->selected($rowEnt['idresponsavel'] == '-2')?> >
                    Pr&oacute;prio aluno
                </option>

                <option value="-1" <?=$this->selected($rowEnt['idresponsavel'] == '-1')?> >
                    Outro
                </option>

                <?php
                $query1 = "SELECT *, pessoas.id as pid, responsaveis.id as rid
                    FROM responsaveis, pessoas
                    WHERE responsaveis.idaluno=$idaluno
                    AND responsaveis.idpessoa=pessoas.id
                    ORDER BY nome ASC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) { ?>
                <option
                    value="<?=$row1['pid']?>"
                    <?=$this->selected($rowEnt['idresponsavel'] == $row1['pid'])?>
                >
                    <?=$row1['nome']?>
                </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-lg-3">
            <label>&nbsp;</label>
            <input
                type="text"
                placeholder="Outro responsável"
                name="entrevistaRespAlunoOutro"
                id="entrevistaRespAlunoOutro"
                class="form-element"
                <?php if ($rowEnt['idresponsavel'] < 0) : ?>
                    style="display:inline-block"
                <?php else : ?>
                    style="display:none"
                <?php endif ?>
                value="<?=$rowEnt['outro']?>"
            />
        </div>
    </div>
    <div class="row" style="margin-top: 10px;">
        <div class="col-lg-3">
            <label for="entrevistaassunto">Assunto</label>
            <input value="<?=$rowEnt['assunto']?>" type="text" id="entrevistaassunto" name="entrevistaassunto" class="form-element" />
        </div>
        <div class="col-lg-2">
            <label for="entrevistadata">Agendar para a data</label>
            <input type="date" id="entrevistadata" class="form-element" value="<?=$datamarcada?>" />
        </div>
        <div class="col-lg-2 clockpicker" data-autoclose="true">
            <label for="entrevistahora">Agendar para a hora</label>
            <input type="text" id="entrevistahora" data-mask="99:99" name="entrevistahora" class="form-element" value="<?=$horamarcada?>" />
        </div>
    </div>
    <div class="hr-line-dashed"></div>
    <div class="row">
        <div class="col-lg-2">
            <label for="entrevistadatarealizada">Data realizada</label>
            <input type="date" id="entrevistadatarealizada" name="entrevistadatarealizada" class="form-element" value="<?=$datarealizada?>"/>
        </div>
        <div class="col-lg-2 clockpicker" data-autoclose="true">
            <label for="entrevistahorarealizada">Hora realizada</label>
            <input type="text" id="entrevistahorarealizada" data-mask="99:99" name="entrevistahorarealizada" class="form-element" value="<?=$horarealizada?>" />
        </div>
    </div>
    <div class="row" style="margin-top: 10px;">
        <div class="col-lg-12">
            <label for="entrevistaresumo">Resumo</label>
            <textarea id="entrevistaresumo" class="form-element" rows="5"><?php echo  $rowEnt['resumo'] ?></textarea></th>
        </div>
    </div>
    <div class="row" style="margin-top: 10px;">
        <div class="col-lg-12">
            <input type="hidden" name="identrevista" id="identrevista" value="<?php echo  $identrevista ?>" />
            <?php if (in_array($alunos[0], $arraydo2)) { ?>
            <input type="button" name="addentrevista" id="addentrevista" class="btn green-color" value="<?php echo $identrevista ? 'Atualizar' : 'Adicionar' ?>" />
            <?php } ?>
        </div>
    </div>
    <?php } ?>
    <h3 class="section-forms">Entrevistas do aluno</h3>
    <div class="row">
        <div class="col-lg-12">
            <table class="new-table table table-striped" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="table-header-repeat line-left-2"><b>Responsável pela Escola/Departamento</b></th>
                        <th class="table-header-repeat line-left-2"><b>Responsável pelo aluno</b></th>
                        <th class="table-header-repeat line-left-2"><b>Assunto</b></th>
                        <th class="table-header-repeat line-left-2"><b>Data/Hora Agendada</b></th>
                        <th class="table-header-repeat line-left-2"><b>Data/Hora Realizada</b></th>
                        <th class="table-header-repeat line-left-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query1 = "SELECT *, alunos_entrevistas.id as entrevista_id, DATE_FORMAT(datahora,'%d/%m/%Y - %H:%i') AS 'dthora', DATE_FORMAT(datahorarealizada,'%d/%m/%Y - %H:%i') AS 'dthorarealizada', departamentos.departamento as ddep FROM alunos_entrevistas, departamentos WHERE alunos_entrevistas.iddepartamento=departamentos.id AND  idaluno=$idaluno ORDER BY datahorarealizada DESC ";
                    $result1 = mysql_query($query1);
                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                        $query2 = "SELECT nome FROM pessoas, funcionarios WHERE funcionarios.idpessoa=pessoas.id AND funcionarios.id=" . $row1['idfuncionario'];
                        $result2 = mysql_query($query2);
                        $row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
                        $nomefuncionario = $row2['nome'];
                        if ($row1['idresponsavel'] > 0) {
                            $query2 = "SELECT nome FROM pessoas WHERE id=" . $row1['idresponsavel'];
                            $result2 = mysql_query($query2);
                            $row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
                            $nomeresponsavel = $row2['nome'];
                        } else {
                            $nomeresponsavel = $row1['outro'];
                        }
                        $query4 = "SELECT idpessoa as pid, idunidade as uid, nummatricula, turmamatricula, status FROM alunos, alunos_matriculas WHERE alunos_matriculas.idaluno=alunos.id AND alunos.id=" . $idaluno . " AND alunos_matriculas.nummatricula=" . $nummatricula;
                        $result4 = mysql_query($query4);
                        $row4 = mysql_fetch_array($result4, MYSQL_ASSOC);
                        ?>
                    <tr id="linha-entrevista-<?php echo $row1['entrevista_id'] ?>">
                        <td><?php echo $nomefuncionario ?><br /><?php echo  $row1['ddep'] ?></td>
                        <td><?php echo $nomeresponsavel ?></td>
                        <td><?php echo $row1['assunto'] ?></td>
                        <td><?php echo $row1['dthora'] ?></td>
                        <td><?php echo $row1['dthorarealizada'] ?></td>
                        <td class="text-center">
                            <?php if (in_array($alunos[0], $arraydo2)) { ?>
                            <button
                                data-toggle="tooltip"
                                data-placement="bottom"
                                title="Editar"
                                type="button"
                                class="btn primary-color"
                                onclick="editarEntrevista('<?=$matricula->id?>','<?=$row1['entrevista_id']?>');"
                            >
                                <i class="fa fa-edit"></i>
                            </button>

                            <button data-toggle="tooltip" data-placement="bottom" title="Imprimir Entrevista" type="button" class="btn green-color" onclick="imprimeEntrevista(<?php echo $idaluno ?>,<?=$nummatricula?>,<?php echo $row1['entrevista_id'] ?>);">
                                <i class="fa fa-print"></i>
                            </button>
                            <?php } ?>
                            <?php if (in_array($alunos[0], $arraydo4)) { ?>
                            <button data-toggle="tooltip" data-placement="bottom" title="Excluir" type="button" class="btn danger-color" onclick="removeLigacao('alunos_entrevistas',<?php echo $row1['entrevista_id'] ?>);">
                                <i class="fa fa-trash-alt"></i>
                            </button>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editarEntrevista(matriculaId, entrevistaId) {
    sweduc.carregarUrl(
        'alunos_cadastra.php' +
        '?matriculaId=' + matriculaId +
        '&identrevista=' + entrevistaId
    );
}

function imprimeEntrevista(idaluno, nummatricula, identrevista) {
    $('#p_idaluno').val(idaluno);
    $('#p_nummatricula').val(nummatricula);
    $('#p_identrevista').val(identrevista);
    $('#formimprimeentrevista').submit();
}
</script>
