<div role="tabpanel" class="tab-pane" id="tab_nova_matricula">
    <section>
        <h2 class="section-forms">
            Nova matrícula
        </h2>

        <div class="form-group">
            <label class="col-sm-2 control-label" for="anoletivomatricula">
                Ano letivo
            </label>

            <div class="col-sm-3">
                <select id="anoletivomatricula" name="anoletivomatricula" class="form-element">
                    <?php foreach ($periodosLetivos as $periodoLet) { ?>
                        <option
                            value="<?=$periodoLet->id?>"
                            <?=$this->selected($periodoLet->anoletivo == $periodoPadraoMatricula)?>
                        >
                            <?=$periodoLet->anoletivo?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label" for="nova-matricula-seguroescolar">
                Seguro Escolar
            </label>

            <div class="col-sm-3">
                <input
                    id="nova-matricula-seguroescolar"
                    name="nova-matricula-seguroescolar"
                    type="checkbox"
                    class="mt-3"
                    value="1"
                />
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label" for="nova-matricula-presencial">
                Aula presencial
            </label>

            <div class="col-sm-3">
                <input
                    id="nova-matricula-presencial"
                    name="nova-matricula-presencial"
                    type="checkbox"
                    class="mt-3"
                    checked
                    value="1"
                />
            </div>
        </div>
    </section>

    <section>
        <h2 class="section-forms" id="pedagogico">
            Pedagógico
        </h2>

        <input type="hidden" name="datamatriculado" id="datamatriculado" value="0000-00-00"/>

        <div class="form-group">
            <label for="idunidadecadastra" class="col-sm-2 control-label">Unidade</label>
            <div class="col-sm-3">
                <select id="idunidadecadastra" name="idunidadecadastra" class="form-element">
                    <option value="-1" selected="selected"> </option>
                    <option value=" - " selected="selected"> - </option>
                    <?php
                    if ($idpermissoes == "1") {
                        $queryunidades = "SELECT * FROM unidades GROUP BY unidade ORDER BY unidade ASC";
                    } else {
                        if (($unidades <> "0") && (trim($unidades) <> "")) {
                            $queryunidades = "SELECT * FROM unidades WHERE id IN (" . $unidades . ") ORDER BY unidade ASC";
                        } else {
                            if ($idfuncionariounidade == "0") {
                                $queryunidades = "SELECT * FROM unidades GROUP BY unidade ORDER BY unidade ASC";
                            } else {
                                $queryunidades = "SELECT * FROM unidades WHERE id=" . $idfuncionariounidade . " GROUP BY unidade ORDER BY unidade ASC";
                            }
                        }
                    }
                    $resultunidades = mysql_query($queryunidades);
                    while ($rowunidades = mysql_fetch_array($resultunidades, MYSQL_ASSOC)) {
                        ?>
                        <option value="<?php echo  $rowunidades['id'] ?>"><?php echo  $rowunidades['unidade'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="idempresacadastra" class="col-sm-2 control-label">Empresa</label>
            <div class="col-sm-3">
                <select
                    id="idempresacadastra"
                    data-toggle="tooltip"
                    data-placement="bottom"
                    title="Escolha a unidade para carregar as empresas"
                    name="idempresacadastra"
                    class="form-element"
                >
                    <option value="-1" selected="selected"> </option>
                    <option value="-1" selected="selected"> - </option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="datamatricula" class="col-sm-2 control-label">Data da matrícula</label>
            <div class="col-sm-3">
                <input type="date" name="datamatricula" id="datamatricula" class="form-element" value="<?=date('Y-m-d')?>"/>
            </div>
        </div>

        <div class="form-group">
            <label for="cursomatricula" class="col-sm-2 control-label">Curso</label>
            <div class="col-sm-3">
                <select id="cursomatricula" name="cursomatricula" class="form-element">
                    <option value="-1"> - </option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="seriematricula" class="col-sm-2 control-label">Série</label>
            <div class="col-sm-3">
                <select id="seriematricula" name="seriematricula" class="form-element">
                    <option value="-1"> - </option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="turmamatricula" class="col-sm-2 control-label">Turma</label>
            <div class="col-sm-5">
                <select id="turmamatricula" name="turmamatricula" class="form-element">
                    <option value="-1"> - </option>
                </select>
                <div id="turmamatriculaquantalunos" style="display:none">
                    <input type="hidden" name="quantlimitealunosturma" id="quantlimitealunosturma" value="-1">
                    <input type="hidden" name="quantalunosturma" id="quantalunosturma" value="-1">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="planohorarios" class="col-sm-2 control-label">Plano de horários</label>
            <div class="col-sm-5">
                <select id="planohorarios" name="planohorarios" class="form-element">
                    <?php
                    $queryplanohorarios = "SELECT *, DATE_FORMAT(entrada,'%H:%i') AS 'ent', DATE_FORMAT(saida,'%H:%i') AS 'sai', valor FROM planohorarios WHERE habilitado = 1 ORDER BY codigo ASC";
                    $resultplanohorarios = mysql_query($queryplanohorarios);
                    while ($rowplanohorarios = mysql_fetch_array($resultplanohorarios, MYSQL_ASSOC)) : ?>
                        <option
                            valor="<?=$rowplanohorarios['valor']?>"
                            value="<?=$rowplanohorarios['id']?>"
                        >
                            <?=$rowplanohorarios['codigo'] . ' - Entrada:' . $rowplanohorarios['ent'] . ' Saída:' . $rowplanohorarios['sai']?>
                        </option>
                    <?php endwhile ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="escolaCadastra" class="col-sm-2 control-label">Escola de origem</label>
            <div class="col-sm-3">
                <input type="text" id="escolaCadastra" class="form-element" value="<?=$escola_origem?>" name="escolaorigem" />
            </div>
        </div>

        <div class="form-group">
            <label for="contratoDesempenho" class="col-sm-2 control-label">Contrato de desempenho</label>
            <div class="col-sm-3">
                <select id="contratoDesempenho" name="contratoDesempenho" class="form-element">
                    <option value="0" <?=$contrato_desempenho != 1 ? 'selected' : '' ?> > Não</option>
                    <option value="1" <?=$contrato_desempenho == 1 ? 'selected' : '' ?>> Sim</option>
                </select>
            </div>
        </div>
    </section>

    <section>
        <h2 class="section-forms">
            Financeiro
        </h2>

        <div class="form-group">
            <label for="eventosMatricula" class="col-sm-2 control-label">
                Evento financeiro
            </label>

            <div class="col-sm-4">
                <select id="eventosMatricula" name="eventosMatricula" size="1" class="form-element" onchange='if (this.value == -1)
                                                                        $("#eventosMatricula").val(0);'>
                    <option value="-1">Selecione um evento...</option>
                    <?php
                    $query1 = "SELECT * FROM eventosfinanceiros WHERE codigo LIKE '1%' ORDER BY codigo ASC";
                    $result1 = mysql_query($query1);
                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                        $opcaohabiltada = ($row1['eventoselecionavel'] == '0') ? ' disabled' : '';
                        if ($row1['codigo'][1] == '0') {
                            echo '<option value="-1" style="background-color:#00F; color:#FFF;" ' . $opcaohabiltada . '>';
                        } else {
                            echo '<option value="' . $row1['codigo'] . '@' . $row1['eventofinanceiro'] . '" ' . $opcaohabiltada . '>';
                        }
                        for ($i = 0; $i < (4 - substr_count($row1['codigo'], '0')); $i++) {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                        }
                        echo $row1['eventofinanceiro'] . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="idcontasbanco" class="col-sm-2 control-label">Receber na conta/caixa</label>
            <div class="col-sm-4">
                <select id="idcontasbanco" name="idcontasbanco" size="1" class="form-element">
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="valorAnuidade" class="col-sm-2 control-label">Valor da Anuidade da Série</label>
            <div class="col-sm-3">
                <input type="text" id="valorAnuidade" name="valorAnuidade" class="form-element">
            </div>
        </div>
    </section>

    <section>
        <h2 class="section-forms">
            Geração de títulos
        </h2>

        <div class="form-group">
            <label for="valorAtabeladescontonuidade" class="col-sm-2 control-label">Possui bolsa?</label>
            <div class="col-sm-3">
                <select id="tabeladesconto" name="tabeladesconto" class="form-element">
                    <option value="0">Selecione... </option>
                    <?php
                    $querytd = "SELECT * FROM financeiro_tabeladescontos ORDER BY descricao ASC";
                    $resulttd = mysql_query($querytd);
                    while ($rowtd = mysql_fetch_array($resulttd, MYSQL_ASSOC)) {
                        echo '<option value="' . $rowtd['id'] . '_' . $rowtd['percentual'] . '"> ' . $rowtd['percentual'] . '% | ' . $rowtd['descricao'] . ' </option>';
                    }?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">
                Bolsa na Anuidade
            </label>

            <div class="col-sm-6 -m-1">
                <div class="row p-1">
                    <div class="col-sm-2">
                        <label for="tipobolsa1">
                            <input
                                type="radio"
                                name="tipobolsa"
                                id="tipobolsa1"
                                value="1"
                                checked="checked"
                                class="align-middle mr-1"
                            />
                            R$
                        </label>
                    </div>

                    <div class="col-sm-3">
                        <input
                            type="text"
                            name="descontoparcelas"
                            id="descontoparcelas"
                            class="form-element"
                            value="0,00"
                        />
                    </div>
                </div>

                <div class="row p-1">
                    <div class="col-sm-2">
                        <label for="tipobolsa2">
                            <input
                                type="radio"
                                name="tipobolsa"
                                id="tipobolsa2"
                                value="0"
                                class="align-middle mr-1"
                            />
                            %
                        </label>
                    </div>

                    <div class="col-sm-3">
                        <input
                            type="text"
                            name="descontoparcelaspercentual"
                            id="descontoparcelaspercentual"
                            class="form-element"
                            value="0"
                            disabled
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="valorDaAnuidadeAluno" class="col-sm-2 control-label">Valor da Anuidade do Aluno</label>
            <div class="col-sm-3">
                <input type="text" readonly="readonly" class="form-element" id="valorDaAnuidadeAluno" name="valorDaAnuidadeAluno">
            </div>
        </div>

        <div class="form-group">
            <label for="planosparcelamento" class="col-sm-2 control-label">Planos de Parcelamento</label>
            <div class="col-sm-4">
                <select id="planosparcelamento" name="planosparcelamento" class="form-element" width="290px" style="width:290px">
                    <option value="-1">Selecione primeiro a série do aluno</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="qtdeparcelas" class="col-sm-2 control-label">Quantidade de Parcelas</label>
            <div class="col-sm-3">
                <input type="text" name="qtdeparcelas" id="qtdeparcelas" class="form-element" value="1" />
            </div>
        </div>

        <div class="form-group">
            <label for="valorparcelas" class="col-sm-2 control-label">Valor das Parcelas (R$)</label>
            <div class="col-sm-3">
                <input type="text" name="valorparcelas" id="valorparcelas" class="form-element" />
            </div>
        </div>

        <div class="form-group">
            <label for="data1parcela" class="col-sm-2 control-label">Data do 1º vencimento</label>
            <div class="col-sm-3">
                <input type="date" name="data1parcela" id="data1parcela" class="form-element" />
            </div>
        </div>

        <div class="form-group">
            <label for="descontoboleto" class="col-sm-2 control-label">Recebe desconto no boleto</label>
            <div class="col-sm-3">
                <input type="checkbox" name="descontoboleto" id="descontoboleto" value="1" checked="checked" />
            </div>
        </div>

        <div class="form-group">
            <label for="pagamento-cartao-online" class="col-sm-2 control-label">Recebe com cartao</label>
            <div class="col-sm-3">
                <input type="checkbox" name="pagamento-cartao-online" id="pagamento-cartao-online" />
            </div>
        </div>
    </section>
</div>
