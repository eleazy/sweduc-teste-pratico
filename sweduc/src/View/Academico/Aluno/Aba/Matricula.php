<div role="tabpanel" class="tab-pane" id="tab_matricula">
    <h2 class="section-forms">
        Matrícula atual
    </h2>

    <div class="row">
        <div class="col-lg-4">
            <?php $mat = ($idaluno > 0) ? $rowA['nummatricula'] . ' / ' . $rowA['anoletivo'] : ''; ?>
            Matrícula: <u><b><?=$mat ?></b></u>
        </div>

        <div class="col-lg-4">
            Matriculado em: <?= $rowA['dtmatricula'] ?>
        </div>

        <div class="col-lg-4">
            Matriculado por : <?= $funcmatricula ?>
        </div>
    </div>

    <div class="hr-line-dashed"></div>

    <?php if ($rowA['status'] > 1) : ?>
        <div class="row">
            <div class="col-lg-12">
                <ul class="list-unstyled">
                    <li style="color:#FF0000;"><b><?=$rowA['nome_status']?> em:</b> <?=$rowA['dtstatus'] ?></li>
                    <li><b>Escola destino:</b> <?=$rowA['escoladestino'] ?></li>
                    <li><b>Motivo:</b> <?=motivoSituacao($rowA['motivoSituacao']) ?></li>
                    <li><b>Obs:</b> <?=$rowA['obsSituacao'] ?></li>
                </ul>
            </div>
        </div>

        <div class="hr-line-dashed"></div>
    <?php endif ?>

    <form name="mudarSitualcao" id="mudarSitualcao">
        <?php if (in_array($alunos[0], $arraydo4)) : ?>
            <div class="row">
                <div class="col-lg-3">
                    <label for="situcaoMatricula">Situação</label>
                    <select name="situcaoMatricula" id="situcaoMatricula" class="form-element">
                        <?php foreach ($alunosStatus as $status) : ?>
                            <?php if ($status->mostrar == 0) {
                                continue;
                            } ?>

                            <option value="<?= $status->id ?>" aria-details="<?= $status->confirmaMsg ?>" <?= $this->selected($status->id == $rowA['status']) ?>>
                                <?= $status->nome ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="col-lg-3">
                    <label for="situcaoMatricula">Motivo</label>
                    <select name="motivoSituacao" id="motivoSituacao"  class="form-element">
                        <option value="0">Selecione o Motivo</option>
                        <?php
                        $query = "SELECT * FROM motivo WHERE aplicacao='2' ORDER BY motivo ASC";
                        $result = mysql_query($query);
                        while ($rowAluno = mysql_fetch_array($result, MYSQL_ASSOC)) {
                            ?>
                        <option  value="<?= $rowAluno['id'] ?>" ><?= $rowAluno['motivo'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-lg-3">
                    <?php
                    $queryescola = "SELECT
                        *
                    FROM
                        alunos
                    WHERE
                        id = " . $rowA['idaluno'];
                    $resultescola = mysql_query($queryescola);
                    $rowescola = mysql_fetch_array($resultescola, MYSQL_ASSOC);
                    ?>
                    <label for="escoladestino">Escola destino</label>
                    <input type="text" class="form-element escolaCadastra" id="escoladestino" value="" name="escoladestino" value="" />
                </div>
            </div>

            <div class="row" style="margin-top: 10px;">
                <div class="col-lg-9">
                    <label for="obsSituacao">OBS</label>
                    <textarea id="obsSituacao" name="obsSituacao" rows="5" class="form-element"></textarea>
                </div>
            </div>

            <div class="row" style="margin-top: 10px;">
                <div class="col-lg-12">
                    <input
                        type="button"
                        id="btMmudaSituacao"
                        value="Mudar Situação"
                        name="btMmudaSituacao"
                        class="btn primary-color"
                    />
                </div>
            </div>
        <?php endif ?>

        <input type="hidden" name="idunidadecadastraFEITA"  id="idunidadecadastraFEITA" value="<?=$rowA['idunidade'] ?>" />
        <input type="hidden" name="matriculaId"  id="matriculaId" value="<?=$rowA['idmatricula'] ?>" />
        <input type="hidden" name="nummatriculaFEITA"  id="nummatriculaFEITA" value="<?=$rowA['nummatricula'] ?>" />
        <input type="hidden" name="datamatriculadoFEITA"  id="datamatriculadoFEITA" value="<?=$rowA['dtmatricula'] ?>" />
        <input type="hidden" name="datamatriculaFEITA"  id="datamatriculaFEITA" value="<?=$rowA['dtmatricula'] ?>" />

        <div class="row" style="margin-top: 10px;">
            <div class="col-lg-3">
                <?php if ($rowA['turmamatricula'] != "-1") : ?>
                    <ul class="list-unstyled">
                        <li><b>Anuidade:</b> <?=$this->dindin($rowA['valorAnuidade']) ?></li>
                        <li id="editarAnuidade" class="hidden mb-1">
                            <div class="row">
                                <div class="col-xs-7">
                                    <label for="anuidade-mensalidade"><small>Mensalidade</small></label>
                                    <input type="number" name="anuidade-mensalidade" id="anuidade-mensalidade" class="form-element" value="<?=$rowA['valorAnuidade'] / max(1, $rowA['qtdparcelas'])?>">
                                </div>

                                <div class="col-xs-5">
                                    <label for="anuidade-parcelas"><small>Parcelas</small></label>
                                    <input type="number" name="anuidade-parcelas" id="anuidade-parcelas" class="form-element" value="<?=$rowA['qtdparcelas']?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-7">
                                    <label for="anuidade-bolsa-abs"><small>Bolsa (R$)</small></label>
                                    <input type="number" name="anuidade-bolsa-abs" id="anuidade-bolsa-abs" class="form-element" value="<?=number_format($rowA['bolsa'] / max($rowA['qtdparcelas'], 1), 2)?>">
                                </div>

                                <div class="col-xs-5">
                                    <label for="anuidade-bolsa-perc"><small>Bolsa (%)</small></label>
                                    <div class="input-group">
                                        <input type="number" name="anuidade-bolsa-perc" id="anuidade-bolsa-perc" class="form-element" value="<?=$rowA['bolsapercentual']?>">
                                        <div class="input-group-addon">%</div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn primary-color mt-10px" onclick="atualizaAnuidade();$(this).parent().addClass('hidden')">Salvar</button>
                        </li>
                        <?php if ($usuario->autorizado('academico-alunos-alterar-anuidade')) : ?>
                            <li>
                                <a onclick="$('#editarAnuidade').removeClass('hidden')">Atualizar</a>
                            </li>
                        <?php endif ?>
                        <li><b>Bolsa Inicial:</b> <?=$this->dindin($rowA['bolsaAluno']) . " (" . number_format($rowA['bolsapercentualAluno'], 1) . "%)" ?></li>
                        <li><b>Anuidade do aluno:</b> <?=$this->dindin($rowA['valorAnuidadeAluno']) ?></li>
                        <li><b>Parcelamento:</b> <?=$this->dindin($rowA['valorAnuidadeAluno']) ?></li>
                        <li>
                            <b>Responsável Financeiro:</b>
                            <?=$respFinNome ?? ''?>
                            (<?=$respFinContatos?>)
                        </li>
                    </ul>
                <?php endif ?>
            </div>

            <div class="col-lg-9">
                <h5 style="margin-top: 0;">Histórico de parcelamentos</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Valor</th>
                            <th>Bolsa</th>
                            <th>Vencimento</th>
                            <th>Reajustado</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $queryparc = "SELECT alunos_fichafinanceira.titulo as titulo, alunos_fichafinanceira.valor as valor,alunos_fichafinanceira.bolsapercentual as bolsap, alunos_fichaitens.eventofinanceiro as evento, alunos_fichafinanceira.datavencimento as datavencimento, alunos_fichafinanceira.situacao as situacao, alunos_fichafinanceira.reajustado as reajustado FROM alunos_fichafinanceira
                        INNER JOIN alunos_fichaitens ON alunos_fichafinanceira.id=alunos_fichaitens.idalunos_fichafinanceira
                        WHERE alunos_fichafinanceira.nummatricula=" . $rowA['nummatricula'] . "  and idaluno = " . $rowA['idaluno'] . " AND alunos_fichafinanceira.situacao in (0,1) AND alunos_fichafinanceira.matricula = 1 ORDER BY alunos_fichafinanceira.datavencimento ASC";
                        $resultparc = mysql_query($queryparc);
                        while ($rowparc = mysql_fetch_array($resultparc, MYSQL_ASSOC)) {
                            $recebeureajuste = ($rowparc['reajustado'] == 1) ? 'Sim' : '';
                            $datavenc = explode('-', $rowparc['datavencimento']);
                            $titulosituacao = ($rowparc['situacao'] == 1) ? '<span style="color:#00FF00;">Recebido</span>' : 'Aberto';
                            ?>
                        <tr>
                            <td><?=$rowparc['titulo'] . ' - ' . $rowparc['evento'] ?></td>
                            <td><?=money_format('%!.2n', $rowparc['valor']) ?></td>
                            <td><?=$rowparc['bolsap'] ?></td>
                            <td><?=$datavenc[2] . '/' . $datavenc[1] . '/' . $datavenc[0] ?></td>
                            <td><?=$recebeureajuste ?></td>
                            <td><?=$titulosituacao ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
    <div class="hr-line-dashed"></div>

    <?php if ($idpermissoes > 0) : ?>
        <?php if ($rowA['status'] == 1) : // SOMENTE PARA ALUNOS COM MATRÍCULA ATIVA ?>
            <div class="row">
                <div class="col-lg-6">
                    <?php
                    $query2 = "SELECT id, anoletivo FROM anoletivo ORDER BY anoletivo DESC ";
                    $result2 = mysql_query($query2);
                    ?>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="col-sm-2 control-label">Ano Letivo</label>
                        <div class="col-sm-10">
                            <?php while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) : ?>
                                <?php if (( $row2['id'] == $rowA['idanoletivo'])) : ?>
                                    <input type="hidden" id="anoletivomatriculado" name="anoletivomatriculado" value="<?= $row2['id']; ?>"/>
                                    <p class="form-control-static"><?=$row2['anoletivo']; ?></p>
                                <?php endif ?>
                            <?php endwhile ?>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="col-sm-2 control-label">Unidade</label>
                        <div class="col-sm-10">
                            <?php
                            if ($idpermissoes == "1") {
                                $queryidunidadematriculado = "SELECT * FROM unidades GROUP BY unidade ORDER BY unidade ASC";
                            } else {
                                if (($unidades <> "0") && (trim($unidades) <> "")) {
                                    $queryidunidadematriculado = "SELECT * FROM unidades WHERE id IN (" . $unidades . ") ORDER BY unidade ASC";
                                } else {
                                    if ($idfuncionariounidade == "0") {
                                        $queryidunidadematriculado = "SELECT * FROM unidades GROUP BY unidade ORDER BY unidade ASC";
                                    } else {
                                        $queryidunidadematriculado = "SELECT * FROM unidades WHERE id=" . $idfuncionariounidade . " GROUP BY unidade ORDER BY unidade ASC";
                                    }
                                }
                            }

                            $resultidunidadematriculado = mysql_query($queryidunidadematriculado);
                            ?>
                            <?php while ($rowidunidadematriculado = mysql_fetch_array($resultidunidadematriculado, MYSQL_ASSOC)) : ?>
                                <?php if (( $rowidunidadematriculado['id'] == $idunidade)) : ?>
                                    <input type="hidden" id="idunidadematriculado" name="idunidadematriculado" value="<?= $rowidunidadematriculado['id']; ?>" />
                                    <p class="form-control-static"><?=$rowidunidadematriculado['unidade']; ?></p>
                                <?php endif ?>
                            <?php endwhile ?>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="col-sm-2 control-label">
                            Empresa
                        </label>

                        <div class="col-sm-10">
                            <?php
                            if ($idpermissoes == "1") {
                                $queryidempresamatriculado = "SELECT * FROM empresas ORDER BY razaosocial ASC";
                            } else {
                                if (($unidades <> "0") && (trim($unidades) <> "")) {
                                    $queryidempresamatriculado = "SELECT * FROM empresas ORDER BY razaosocial ASC";
                                } else {
                                    if ($idfuncionariounidade == "0") {
                                        $queryidempresamatriculado = "SELECT * FROM empresas ORDER BY razaosocial ASC";
                                    } else {
                                        $queryidempresamatriculado = "SELECT * FROM empresas ORDER BY razaosocial ASC";
                                    }
                                }
                            }
                            $resultidempresamatriculado = mysql_query($queryidempresamatriculado);
                            ?>
                            <?php while ($rowidempresamatriculado = mysql_fetch_array($resultidempresamatriculado, MYSQL_ASSOC)) : ?>
                                <?php if (( $rowidempresamatriculado['id'] == $idempresa)) : ?>
                                    <input type="hidden" id="idempresamatriculado" name="idempresamatriculado" value="<?= $rowidempresamatriculado['id']; ?>" />
                                    <p class="form-control-static"><?=$rowidempresamatriculado['razaosocial']; ?></p>
                                <?php endif ?>
                            <?php endwhile ?>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="col-sm-2 control-label">
                            Curso
                        </label>

                        <div class="col-sm-10">

                        <?php if ($idaluno > 0) :
                            $query1 = "SELECT * FROM cursos WHERE idunidade=" . $rowA['idunidade'];
                            $result1 = mysql_query($query1);
                            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) :
                                if (( $row1['id'] == $rowA['idcurso'])) : ?>
                                    <input type="hidden" id="cursomatriculado" name="cursomatriculado" value="<?= $row1['id']; ?>" />
                                    <p class="form-control-static"><?=$row1['curso']; ?></p>
                                <?php endif ?>
                            <?php endwhile ?>
                        <?php endif ?>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="col-sm-2 control-label">
                            Série
                        </label>

                        <div class="col-sm-10">
                            <?php if ($idaluno > 0) :
                                $query1 = "SELECT * FROM series WHERE idcurso=" . $rowA['idcurso'];
                                $result1 = mysql_query($query1);
                                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) :
                                    if ($row1['id'] == $rowA['idserie']) : ?>
                                        <input type="hidden" id="seriematriculado" name="seriematriculado" value="<?= $row1['id']; ?>" />
                                        <p class="form-control-static"><?=$row1['serie']; ?></p>
                                    <?php endif ?>
                                <?php endwhile ?>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">
                            Turma
                        </label>

                        <div class="flex -m-2 col-sm-7">
                            <input type="hidden" id="turmamatriculadoantiga" name="turmamatriculadoantiga" value="<?= $rowA['turmamatricula'] ?>" >
                            <div class="p-2">
                                <select id="turmamatriculado" name="turmamatriculado" class="form-element"></select>
                            </div>
                            <?php if ($usuario->autorizado('academico-alunos-editar')) : ?>
                                <div class="p-2">
                                    <input
                                        type="button"
                                        value="Trocar turma"
                                        class="sw-btn sw-btn-secondary"
                                        id="trocaTurma"
                                    />
                                </div>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">
                            Plano de Horários
                        </label>

                        <div class="flex -m-2 col-sm-10">
                            <div class="p-2">
                                <select id="planohorariosmatriculado" name="planohorariosmatriculado" class="form-element">
                                    <?php
                                    $queryplanohorariosmatriculado = "SELECT *, DATE_FORMAT(entrada,'%H:%i') AS 'ent', DATE_FORMAT(saida,'%H:%i') AS 'sai', valor FROM planohorarios WHERE habilitado = 1 OR id = " . $rowA['idplanohorario'] . " ORDER BY codigo ASC";
                                    $resultplanohorariosmatriculado = mysql_query($queryplanohorariosmatriculado);
                                    while ($rowplanohorariosmatriculado = mysql_fetch_array($resultplanohorariosmatriculado, MYSQL_ASSOC)) {
                                        echo '<option valor="' . $rowplanohorariosmatriculado['valor'] . '" value="' . $rowplanohorariosmatriculado['id'] . '" ';
                                        if ($rowplanohorariosmatriculado['id'] == $rowA['idplanohorario']) {
                                            echo 'selected="selected" ';
                                        }
                                        if ($rowplanohorariosmatriculado['habilitado'] == 0) {
                                            echo 'style="color: gray; font-style: italic;" ';
                                        }
                                        echo '>' . $rowplanohorariosmatriculado['codigo'] . ' - Entrada:' . $rowplanohorariosmatriculado['ent'] . ' Saída:' . $rowplanohorariosmatriculado['sai'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <?php if (in_array($alunos[0], $arraydo4)) { ?>
                                <div class="p-2 matObr">
                                    <input
                                        id="trocaPHorarios"
                                        type="button"
                                        value="Trocar Plano de Horários"
                                        class="sw-btn sw-btn-secondary"
                                    >
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php if ($usuario->autorizado('academico-alunos-editar')) : ?>
                    <div class="flex flex-wrap col-lg-8">
                        <div class="w-full sm:w-auto">
                            <label class="switch-toggle" for="mudaseguroescolar">
                                <input
                                    id="mudaseguroescolar"
                                    name="seguroescolar"
                                    type="checkbox"
                                    <?=$this->checked($rowA['seguroescolar'] == 1)?>
                                >
                                Seguro Escolar
                            </label>
                        </div>

                        <div class="w-full sm:w-auto">
                            <label class="switch-toggle" for="mudareajuste">
                                <input
                                    id="mudareajuste"
                                    name="recebereajuste"
                                    type="checkbox"
                                    <?=$this->checked($rowA['recebereajuste'] == 1)?>
                                >
                                Reajuste anual
                            </label>
                        </div>

                        <div class="w-full sm:w-auto">
                            <label class="switch-toggle" for="presencial">
                                <input
                                    id="presencial"
                                    name="presencial"
                                    type="checkbox"
                                    <?=$this->checked($rowA['presencial'] == 1)?>
                                >
                                Aula presencial
                            </label>
                        </div>
                        <?php if ($usaMatriculaOnline && $usuario->autorizado('financeiro-controle-rematricula-matricula-online')) : ?>
                            <div class="w-full sm:w-auto">
                                <label class="switch-toggle" for="matricula_online">
                                    <input
                                        id="matricula_online"
                                        name="matricula_online"
                                        type="checkbox"
                                        <?=$this->checked($rowA['matricula_online'] == 1)?>
                                    >
                                    Matricula online
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif ?>
            </div>
        <?php endif ?>
    <?php endif ?>
</div>

<script>
    $(function() {
        $.ajax({
            url: "dao/turmas.php",
            type: "POST",
            data: {
                action: "recebeTurmas",
                idserie: $('#seriematriculado').val(),
                anoletivomatricula: $("#anoletivomatriculado").val()
            },
            context: jQuery('#turmamatriculado'),
            success: function (data) {
                this.html(data);
                $('#turmamatriculado option[value="<?=$turmamatricula?>"]').prop('selected', true);
            }
        })
    })

    $("#trocaTurma").click(function () {
        const matriculaId = $("#idmatricula").val();

        swal({
            title: "Atenção",
            text: "Confirma trocar a turma ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#64B5F6",
            confirmButtonText: "Confirmar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: true
        }, function () {
            $.ajax({
                url: "/api/v1/academico/matriculas/" + matriculaId + "/trocar-turma",
                type: "PUT",
                contentType: "application/json",
                data: JSON.stringify({
                    novaTurma: $("#turmamatriculado").val(),
                }),
                success: function (data) {
                    criaAlerta('success', 'Matrícula transferida de turma');
                    const alunoId = $("#idaluno").val();

                    sweduc.carregarUrl(
                        'alunos_cadastra.php' +
                        '?alunoId=' + alunoId +
                        '&matriculaId=' + matriculaId
                    );
                },
                error: function () {
                    criaAlerta('error', 'Erro ao trocar turma');
                }
            });
        });
    });

    if (document.getElementById('mudaseguroescolar')) {
        document.getElementById('mudaseguroescolar').addEventListener('change', function (event) {
            const matriculaId = document.getElementById('matriculaId').value;
            const matricula = new Academico.Matricula(matriculaId);

            const atributo = event.currentTarget.name;
            const toggleState = event.currentTarget.checked;

            matricula[atributo] = toggleState;
            matricula.salvar();
        });
    }

    if (document.getElementById('mudareajuste')) {
        document.getElementById('mudareajuste').addEventListener('change', function (event) {
            const matriculaId = document.getElementById('matriculaId').value;
            const matricula = new Academico.Matricula(matriculaId);

            const atributo = event.currentTarget.name;
            const toggleState = event.currentTarget.checked;

            matricula[atributo] = toggleState;
            matricula.salvar();
        });
    }

    if (document.getElementById('presencial')) {
        document.getElementById('presencial').addEventListener('change', function (event) {
            const matriculaId = document.getElementById('matriculaId').value;
            const matricula = new Academico.Matricula(matriculaId);

            const atributo = event.currentTarget.name;
            const toggleState = event.currentTarget.checked;

            matricula[atributo] = toggleState;
            matricula.salvar();
        });
    }

    if (document.getElementById('matricula_online')) {
        document.getElementById('matricula_online').addEventListener('change', function (event) {
            const matriculaId = document.getElementById('matriculaId').value;
            const matricula = new Academico.Matricula(matriculaId);

            const atributo = event.currentTarget.name;
            const toggleState = event.currentTarget.checked;

            matricula[atributo] = toggleState;
            matricula.salvar();
        });
    }
</script>
