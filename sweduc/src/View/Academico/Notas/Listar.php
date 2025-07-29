<div class="hidden print:block">
    <span id="anoletivoPrint"></span> - <span id="unidadePrint"></span><br>
    <span id="gradePrint"></span><br>
    <span id="periodoPrint"></span><br>
    <span id="nomeprofessor"></span><br>

    <div class="flex my-3">
        <div class="w-1/2">
            <span>Data de emissão: <?=date('d/m/Y');?></span>
        </div>

        <div class="w-1/2">
            <span>Assinatura professor(a):</span>
        </div>
    </div>
</div>

<?php if (!$multiplasAvaliacoes) : ?>
<div class="print:hidden w-full my-3 text-right">
    <button
        type="button"
        class="sw-btn sw-btn-secondary"
        onclick="document.getElementById('modal-importador-notas').classList.toggle('hidden')"
    >
        Importar CSV
    </button>

    <?php $this->insert('Academico/Notas/ModalImportador'); ?>
</div>
<?php endif ?>

<div class="my-3">
    <table style="width: 100%;" class="new-table table-striped prod-table">
        <thead id="headnotas">
            <tr>
                <th class="table-header-repeat line-left-2" colspan="2">
                    <b>Aluno</b>
                </th>

                <?php if ($educacaoInfantil || $btnMedias) : ?>
                    <th class="table-header-repeat line-left-2 noPrint"></th>
                <?php endif ?>

                <?php if ($faltasPorDisciplina) : ?>
                    <th class="table-header-repeat line-left-2" style="width: 20px;">
                        <b>Faltas</b>
                    </th>
                <?php endif ?>

                <?php foreach ($avaliacoes as $row) : ?>
                    <?php if (!empty($notasImportadas)) : ?>
                        <th class="table-header-repeat line-left-2">
                            <b>
                                <?=$row['avaliacao']?>
                                (Nota antiga)
                            </b>
                        </th>
                    <?php endif ?>

                    <th class="table-header-repeat line-left-2">
                        <b>
                            <?=$row['avaliacao']?>
                            <?=$this->eif('(Nota de importação)', !empty($notasImportadas))?>
                        </b>
                    </th>
                <?php endforeach ?>
            </tr>
        </thead>

        <tbody id="bodynotas">
            <?php foreach ($alunos as $aluno) : ?>
                <tr>
                    <td width="10px" class="text-center" style="padding:5px;">
                        <?=$cnt++?>
                    </td>

                    <td width="400px">
                        <input type="hidden" name="idaluno[]" value="<?=$aluno['aid']?>" />
                        <?=$aluno['nome']?>
                    </td>

                    <?php if ($educacaoInfantil || $btnMedias) : ?>
                        <td class="noPrint">
                            <input
                                type="button"
                                class="btn green-color"
                                id="medias<?= $aluno['aid'] ?>"
                                onclick="medias(
                                    '<?= $aluno['turmamatricula'] ?>',
                                    '<?= $aluno['anoletivomatricula'] ?>',
                                    '<?= $aluno['nome'] ?>',
                                    <?= $aluno['aid'] ?>,
                                    <?= $aluno['nummatricula'] ?>,
                                    <?= $gradeId ?>
                                );"
                                value="Médias"
                            />
                        </td>
                    <?php endif ?>

                    <?php if ($faltasPorDisciplina) : ?>
                        <td>
                            <input
                                type="text"
                                name="faltas[]"
                                id="faltas<?=$cnt?>"
                                class="form-element print:border-0 print:shadow-none text-right"
                                style="width: 4rem"
                                value="<?=$aluno['faltas']?>"
                                <?=$this->eif("disabled='disabled'", $multiplasAvaliacoes)?>
                            />
                        </td>
                    <?php endif ?>

                    <?php foreach ($aluno['avaliacoes'] as $avaliacaoId => $nota) : ?>
                        <?php if (!empty($notasImportadas)) : ?>
                            <td>
                                <?=$nota?>
                            </td>
                        <?php endif ?>

                        <td>
                            <input
                                type="text"
                                name="nota[<?= $aluno['aid']?>][]"
                                id="nota<?=$cnt?>"
                                data-id="<?=$cnt?>"
                                class="form-element soma<?=$avaliacaoId?> camponota print:border-0 print:shadow-none"
                                style="width:70px;"
                                value="<?=!empty($notasImportadas) ? $aluno['notaImportada'] : $nota?>"
                                <?php if ($educacaoInfantil === false) : ?>
                                    onclick="notaEditar(<?=$avaliacaoId?>)"
                                    onblur="if($.trim(this.value)!='') if (!(isNaN(this.value))) this.value=parseFloat(this.value).toFixed(<?=$casasdecimaisnotas?>)"
                                    onkeypress="return isNumberKey(event)"
                                <?php else : ?>
                                    onclick="notaEditar(<?=$avaliacaoId?>)"
                                <?php endif ?>
                                <?=$this->eif("disabled='disabled'", $multiplasAvaliacoes)?>
                            />

                            <input
                                type="hidden"
                                name="cond[<?=$aluno['aid']?>][<?=$avaliacaoId?>]"
                                id="cond<?=$avaliacaoId?>"
                                class="soma<?=$avaliacaoId?>"
                                value="false"
                            />

                            <input
                                type="hidden"
                                name="idavaliacao[<?=$aluno['aid']?>][]"
                                id="idavaliacao<?=$cnt?>"
                                class="form-element peq"
                                value="<?=$avaliacaoId?>"
                            />
                        </td>

                        <script>
                            $(".soma<?= $avaliacaoId ?>").blur(function () {
                                valor = 0;
                                $(".soma<?=$avaliacaoId?>").each(function () {
                                    valor = parseFloat($(this).val()) + valor;
                                });

                                valor = valor / $("#totalalunos").val();
                                $('#media<?=$avaliacaoId?>').html(valor.toFixed(<?= $casasdecimaisnotas ?>));
                            });
                        </script>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>

            <?php if ($educacaoInfantil == 0) :
                $notav = 0;
                $notal = 0;
                $notaa = 0;
                $notab = 0;
                ?>
                <tr>
                    <td></td>
                    <td><strong>MÉDIA:</strong></td>

                    <?php foreach (array_column($avaliacoes, 'id') as $idava) : ?>
                        <?php
                        $query = "SELECT * FROM avaliacoes WHERE avaliacoes.id IN ($idava) ORDER BY id ASC";
                        $result = mysql_query($query);

                        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                            $sql = "SELECT nota
                            FROM alunos_notas, medias
                            WHERE idmedia = medias.id
                            AND medias.idgrade = $gradeId
                            AND medias.idperiodo = $periodoId
                            AND idavaliacao = $idava ";

                            $result2 = mysql_query($sql);

                            $total_notas = 0;
                            $media = 0;

                            while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
                                $total_notas = (float) $total_notas + (float) $row2['nota'];

                                if ($row2['nota'] != "") {
                                    $media++;
                                }

                                $notav = ($row2['nota'] >= 0  && $row2['nota'] <= 30 ) ? $notav + 1 : $notav;
                                $notal = ($row2['nota'] > 30 && $row2['nota'] <= 50 ) ? $notal + 1 : $notal;
                                $notaa = ($row2['nota'] > 50 && $row2['nota'] < 65 ) ? $notaa + 1 : $notaa;
                                $notab = ($row2['nota'] >= 65 ) ? $notab + 1 : $notab;
                            }

                            echo '<td>' . "" . round($total_notas / max($media, 1), 2) . '</td>';
                        } ?>
                    <?php endforeach ?>
                </tr>

                <tr>
                    <td></td>
                    <td colspan="<?=is_countable($avaliacoes) ? count($avaliacoes) : 0?>" style='text-align:right;padding-right:30px;'>
                        <strong>Quantidade de notas maiores ou igual a 65:</strong>
                    </td>
                    <td style='background-color:#FFFFFF;'>
                        <?=$notab?>
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td colspan="<?=is_countable($avaliacoes) ? count($avaliacoes) : 0?>" style='text-align:right;padding-right:30px;'>
                        <strong>Quantidade de notas entre 51 e 65:</strong>
                    </td>
                    <td style='background-color:#f0ad4e;'>
                        <?=$notaa?>
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td colspan="<?=is_countable($avaliacoes) ? count($avaliacoes) : 0?>" style='text-align:right;padding-right:30px;'>
                        <strong>Quantidade de notas entre 31 e 50:</strong>
                    </td>
                    <td style='background-color:#F18548;'>
                        <?=$notal?>
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td colspan="<?=is_countable($avaliacoes) ? count($avaliacoes) : 0?>" style='text-align:right;padding-right:30px;'>
                        <strong>Quantidade de notas menores ou igual 30:</strong>
                    </td>
                    <td style='background-color:#d9534f;'>
                        <?=$notav?>
                    </td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>

    <?php if (!$multiplasAvaliacoes) : ?>
    <div class="mt-3 print:hidden">
        <input
            type="button"
            name="salvanota"
            id="salvanota"
            disabled="disabled"
            value="Salvar Notas"
            class="sw-btn sw-btn-primary"
        />
    </div>
    <?php endif ?>
</div>

<script>
$("#salvanota").click(function () {
    $.ajax({
        url: "dao/notas.php",
        type: "POST",
        data: $("#formsalvanota").serialize(),
        beforeSend: bloqueiaUI,
        complete: $.unblockUI,
        success: function () {
            criaAlerta('success', 'Notas salvas');
            buscaNotas(true);
        },
        error: function () {
            criaAlerta('error', 'Ocorreu um erro ao tentar salvar as notas. Tente novamente');
        }
    });
});
</script>
