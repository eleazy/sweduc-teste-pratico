<?php

use App\Model\Core\Empresa;
use App\Model\Financeiro\Titulo;

require_once __DIR__ . '/../../../../public/function/ultilidades.func.php';
require_once __DIR__ . '/../../../../public/permissoes.php';

$anoAtual = date('Y');

?>
<style type="text/css">
    .window{
        display:none;
        width:900px;
        height:500px;
        position:absolute;
        left:0;
        top:0;
        background:#FFF;
        z-index:9900;
        padding:10px;
        border-radius:10px;
    }

    #mascara{
        position:absolute;
        left:0;
        top:0;
        z-index:9000;
        background-color:#000;
        display:none;
    }

    .fechar{display:block; text-align:right;}

    .capitalize-first::first-letter {
        text-transform: capitalize;
    }

    .ui-autocomplete {
        z-index:1055 !important;
    }
</style>
<div id="dialog-apagaAluno" title="Apagar TODAS informações ?" style="display:none;">
    <span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>
    Somente alunos NÃO-MATRICULADOS e sem NENHUMA ficha financeira serão APAGADOS.<br />
    <!-- Serão apagadas TODAS AS INFORMAÇÕES DESTES ALUNOS, incluindo: Matrículas(todas), Ficha financeira, Faltas, Responsáveis (se apenas deste aluno), emails, telefones e usuário do sistema.<br /> -->
    Confirma?
</div>

<form id="geraDocs" action="documentos_alunos.php" method="post" target="_blank" >
    <input type="hidden" name="nomefuncDOC" id="nomefuncDOC" value="" />
    <input type="hidden" name="pagina" id="pagina" value="1" />
    <input type="hidden" name="idalunoDOC" id="idalunoDOC" value="" />
    <input type="hidden" name="idDOC" id="idDOC" value="" />
    <input type="hidden" name="idfuncionarioDOC" id="idfuncionarioDOC" value="" />
    <input type="hidden" name="idanoletivo" id="idanoletivoDOC" value="" />
    <input type="hidden" name="idpermissoes" id="idpermissoes" value="<?= $idpermissoes ?>" />
</form>

<form id="gerar_documento_v2" action="/documento-academico" method="post" target="_blank" >
    <input type="hidden" name="matriculaId" id="docs_v2_matriculaId" value="" />
    <input type="hidden" name="documentoId" id="docs_v2_documentoId" value="" />
</form>

<form name="reemiteboletoform" id="reemiteboletoform" target="_blank" method="POST" action="lib/boletos/boletos.php">
    <input type="hidden" name="idlancamento" id="boleto-reemissao" value="" />
</form>

<form id="geraDocsOnline" action="relatorios_pais.php" method="get" target="_blank" >
    <input type="hidden" name="pagina" id="pag-weight: 300;
    fontinaOnline" value="1" />
    <input type="hidden" name="idalunoDOC" id="idalunoDOCOnline" value="" />
    <input type="hidden" name="idDOC" id="idDOCOnline" value="" />
    <input type="hidden" name="crc" id="crcOnline" value="" />
    <input type="hidden" name="idpermissoes" id="idpermissoes" value="<?= $idpermissoes ?>" />
</form>

<form id="etiqueta" action="etiqueta.php" method="post" target="_blank" >
    <input type="hidden" name="etiquetaIds" id="etiquetaIds" value="1" />
    <input type="hidden" name="etiquetaTurma" id="etiquetaTurma" value="1" />
</form>

<?php $this->insert('Modal/Rematricula') ?>
<?php $this->insert('Modal/EmailEmMassa') ?>
<?php $this->insert('Modal/EmailAluno') ?>
<?php $this->insert('Modal/EnviarMensagemAluno') ?>
<?php $this->insert('Modal/TrocaEtapa') ?>
<?php $this->insert('Modal/ContatoAluno') ?>
<?php $this->insert('Modal/Ocorrencias') ?>
<?php $this->insert('Modal/Avaliacoes') ?>
<?php $this->insert('Modal/Medias') ?>
<?php $this->insert('Academico/Aluno/ModalGerarDocumento') ?>

<div>
    <div id="content_Alerta"></div>

    <h3>
        Alunos
        <?php if ($_ENV['CLIENTE'] == 'grupoalfacem') : ?>
            <a href="#janela1" data-toggle="tooltip" data-placement="right" title="Vídeo explicativo de como prosseguir com a rematrícula" rel="modal"><img src="images/video1.png" title="Ajuda" border="0" /></a>
            <div class="window" id="janela1">
                <a href="#" class="fechar"><img src="images/fechar.png" /></a>
                <center>
                    <video width="853" height="480" controls>
                        <source src="videos/matricula-secretaria.mp4" type="video/mp4">
                        Seu browser nao tem suporte para rodar video
                    </video>
                </center>
            </div>
            <div id="mascara"></div>
        <?php endif ?>
    </h3>

    <div
        id="barra-de-acoes"
        class="flex flex-wrap items-end overflow-hidden bg-gray-200 border rounded print:hidden justify-stretch"
    >
        <div class="w-full p-2 sm:w-1/2 md:w-1/4 lg:w-2/6">
            <label for="">Ação</label>

            <input name="oqfazer" id="oqfazer" class="form-element hidden" value="-1"/>
            <input id="oqfazerValue" class="form-element hidden" value="-1"/>
            <input
                id="oqfazerDisplay"
                class="form-element"
                value="Com os Selecionados..."
                onclick="openDocumentosModal()"
            />
        </div>

        <div class="w-full p-2 sm:w-1/2 md:w-1/4 lg:w-1/6">
            <label>
                Página inicial
            </label>

            <input
                type="text"
                class="form-element"
                id="paginaMenu"
                name="paginaMenu"
                value="1"
            >
        </div>

        <div class="w-full p-2 sm:w-1/2 md:w-1/4 lg:w-1/6">
            <label for="">
                Modo
            </label>

            <select name="modogerar" id="modogerar" class="form-element">
                <option value="0">Documento único</option>
                <option value="1">Documento por aluno</option>
            </select>
        </div>

        <div class="flex flex-wrap w-full p-2 -m-2 sm:w-1/2 md:w-1/4 lg:w-2/6">
            <div class="p-2 mt-auto">
                <button
                    type="button"
                    class="sw-btn sw-btn-primary"
                    onclick="executaoqfazer($('#oqfazer').val());"
                >
                    <i class="fa fa-desktop"></i>
                    Gerar na Tela
                </button>
            </div>

            <?php if ($permitirEnviarEmails) : ?>
                <div class="p-2 mt-auto">
                    <button
                        id="acao-enviar-email"
                        style="display:none;"
                        type="button"
                        class="sw-btn sw-btn-secondary"
                        onclick="executaAcao('enviar-email', $('#oqfazer').val());"
                    >
                        <i class="fas fa-envelope"></i>
                        Enviar por e-mail
                    </button>
                </div>
            <?php endif ?>
            <?php
            if ($permitirEnviarEmails) : ?>
                <div class="p-2 mt-auto">
                    <button
                        id="acao-enviar-mensagem"
                        style="display:none;"
                        type="button"
                        class="sw-btn sw-btn-secondary"
                        onclick="executaEnviarMensagem('mensagens-multi', $('#oqfazer').val());"
                    >
                        <i class="fas fa-envelope"></i>
                        Enviar por e-mail
                    </button>
                </div>
            <?php endif ?>
        </div>
    </div>

    <div style="border-top:1px solid #000;padding:10px;display:none;" id="cabecalhoPrint" >
        <h1><center><span id="qtosalunos"></span> ALUNOS<br /> EMPRESA: <span id="empresaprint"></span></center><br />
            <table style="width:100%;" id="itensCabecalho"></table></h1>
    </div>
</div>

<div>
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php
                $porPagina = 25;
                $totalpaginas = 1 + (floor($cntTotal / $porPagina));
                $paginaatual = ($limiteinf / $porPagina) + 1;
                $ultimapagina = $paginaatual + 5;
                $paginadepois = $ultimapagina + 1;

            if ($paginaatual > 11) {
                $primeirapagina = $paginaatual - 5;
                $paginaantes = $primeirapagina - 1;
                echo "<li class='page-item'><a class='page-link' onClick='mudaPagina(1)'>1</a></li>";
                if ($paginaantes > 0) {
                    echo "<li class='page-item'><a class='page-link' onClick='mudaPagina($paginaantes)'>...</a></li>";
                }
            } else {
                $primeirapagina = 1;
            }

            if ($ultimapagina > $totalpaginas) {
                $ultimapagina = $totalpaginas;
            }
            for ($i = $primeirapagina; $i < $ultimapagina + 1; $i++) {
                if ($i == $paginaatual) {
                    echo "<li class='page-item active'><a class='page-link' href='#'>" . $i . "</a></li>";
                } else {
                    echo "<li class='page-item'><a onClick='mudaPagina($i)' href='#'>" . $i . "</a></li>";
                }
            }

            if ($ultimapagina < $totalpaginas) {
                if ($paginadepois < $totalpaginas) {
                    echo "<li class='page-item'><a class='page-link' onClick='mudaPagina($paginadepois)'>...</a></li>";
                }

                echo "<li class='page-item'><a class='page-link' href='#' onClick='mudaPagina($totalpaginas)' >$totalpaginas</a></li>";
            }
            ?>

        </ul>
        <div id="exibindopaginacao" style="margin-bottom: 10px;"></div>
    </nav>

    <form id="mainform">
        <table style="width: 100%;" class="new-table table-striped" id="lista-alunos">
            <tr>
                <th class="table-header-repeat line-left-2" width="10px">
                    <input type="checkbox" name="todas" id="todas" value="0" />
                    <label for="todas"><span></span></label>
                </th>
                <th class="table-header-repeat line-left-2 minwidth-1"><b>Nome / Nascimento / Num.Aluno</b></th>
                <th class="table-header-repeat line-left-2 minwidth-1"><b>Ano Letivo / Matrícula</b></th>
                <th class="table-header-repeat line-left-2"><b>Unidade / Curso</b></th>
                <th class="table-header-repeat line-left-2"><b>Série</b></th>
                <th class="table-header-repeat line-left-2"><b>Turma</b></th>
            </tr>
            <?php
            $cnt = 0;
            while ($row = mysql_fetch_array($alunosResult, MYSQL_ASSOC)) {
                Titulo::patchMatriculaId($row['as_id']);

                $rematricula_evento_financeiro_id = Empresa::find($row['empid'])->rematricula_evento_financeiro_id;

                $row['rematriculaHabilitada'] = Titulo::where('idaluno', $row['as_id'])
                    ->where('matricula_id', $row['matricula_id'])
                    ->where('situacao', 0)
                    ->whereHas('itens', function ($query) use ($rematricula_evento_financeiro_id) {
                        $query->where('codigo', $rematricula_evento_financeiro_id);
                        $query->where('parcela', 1);
                    })
                    ->count();

                $cnt++;
                ?>
                <tr id="linha<?= $cnt; ?>">
                    <td>
                        <?php if ($idanoletivo == -2) { ?>
                            <input
                                type="checkbox"
                                class="group1"
                                name="ids[]"
                                id="ids<?= $cnt ?>"
                                value="<?= $row['nome'] ?>"
                            />
                        <?php } else { ?>
                            <input
                                type="checkbox"
                                class="group1"
                                name="ids[]"
                                id="ids<?= $cnt ?>"
                                data-matricula-id=<?=$row['matricula_id']?>
                                value="<?= $row['uid'] ?>@<?= $row['as_id'] ?>@<?= $row['nummatricula'] ?>@<?= $row['pid'] ?>@<?= $row['empid'] ?>"
                            />
                        <?php } ?>
                        <label for="ids<?= $cnt ?>"><span></span></label>
                    </td>
                    <td>
                        <?php
                        echo $row['nome'] . "<br />" . $row['dtnasc'] . "<br />" . $row['numeroaluno'];
                        $queryFin = "SELECT COUNT(*) as cnt FROM
                                            alunos_fichafinanceira ff
                                                INNER JOIN
                                            alunos_matriculas am ON ff.nummatricula = am.nummatricula
                                                INNER JOIN
                                            financeiro_situacaotitulos ON situacao = situacaonumero
                                        WHERE
                                            (situacao = 0 OR exibir_contagem = 1)
                                            AND datavencimento < CURDATE()
                                            AND ff.idaluno = " . $row['as_id'] . "
                                            AND am.idaluno = " . $row['as_id'];

                        $resultFin = mysql_query($queryFin);
                        $rowFin = mysql_fetch_array($resultFin, MYSQL_ASSOC);
                        if ($rowFin['cnt'] > 0 && $usuario->autorizado('financeiro-contas-a-receber-consultar')) {
                            echo '<br /><br /><span class="circulo bgred" style="color:#fff;" data-toggle="tooltip" data-placement="right" title="Total de títulos vencidos">' . $rowFin['cnt'] . '</span>';
                        }
                        $queryFin2 = "SELECT
                                            COUNT(cd.id) AS quantcd
                                        FROM
                                            alunos_fichafinanceira af
                                                INNER JOIN
                                            cheque_devolvido cd ON af.id = cd.id_fichafinanceira
                                        WHERE
                                                datavencimento < CURDATE()
                                                AND idaluno = " . $row['as_id'];

                        $resultFin2 = mysql_query($queryFin2);
                        $rowFin2 = mysql_fetch_array($resultFin2, MYSQL_ASSOC);
                        if (!empty($rowFin['quantcd']) && $rowFin['quantcd'] > 0) {
                            echo '<span class="circulo bgroxo" style="color:#fff;">' . $rowFin['quantcd'] . '</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($row['turmamatricula'] != "-1") : ?>
                            <?=$row['anoletivo']?><br>
                            <?=($row['status'] > 1) ? "<span style='color:#f00;'>" . $row['nome_status'] . " em " . $row['dtstatus'] . "</span><br>" : "" ?>
                            <?=$row['nummatricula']?>
                        <?php endif ?>
                    </td>
                    <td><?php if ($row['turmamatricula'] != "-1") {
                        echo $row['unidade'] . "<br />" . $row['curso'];
                        } ?></td>
                    <td><?php if ($row['turmamatricula'] != "-1") {
                        echo $row['serie'];
                        } ?></td>
                    <td>
                        <?php if ($row['turmamatricula'] != "-1") : ?>
                            <div><strong><?=$row['turma'];?></strong> (<?=$row['turno'];?>)</div>
                            <br>
                            <?php if ($row['iniciada_em'] && $row['iniciada_em'] != "00/00/0000") :?>
                                Iniciada em <?=$row['iniciada_em']?><br>
                            <?php endif?>
                            <?php if ($row['adiada_para_'] && $row['adiada_para_'] != "00/00/0000") :?>
                                2ª início em <?=$row['adiada_para_']?><br>
                            <?php endif?>
                            <?php if ($row['encerrada_em'] && $row['encerrada_em'] != "00/00/0000") :?>
                                Encerrada em <?=$row['encerrada_em']?><br>
                            <?php endif?>
                            <div class="capitalize-first">
                                <?=naturalizaDiasDaSemana($row['dias_da_semana'])?>
                                <?php if ($row['entrada'] && $row['saida']) :?>
                                    de <?=$row['entrada']?> às <?=$row['saida']?>
                                <?php endif?>
                            </div>
                        <?php endif ?>
                    </td>
                </tr>

                <tr>
                    <td class="noPrint" colspan="6">
                        <?php $this->insert('Academico/Aluno/AcoesAluno', get_defined_vars()) ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </form>

    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php
                $porPagina = 25;
                $totalpaginas = 1 + (floor($cntTotal / $porPagina));
                $paginaatual = ($limiteinf / $porPagina) + 1;
                $ultimapagina = $paginaatual + 5;
                $paginadepois = $ultimapagina + 1;

            if ($paginaatual > 11) {
                $primeirapagina = $paginaatual - 5;
                $paginaantes = $primeirapagina - 1;

                echo "<li class='page-item'><a class='page-link' onClick='mudaPagina(1)'>1</a></li>";
                if ($paginaantes > 0) {
                    echo "<li class='page-item'><a class='page-link' onClick='mudaPagina($paginaantes)'>...</a></li>";
                }
            } else {
                $primeirapagina = 1;
            }

            if ($ultimapagina > $totalpaginas) {
                $ultimapagina = $totalpaginas;
            }

            for ($i = $primeirapagina; $i < $ultimapagina + 1; $i++) {
                if ($i == $paginaatual) {
                    echo "<li class='page-item active'><a class='page-link' href='#'>" . $i . "</a></li>";
                } else {
                    echo "<li class='page-item'><a onClick='mudaPagina($i)' href='#'>" . $i . "</a></li>";
                }
            }

            if ($ultimapagina < $totalpaginas) {
                if ($paginadepois < $totalpaginas) {
                    echo "<li class='page-item'><a class='page-link' onClick='mudaPagina($paginadepois)'>...</a></li>";
                }

                echo "<li class='page-item'><a class='page-link' href='#' onClick='mudaPagina($totalpaginas)' >$totalpaginas</a></li>";
            }
            ?>
        </ul>
        <div id="exibindopaginacao2"></div>
    </nav>
</div>

<script type="text/javascript">
$("#empresaprint").append($("#idunidade option:selected").text());

$("#itensCabecalho").append("<tr><td style='padding:5px;border:1px solid #000;'>ANO LETIVO:</td><td style='padding:5px;border:1px solid #000;'>" + $("#idanoletivo option:selected").text() + "</td></tr>");

$("#itensCabecalho").append("<tr><td style='padding:5px;border:1px solid #000;'>CURSO:</td><td style='padding:5px;border:1px solid #000;'>" + $("#curso option:selected").text() + "</td></tr>");

$("#itensCabecalho").append("<tr><td style='padding:5px;border:1px solid #000;'>SÉRIE:</td><td style='padding:5px;border:1px solid #000;'>" + $("#serie option:selected").text() + "</td></tr>");

$("#itensCabecalho").append("<tr><td style='padding:5px;border:1px solid #000;'>TURMA:</td><td style='padding:5px;border:1px solid #000;'>" + $("#turma option:selected").text() + "</td></tr>");

if ($("#nome").val() != "")
    $("#itensCabecalho").append("<tr><td style='padding:5px;border:1px solid #000;'>NOME:</td><td style='padding:5px;border:1px solid #000;'>" + $("#nomeiniciacontem option:selected").text() + " " + $("#nome").val() + "</td></tr>");

if ($("#codigo").val() != "")
    $("#itensCabecalho").append("<tr><td style='padding:5px;border:1px solid #000;'>Nº ALUNO:</td><td style='padding:5px;border:1px solid #000;'>" + $("#codigo option:selected").text() + "</td></tr>");

$("#itensCabecalho").append("<tr><td style='padding:5px;border:1px solid #000;'>MÊS DE NASCIMENTO:</td><td style='padding:5px;border:1px solid #000;'>" + $("#mesnascimento option:selected").text() + "</td></tr>");

$("#itensCabecalho").append("</table>");

$('#loader').hide();
var index = -1;
$("#lista-alunos tbody tr").each(function ( ) {
    $(this).css("background-color", (index < 2) ? '#f9f9f9' : '#FFF');
    index++;
    if (index == 4)
        index = 0;
});

$(document).ready(function() {
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
});

var conteudoBuscaParam = <?php echo  json_encode($_POST, JSON_THROW_ON_ERROR)?>;
var conteudoBuscaPag = 'alunos_busca.php';
$.mask.definitions['#'] = '[0123]';
$.mask.definitions['@'] = '[01]';
$.mask.definitions['&'] = '[12]';
$.mask.definitions['$'] = '[012]';
$.mask.definitions['%'] = '[012345]';
$('.date').mask("#9/@9/&999", {placeholder: 'dd/mm/yyyy'}).datepicker({
    changeMonth: true,
    changeYear: true,
    dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
    monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
    dateFormat: "dd/mm/yy"});

if (<?= $limitesuperior ?> == 0)
    var texto = "Exibindo 0 a 0 de 0 alunos.";
else
    var texto = "Exibindo <?= $limiteinf + 1 ?> a <?= $limitesuperior ?> de <?= $cntTotal ?> alunos.";
$("#qtosalunos").html(texto);
$("#exibindopaginacao").html(texto);
$("#exibindopaginacao2").html(texto);

$(".numeros").keyup(function () {
    if (this.value == "0")
        this.value = "1";
    this.value = this.value.replace(/[^0-9\.]/g, '');
});

function setEmitenf(id, valor) {
    if (valor)
        valor = 1;
    else
        valor = 0;
    $.ajax({
        url: "dao/alunos.php",
        data: {action: "setEmitenf", id: id, valor: valor},
        type: 'POST',
        context: jQuery('#conteudo'),
        success: function (data) {
            var resposta = data.split("|");
            if (resposta[0] == 'green') {
                criaAlerta('success', 'Situação de emissão de NFe atualizada com sucesso');
            } else {
                criaAlerta('error', 'Erro ao atualizar a situação de emissão de NFe aluno');
            }
        }
    });
}

$('#bt-envia-email').on('click', function () {
    var idaluno = $("#dialog-email-aluno").data("idaluno");
    var idpessoa = $("#dialog-email-aluno").data("idpessoa");
    var alunoMail = ($('#alunoMail').is(':checked') ? 1 : 0);
    var paiMail = ($('#paiMail').is(':checked') ? 1 : 0);
    var maeMail = ($('#maeMail').is(':checked') ? 2 : 0);
    var respfinMail = ($('#respfinMail').is(':checked') ? 1 : 0);
    var resppedagMail = ($('#resppedagMail').is(':checked') ? 1 : 0);

    $.ajax({
        url: "dao/alunos.php",
        data: {action: "getEmails", idaluno: idaluno, idpessoa: idpessoa, alunoMail: alunoMail, paiMail: paiMail, maeMail: maeMail, respfinMail: respfinMail, resppedagMail: resppedagMail},
        type: 'POST',
        context: jQuery('#conteudo'),
        success: function (data) {
            window.open("mailto:?bcc=" + data);
        }
    });
});

function ffin(nummatricula, idaluno, idpessoa, idunidade, turmamatricula, idmatricula, experimental = false) {
    $.ajax({
        url: "alunos_fichafin.php",
        type: 'POST',
        context: jQuery('#conteudoBusca'),
        data: {
            idaluno: idaluno,
            idpessoa: idpessoa,
            idmatricula: idmatricula,
            nummatricula: nummatricula,
            idfuncionariounidade: $("#idfuncionariounidade").val(),
            idfuncionario: $("#idfuncionario").val(),
            idpessoalogin: $("#idpessoalogin").val(),
            idpermissoes: $("#idpermissoes").val(),
            idunidade: idunidade,
            turmamatricula: turmamatricula,
            experimental: experimental,
            situacaoFiltro: "",
        },
        beforeSend: function () {
            $.blockUI({
                message: $('#displayBox'),
                css: {
                    top: ($(window).height() - 400) / 2 + 'px',
                    left: ($(window).width() - 400) / 2 + 'px',
                    width: '400px'
                }
            });
        },
        complete: function () {
            $.unblockUI();
        },
        success: function (data) {
            this.html(data);
        }
    });
}

function entrevistas(matriculaId) {
    sweduc.carregarUrl(
        'alunos_cadastra.php' +
        '?matriculaId=' + matriculaId +
        '&aba=but_entrevistas' +
        '&modo=ababloqueio'
    );
}

// ABRE DIÁLOGO PARA O PAI ESCOLHER DATAS INICIAIS E FINAIS DAS OCORRÊNCIAS
function ocorrencias(idaluno, nomedoaluno, idunidade, anoletivo) {
    // $('#dialog-busca-ocorrencias').data('idaluno', idaluno).data('nomedoaluno', nomedoaluno).data('idunidade', idunidade).data('anoletivo',anoletivo).modal('toggle');
    $('#dialog-busca-ocorrencias').data('idaluno', idaluno).data('nomedoaluno', nomedoaluno).data('idunidade', idunidade).data('anoletivo', <?=$idanoletivo?>).modal('toggle');
}

$('#bt-lista-ocorrencias').on('click', function () {
    var idaluno = $('#dialog-busca-ocorrencias').data("idaluno");
    var idalunoArr = $('#dialog-busca-ocorrencias').data("idalunoArr");
    var nomealuno = $('#dialog-busca-ocorrencias').data("nomedoaluno");
    var idunidade = $('#dialog-busca-ocorrencias').data("idunidade");
    var nomedaturma = $('#dialog-busca-ocorrencias').data("nomedaturma");
    var anoletivo = $('#dialog-busca-ocorrencias').data("anoletivo");
    var datadeOcorrencias = $("#datadeOcorrencias").val();
    var dataateOcorrencias = $("#dataateOcorrencias").val();

    $.ajax({
        url: "alunos_ocorrencias_lista.php",
        type: 'POST',
        context: jQuery('#conteudoBusca'),
        data: {contexto: "conteudoBusca", idaluno: idaluno, tipobusca: "0", idalunoArr: idalunoArr, nomealuno: nomealuno, nomedaturma: nomedaturma, idunidade: idunidade, periodode: datadeOcorrencias, periodoate: dataateOcorrencias, idpermissoes: $("#idpermissoes").val(), idpessoalogin: $("#idpessoalogin").val(), idanoletivo:<?=$idanoletivo?>},
        beforeSend: function () {
            $.blockUI({
                message: $('#displayBox'),
                css: {
                    top: ($(window).height() - 400) / 2 + 'px',
                    left: ($(window).width() - 400) / 2 + 'px',
                    width: '400px'
                }
            });
        },
        complete: function () {
            $.unblockUI();
        },
        success: function (data) {
            this.html(data);
            $('#dialog-busca-ocorrencias').modal('toggle');
            $('[data-toggle="tooltip"]').tooltip();
            //$('html, body').animate({scrollTop: $("#conteudoBusca").offset().top}, 'slow');
        }
    });
});

function protocolo(idaluno, nomealuno, unidade, idunidade) {
    $.ajax({
        url: "alunos_protocolo.php",
        type: 'POST',
        context: jQuery('#conteudo'),
        data: {idaluno: idaluno, nomealuno: nomealuno, unidade: unidade, idunidade: idunidade, idfuncionariounidade: $("#idfuncionariounidade").val(), idpessoalogin: $("#idpessoalogin").val(), idpermissoes: $("#idpermissoes").val()},
        beforeSend: function () {
            $.blockUI({
                message: $('#displayBox'),
                css: {
                    top: ($(window).height() - 400) / 2 + 'px',
                    left: ($(window).width() - 400) / 2 + 'px',
                    width: '400px'
                }
            });
        },
        complete: function () {
            $.unblockUI();
        },
        success: function (data) {
            this.html(data);
        }
    });
}

function historico(idaluno) {
    $.ajax({
        url: "alunos_historico_cadastra.php",
        type: 'POST',
        context: jQuery('#conteudo'),
        data: {idfuncionariounidade: $("#idfuncionariounidade").val(), doc: '0', idaluno: idaluno, idpermissoes: $("#idpermissoes").val(), idpessoalogin: $("#idpessoalogin").val()},
        beforeSend: function () {
            $.blockUI({
                message: $('#displayBox'),
                css: {
                    top: ($(window).height() - 400) / 2 + 'px',
                    left: ($(window).width() - 400) / 2 + 'px',
                    width: '400px'
                }
            });
        },
        complete: function () {
            $.unblockUI();
        },
        success: function (data) {
            $('html, body').animate({scrollTop: 0}, 0);
            if ($('#conteudoLista').length) {
                $('#conteudoLista').html($('#conteudoBusca').html());
                this.html(data);
            } else
                this.html("<span id='conteudoLista' style='display:none;'>" + $('#conteudoBusca').html() + "</span>" + data);
        }
    });
}

function historicoAntigos(nomealuno) {
    var tmp = '';
    if ($('#conteudoBusca').html() != null)
        tmp = $('#conteudoBusca').html();
    else
        tmp = $('#conteudoLista').html();

    $.ajax({
        url: "alunos_historico_antigos.php",
        type: 'POST',
        context: jQuery('#conteudo'),
        data: {nomealuno: nomealuno, idfuncionariounidade: $("#idfuncionariounidade").val(), idpermissoes: $("#idpermissoes").val(), idpessoalogin: $("#idpessoalogin").val()},
        beforeSend: function () {
            $.blockUI({
                message: $('#displayBox'),
                css: {
                    top: ($(window).height() - 400) / 2 + 'px',
                    left: ($(window).width() - 400) / 2 + 'px',
                    width: '400px'
                }
            });
        },
        complete: function () {
            $.unblockUI();
        },
        success: function (data) {
            this.html(data);
            $('#conteudoLista').html(tmp);
            $('html, body').animate({scrollTop: 0}, 0);
        }
    });
}

function medias(idturma, idanoletivo, idaluno, nummatricula) {
    if ($('#conteudoBusca').html() != null)
        var tmp = $('#conteudoBusca').html();
    else
        var tmp = $('#conteudoLista').html();

    $.ajax({
        url: "alunos_medias.php",
        type: 'POST',
        data: {idturma: idturma, idanoletivo: idanoletivo, idaluno: idaluno, idgr: "0", nummatricula: nummatricula, idpermissoes: $("#idpermissoes").val(), idpessoalogin: $("#idpessoalogin").val()},
        beforeSend: function () {
            $.blockUI({
                message: $('#displayBox'),
                css: {
                    top: ($(window).height() - 400) / 2 + 'px',
                    left: ($(window).width() - 400) / 2 + 'px',
                    width: '400px'
                }
            });
        },
        complete: function () {
            $.unblockUI();
        },
        success: function (data) {
            $('#recebe-medias').empty().html(data);
            $('#dialog-medias').modal('toggle');
        }
    });
}

function edita(alunoId, matriculaId) {
    sweduc.carregarUrl(
        'alunos_cadastra.php' +
        '?alunoId=' + alunoId +
        '&matriculaId=' + matriculaId
    );
}

function view(nome, datanascimento, numeroaluno, unidade, curso, serie, turma, nummatricula, idaluno, idpessoa, status) {
    $.ajax({
        url: "alunos_view.php",
        type: 'POST',
        data: {nome: nome, datanascimento: datanascimento, numeroaluno: numeroaluno, unidade: unidade, curso: curso, serie: serie, turma: turma, nummatricula: nummatricula, idaluno: idaluno, idpessoa: idpessoa, status: status, idpessoalogin: $("#idpessoalogin").val(), idpermissoes: $("#idpermissoes").val()},
        beforeSend: function () {
            $.blockUI({
                message: $('#displayBox'),
                css: {
                    top: ($(window).height() - 400) / 2 + 'px',
                    left: ($(window).width() - 400) / 2 + 'px',
                    width: '400px'
                }
            });
        },
        complete: function () {
            $.unblockUI();
        },
        success: function (data) {
            $('#recebe-contato-aluno').empty().html(data);
            $("#dialog-contatos-aluno").modal('toggle');
        }
    });
}

function apaga(idaluno) {
    $("#dialog-apagaAluno").dialog({
        resizable: false,
        modal: true,
        buttons: {
            "APAGAR": function () {
                $.ajax({
                    url: "dao/alunos.php",
                    type: 'POST',
                    context: jQuery('#conteudo'),
                    data: {action: "apagar", idaluno: idaluno},
                    beforeSend: function () {
                        $.blockUI({
                            message: $('#displayBox'),
                            css: {
                                top: ($(window).height() - 400) / 2 + 'px',
                                left: ($(window).width() - 400) / 2 + 'px',
                                width: '400px'
                            }
                        });
                    },
                    complete: function () {
                        $.unblockUI();
                    },
                    success: function (data) {
                        $("#busca").click();
                    }
                });
                $(this).dialog("close");
            },
            "CANCELAR": function () {
                $(this).dialog("close");
            }
        }
    });
}

function novolancamentomultiplo(dadosalunos, idalunosM, rematricula = false) {
    if (rematricula) {
        Swal.fire({
            title: 'Escolha a quantidade tolerada de títulos em atraso',
            input: 'number',
            inputLabel: 'Selecione:',
            inputAttributes: {
                min: 0
            },
            inputValue: 1,
            showCancelButton: true,
            confirmButtonText: 'Enviar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'sw-btn sw-btn-primary',
                cancelButton: 'sw-btn sw-btn-danger'
            },

            preConfirm: (toleranciaAtraso) => {
                if (toleranciaAtraso === '' || toleranciaAtraso < 0) {
                    Swal.showValidationMessage('Por favor, insira uma quantidade válida!');
                } else {
                    return toleranciaAtraso;
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let tolerancia = result.value;

                $.ajax({
                    url: "alunos_lancamentocoletivo.php",
                    type: 'POST',
                    context: jQuery('#conteudo'),
                    data: {
                        dadosalunos: dadosalunos,
                        idalunosM: idalunosM,
                        idpessoalogin: $("#idpessoalogin").val(),
                        idfuncionario: $("#idfuncionariounidade").val(),
                        rematricula: rematricula,
                        toleranciaAtraso: tolerancia
                    },
                    beforeSend: bloqueiaUI,
                    complete: $.unblockUI,
                    success: function (data) {
                        this.html(data);
                    }
                });
            }
        });
    } else {
        $.ajax({
            url: "alunos_lancamentocoletivo.php",
            type: 'POST',
            context: jQuery('#conteudo'),
            data: {
                dadosalunos: dadosalunos,
                idalunosM: idalunosM,
                idpessoalogin: $("#idpessoalogin").val(),
                idfuncionario: $("#idfuncionariounidade").val(),
                rematricula: rematricula,
            },
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function (data) {
                this.html(data);
            }
        });
    }
}

function executaoqfazer(selecionado) {
    var ids = $('.group1:checkbox:checked').map(function () {
        return this.value;
    }).get().join();

    var idsM = $('.group1:checkbox:checked').map(function () {
        return this.value.split("@")[1];
    }).get().join();

    var idp = $('.group1:checkbox:checked').map(function () {
        return this.value.split("@")[3];
    }).get().join();

    var matriculasIds = $('.group1:checkbox:checked').map(function () {
        return this.dataset.matriculaId;
    }).get().join();

    switch (selecionado) {
        case "-1":
            swal("Atenção", "Escolha uma ação primeiro", "warning");
            document.getElementById('modal-gerar-documento-aluno').classList.toggle('hidden');
            break;
        case "excluir":
            apaga(idsM);
            break;
        case "lancamento-mult":
            novolancamentomultiplo(ids, idsM);
            break;
        case "emails-mult":
            $('input:checkbox').removeAttr('checked');
            $('#dialog-email-aluno').data('idaluno', idsM).data('idpessoa', idp).modal('toggle');
            break;
        case "ocorrencias":
            if ($("#turma option:selected").val() == "todos")
                swal("Atenção", "É necessário selecionar uma turma na busca.", "warning");
            else {
                $('input:checkbox').removeAttr('checked');
                $('#dialog-busca-ocorrencias')
                    .data('idaluno', 'TURMA')
                    .data('nomedoaluno', '')
                    .data('idunidade', $("#idunidade option:selected").text())
                    .data('nomedaturma', $("#turma option:selected").text())
                    .data('idalunoArr', idsM)
                    .modal('toggle');
            }
            break;
        case "etiqueta":
            $("#etiquetaIds").val(idsM);
            $("#etiquetaTurma").val($("#turma option:selected").val());
            $('#etiqueta').submit();
            break;
        case "10":
            if (ids == "")
                swal("Atenção", "Selecione ao menos 1 aluno.", "warning");
            else {
                if ($("#modogerar :selected").val() == "0") {
                    $("#nomefuncDOC").val($("#nomefuncionario").val());
                    $("#idanoletivoDOC").val($("#idanoletivo").val());
                    $("#pagina").val($("#paginaMenu").val());
                    $("#idalunoDOC").val(ids);
                    $("#idDOC").val($("#oqfazerValue").val());
                    $("#geraDocs").submit();
                } else {
                    var idd = ids.split(",");
                    var arrayLength = idd.length;
                    for (var i = 0; i < arrayLength; i++) {
                        $("#nomefuncDOC").val($("#nomefuncionario").val());
                        $("#idanoletivoDOC").val($("#idanoletivo").val());
                        $("#pagina").val($("#paginaMenu").val());
                        $("#idalunoDOC").val(idd[i]);
                        $("#idDOC").val($("#oqfazerValue").val());
                        document.getElementById("geraDocs").target = "window-" + i;
                        window.open("http://<?= $cliente ?>.managerlearn.com.br/documentos_alunos.php", "window-" + i);
                        document.getElementById("geraDocs").submit();
                    }
                }
            }
            break;
        case "11":
            if (matriculasIds == "")
                swal("Atenção", "Selecione ao menos 1 aluno.", "warning");
            else {
                if ($("#modogerar :selected").val() == "0") {
                    document.getElementById('docs_v2_matriculaId').value = matriculasIds;
                    document.getElementById('docs_v2_documentoId').value = $("#oqfazerValue").val();
                    document.getElementById("gerar_documento_v2").submit();
                } else {
                    matriculasIds.split(",").forEach(function (id) {
                        document.getElementById('docs_v2_matriculaId').value = id
                        document.getElementById('docs_v2_documentoId').value = $("#oqfazerValue").val()
                        document.getElementById("gerar_documento_v2").submit();
                    });
                }
            }
            break;
        case "trocar-turma":
            dialogTrocarTurma()
            break
        case "rematricula":
            novolancamentomultiplo(ids, idsM, true);
            break;
        case "mensagens-multi":
            //$('input:checkbox').removeAttr('checked');
            $('#dialog-enviar-mensagem-aluno').data('idaluno', idsM).data('idpessoa', idp).modal('toggle');
            //dialogEnviarMensagem();
            break;
    }
}

/**
 * @param {string} acao O tipo de ação do botão
 * @param opcao A opção selecionada pela pessoa no select
 */
function executaAcao(acao, opcao) {
    if (acao = 'enviar-email') {
        $('#dialog-email-massa').modal('show')
        $('#dialog-email-massa #acaoEmailAssunto').val($("#oqfazer").text())
    }
}


// FUNÇÃO enviarEmaisEmMassa() TALVEZ CÓDIGO LIXO
function enviarEmaisEmMassa() {
    // Formato do group1
    // $row['uid'] @ $row['as_id'] @ $row['nummatricula'] @ $row['pid'] @ $row['empid']
    var alunos = $('.group1:checkbox:checked').map(function () {
        return this.value
    }).get();

    //console.log('enviarMensagensEmMassa -> var alunos->' + alunos);

    var assunto = $('#email-em-massa-form #acaoEmailAssunto').val();
    var destinatarios = $('#email-em-massa-form :checked').map(function() {
        return this.name
    }).get();

    $.ajax({
        url: 'documentos_email.php',
        type: 'POST',
        data: {
            nomefuncDOC: $("#nomefuncionario").val(),
            alunos: alunos,
            idDOC: $("#oqfazerValue").val(),
            idanoletivo: $("#idanoletivo").val(),
            pagina: $("#paginaMenu").val(),
            assunto: assunto,
            destinatarios: destinatarios
        },
        success: function(data) {
            criaAlerta('success', 'Seus e-mails foram enviados')
        }
    });
}

//  CARREGA MENSAGENS PREDEFINIDAS QUANDO ABRE O MODAL DE ENVIO DE MENSAGENS
$(document).ready(function() {
    $('#div-campo-envia-para').hide();
    $("#div-formulario-campo").hide();
    $("#div-anexo-campo").hide();

    $.ajax({
        url: "dao/mensagensinstitucionais.php",
        type: "POST",
        data: {
            action: "carregarMensagensPredefinidas"
        },
        context: jQuery('#mensagem-predefinida'),
        success: function (data) {
            this.html('<option value="0" selected="selected">SEM PREDEFINIÇÃO</option>' +data);
        }
    })
});

//  CARREGA DADOS DA MENSAGEM PREDEFINIDA QUANDO SELECIONADO
$("#mensagem-predefinida").on('change', function() {
    if($('#mensagem-predefinida').val() == '0'){
        $('#assunto-campo').val('');
        $('#div-mensagem-campo').show();
        $('#div-assunto-campo').show();
    }else{
        //$('#assunto-campo').val('Carrega assunto');
        $.ajax({
            url: "dao/mensagensinstitucionais.php",
            type: "POST",
            data: {
                action: "carregarUmaMensagemPredefinida",
                idMensagemPredefinida: $('#mensagem-predefinida').val()
            },
            success: function (data) {
                var dadosAssuntoEMensagem = JSON.parse(data);
                $('#assunto-campo').val(dadosAssuntoEMensagem['assunto']);
                $('#mensagem-campo').val('');
                $('#div-mensagem-campo').hide();
                $('#div-assunto-campo').hide();
            }
        })
    }

});


//  MOSTRA OU ESCONDE O CAMPO DE ENVIO DE LINK PARA FORMULÁRIOS
$("#mostraCampoFormularioMensagem").on('change', function() {
    $("#div-formulario-campo").toggle();
});

//  ADICIONA UM CAMPO DE ANEXO DE ARQUIVO NA DIV #div-anexo-campo
$("#adicionar-anexo").on('click', function() {
    $("#div-anexo-campo").show();
    $("#div-anexo-campo").append('<input type="file" name="anexo[]" class="form-control"  onchange="checkFileSize(event)"/>');
});

function checkFileSize(event) {
    const fileInput = event.target;
    const file = fileInput.files[0];

    // Tamanho máximo permitido em bytes (100MB)
    const maxSize = 100000000;

    if (file && file.size > maxSize) {
        criaAlerta('error', 'O arquivo excede o tamanho máximo de 100MB.');
        fileInput.value = ''; // Limpa o campo de arquivo selecionado
    }
}


function mostrarnolog(item) {
    console.log(item.nome);
}

 onchange="checkFileSize(event)"

function enviarMensagens() {
    var alunos = alunosSelecionados();

    var idpessoalogin = $("#idpessoalogin").val();
    var idfuncionario = $("#idfuncionariounidade").val();

    var assunto = $('#dialog-enviar-mensagem-aluno #assunto-campo').val();
    var mensagem = $('#dialog-enviar-mensagem-aluno #mensagem-campo').val();


    // Recebe anexos
    var anexos = new FormData();
    var fileInputs = document.querySelectorAll('input[name="anexo[]"]');

    for (var i = 0; i < fileInputs.length; i++) {
        var fileInput = fileInputs[i];
        var totalFiles = fileInput.files.length;

        for (var j = 0; j < totalFiles; j++) {
        var file = fileInput.files[j];
        anexos.append('arquivo[]', file);
        }
    }


    var seEnviaMsgAluno = 0;
    var formularioUrl = null;

    //  VALIDA O CAMPO ASSUNTO
    if (assunto == ""){
            criaAlerta('error', 'O campo do assunto não pode estar em branco. Favor verificar.');
            return
    }

    //  VALIDA O CAMPO DO FORMULÁRIO
    if ($('#dialog-enviar-mensagem-aluno #mostraCampoFormularioMensagem').is(":checked")){

        var seEnviaFormulario = 1;
        var formularioUrl = $('#dialog-enviar-mensagem-aluno #formulario-campo').val();

        var verificaLinkGoogle1 = formularioUrl.slice(0, 34) === "https://docs.google.com/forms/d/e/";
        var verificaLinkGoogle2 = formularioUrl.slice(0, 14) === "https://forms.";

        if(!verificaLinkGoogle1 && !verificaLinkGoogle2) {
            criaAlerta('error', 'Link para o formulário com erro. Favor verificar.');
            return
        }

    }

    if ($('#dialog-enviar-mensagem-aluno #alunoMensagem').is(":checked") == true){
        var seEnviaMsgAluno = 1;
    }else{
        var seEnviaMsgAluno = 0;
    }

    if ($('#dialog-enviar-mensagem-aluno #alunoMensagem').is(":checked") == true){
        var seEnviaMsgRespFin = 1;
    }else{
        var seEnviaMsgRespFin = 0;
    }

    if ($('#dialog-enviar-mensagem-aluno #resppedagMensagem').is(":checked") == true){
        var seEnviaMsgRespPed = 1;
    }else{
        var seEnviaMsgRespPed = 0;
    }

    if (todosAlunosSelecionados()){
        var todosSelecionados = 1;
    }else{
        var todosSelecionados = 0;
    }

    var situacao = $('#mainform #situacao').val();
    var idunidadebusca = $('#mainform #idunidadebusca').val();
    var curso = $('#mainform #curso').val();
    var serie = $('#mainform #serie').val();
    var turma = $('#mainform #turma').val();
    var idanoletivo = $("#mainform #idanoletivo").val();

    if (situacao == "") situacao = "todos";

    if (todosSelecionados == 1) {
        alunos = null;
    }


    var json = {
            nomefuncDOC: $("#nomefuncionario").val(),
            alunos: alunos,
            idpessoalogin: idpessoalogin,
            idfuncionario: idfuncionario,
            idanoletivo: idanoletivo,
            situacao: situacao,
            idunidadebusca: idunidadebusca,
            curso: curso,
            serie: serie,
            turma: turma,
            seEnviaMsgAluno: seEnviaMsgAluno,
            seEnviaMsgRespFin: seEnviaMsgRespFin,
            seEnviaMsgRespPed: seEnviaMsgRespPed,
            assunto: assunto,
            mensagem: mensagem,
            seEnviaFormulario: seEnviaFormulario,
            formularioUrl: formularioUrl,
        };

    anexos.append('json', JSON.stringify(json));

    $.ajax({
        url: 'documentos_mensagens_salvar',
        type: 'POST',
        data: anexos,
        contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
        processData: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)

        success: function(data) {
            criaAlerta('success', 'Suas mensagens foram enviadas');
        },
        error: function(data) {
            if (data.status == 422) {
                criaAlerta('error', data.responseText);
                return
            }
            criaAlerta('error', 'Erro ao gravar no banco de dados.');
        },

    });


}

function relatoriosPorEmail(idn) {
    if (idn == 10) {
        var ids = $('.group1:checkbox:checked').map(function () {
            return this.value;
        }).get().join();
        var idd = ids.split(",");
        var arrayLength = idd.length;
        if (idd == "")
            swal("Atenção", "Selecione ao menos 1 aluno.", "warning");
        else {
            for (var i = 0; i < arrayLength; i++) {
                var urlcliente = "<?= $cliente ?>";
                var crccliente = urlcliente.split('').map(function (c) {
                    return 'abcdefghijklmnopqrstuvwxyz'.indexOf(c) + 1;
                }).join('');
                var crc = parseInt(parseFloat($("#paginaMenu").val()) * 879 + (parseFloat(idd[i]) * (parseFloat($("#paginaMenu").val()) + 5)) + parseFloat($("#oqfazerValue").val()) + parseInt(crccliente));
                $("#paginaOnline").val($("#paginaMenu").val());
                $("#idalunoDOCOnline").val(idd[i]);
                $("#idDOCOnline").val($("#oqfazerValue").val());
                $("#crcOnline").val(crc);
                document.getElementById("geraDocsOnline").target = "window-" + i;
                window.open("http://<?= $cliente ?>.managerlearn.com.br/relatorios_pais.php", "window-" + i);
                document.getElementById("geraDocsOnline").submit();
            }
        }
    } else
        swal("Atenção", "Escolha um documento.", "warning");
}

function verificaSeDocumentoEnviaEmail () {
    let documento = $('#oqfazer');

    if ((documento.val() == 2) && ($("#idunidadebusca").val() == "todos")) {
        swal("Atenção", "É necessário especificar uma unidade na busca", "warning");
        documento.val(0);
        $("#conteudoBusca").html("");
    }

    /**
     * FIXME: Remover limitação de enviar e-mail apenas para o documento de acesso dos pais
     *
     * Alterna visibilidade do botão de enviar e-mail
     * de acordo com a opção documento Acesso dos pais
     */
    var selected = document.getElementById('oqfazerDisplay').value;
    var enviaEmail = selected.includes("Acesso ")
    $("#acao-enviar-email").toggle(enviaEmail)
};

var porPagina = 25;
function mudaPagina(pag) {
    var novolimite = (pag - 1) * porPagina;
    document.getElementById('limiteinf').value = novolimite;
    document.getElementById('limitesup').value = novolimite + porPagina;
    buscaAlunos();
}

function geraboletim(ava, nome, ida, nummatricula, idanoletivo, idunidade) {
    if (ava == 0) {
        $.ajax({
            url: "alunos_boletim.php",
            type: "POST",
            context: jQuery('#dialog'),
            data: {mostrapontos: "0", mostraAvaliacao: ava, idaluno: ida, databoletim: "13", nummatricula: nummatricula, idanoletivo: idanoletivo, idunidade: idunidade, comfaltas: '0',pontosparaPF: "0"},
            beforeSend: function () {
                $.blockUI({
                    message: $('#displayBox'),
                    css: {
                        top: ($(window).height() - 400) / 2 + 'px',
                        left: ($(window).width() - 400) / 2 + 'px',
                        width: '400px'
                    }
                });
            },
            complete: function () {
                $.unblockUI();
            },
            success: function (data) {
                this.html(data);
                $("#dialog").dialog("open");
            }
        });
    } else {
        $.ajax({
            url: "alunos_boletim_avaliacoes.php",
            type: "POST",
            context: jQuery('#dialog'),
            data: {mostrapontos: "0", mostraAvaliacao: ava, idaluno: ida, databoletim: "<?= date('m') ?>", nummatricula: nummatricula, idanoletivo: idanoletivo, idunidade: idunidade, comfaltas: '0'},
            beforeSend: function () {
                $.blockUI({
                    message: $('#displayBox'),
                    css: {
                        top: ($(window).height() - 400) / 2 + 'px',
                        left: ($(window).width() - 400) / 2 + 'px',
                        width: '400px'
                    }
                });
            },
            complete: function () {
                $.unblockUI();
            },
            success: function (data) {
                this.html(data);
                $("#dialog").dialog("open");
            }
        });
    }
}

$('#todas').on('click', function () {
    $('input:checkbox.group1').not(this).prop('checked', this.checked);
});

function geraboletimnovo(ava, nome, ida, nummatricula, idanoletivo, idunidade) {
    $.ajax({
        url: "alunos_boletim_novo_aluno.php",
        type: "POST",
        data: {mostrapontos: "0", mostraAvaliacao: ava, idaluno: ida, databoletim: "<?= date('m') ?>", nummatricula: nummatricula, idanoletivo: idanoletivo, idunidade: idunidade, comfaltas: '0',pontosparaPF: "0", exibeCabecalho: "1"},
        beforeSend: function () {
            $.blockUI({
                message: $('#displayBox'),
                css: {
                    top: ($(window).height() - 400) / 2 + 'px',
                    left: ($(window).width() - 400) / 2 + 'px',
                    width: '600px'
                }
            });
        },
        complete: function () {
            $.unblockUI();
        },
        success: function (data) {
            $('#recebe-boletim-avaliacoes').empty().html(data);
            $("#dialog-boletim-avaliacoes").modal('toggle');
        }
    });
}

function imprimeBoletimNovo() {
    var conteudo = $('#recebe-boletim-avaliacoes').html();
    var win = window.open();
    win.document.write(conteudo);
    win.print();
    win.close();
}

$(document).ready(function () {
    $("a[rel=modal]").click(function (ev) {
        ev.preventDefault();
        var id = $(this).attr("href");
        var alturaTela = $(document).height();
        var larguraTela = $(window).width();
        $('#mascara').css({'width': larguraTela, 'height': alturaTela});
        $('#mascara').fadeIn(1000);
        $('#mascara').fadeTo("slow", 0.8);
        var left = ($(window).width() / 2) - ($(id).width() / 2);
        var top = ($(window).height() / 2) - ($(id).height() / 2);
        $(id).css({'top': top, 'left': left});
        $(id).show();
    });

    $("#mascara").click(function () {
        $(this).hide();
        $(".window").hide();
    });

    $('.fechar').click(function (ev) {
        ev.preventDefault();
        $("#mascara").hide();
        $(".window").hide();
    });

    if (localStorage.getItem('selecaoDeTurma') == 'autocomplete') {
        $(".tt-autocomplete,.tt-normal,.tt-alternar-busca").toggleClass('hidden')
    }
});


function alunosSelecionados() {
    let alunos = []
    $('.group1:checked').each(function (idx, el) {
        let i = 0
        let alunData = el.value.split('@')
        let matriculaId = $(this).data()
        let aluno = {
            'unidadeId' : alunData[i++],
            'alunoId'   : alunData[i++],
            'matId'     : matriculaId['matriculaId'],
            'matricula' : alunData[i++],
            'pessoaId'  : alunData[i++],
            'empresaId' : alunData[i++],
        }
        alunos.push(aluno)
    })

    return alunos
}

//  RETORNA TRUE OU FALSE SE TODAS AS CAIXAS FOREM MARCADAS
//  USADO PARA DESCOBRIR SE TODOS FORAM SELECIONADOS OU APENAS
//  ALGUNS DA LISTA. FUNCIONA JUNTO COM alunosSelecionados()
function todosAlunosSelecionados() {
    let alunos = [];

    $('.group1').each(function (idx, el) {
        let i = 0
        let alunData = el.value.split('@')
        let matriculaId = $(this).data()
        let aluno = {
            'unidadeId' : alunData[i++],
            'alunoId'   : alunData[i++],
            'matId'     : matriculaId['matriculaId'],
            'matricula' : alunData[i++],
            'pessoaId'  : alunData[i++],
            'empresaId' : alunData[i++],
        }
        alunos.push(aluno)
    })

    //  FORMA COMPACTA, MAS NÃO IDERAL PARA COMPARAR AS ARRAYS

    if ( JSON.stringify(alunos)==JSON.stringify(alunosSelecionados())){
        if($("#nome").val() == ""){
            return true
        }else{
            return false
        }

    }else{
        return false
    }
}

function dialogTrocarTurma() {
    let alunos = alunosSelecionados()

    if (!alunos.length) {
        swal("Atenção", "Selecione ao menos 1 aluno.", "warning")
        return
    }

    let contagemAlunos = alunos.length > 1 ? alunos.length + " alunos" : "1 aluno"

    $("#tt-contagem-alunos").text(contagemAlunos)
    $("#dialog-troca-turma").modal().data('alunos', alunos)
}


function dialogEnviarMensagem() {
    let alunos = alunosSelecionados()

    if (!alunos.length) {
        swal("Atenção", "Selecione ao menos 1 aluno.", "warning")
        return
    }

    let contagemAlunos = alunos.length > 1 ? alunos.length + " alunos" : "1 aluno"

    swal("Função de chamada de dialogEnviarMensagem ok.")

    //$("#tt-contagem-alunos").text(contagemAlunos)
    //$("#dialog-troca-turma").modal().data('alunos', alunos)
}

function trocarTurma() {
    let alunos       = $("#dialog-troca-turma").data('alunos')
    let turmaDestino = $("#dialog-troca-turma").data("turmaDestino")

    if(!alunos.length) {
        swal("Atenção", "Selecione ao menos 1 aluno.", "warning")
        return
    }

    if(!turmaDestino > 0) {
        swal("Atenção", "Selecione a turma destino.", "warning")
        return
    }

    $.ajax({
        url: 'dao/alunos.php',
        type: 'POST',
        data: {
            action: 'progredirDeTurma',
            turmaDestino: turmaDestino,
            alunos: alunos.map(function (elem) { return elem.matricula })
        },
        success: function (data) {
            criaAlerta('success', JSON.parse(data).msg);
            $("#dialog-troca-turma").modal('hide')
        },
        error: function (xhr) {
            let error = JSON.parse(xhr.responseText)
            criaAlerta('error', error.msg + (error.debug || ""));
        }
    })
}

$("#tt-turma-autoc").autocomplete({
    source: "/dao/turmas.php?action=recebeTurmasAutocomplete",
    minLength: 3,
    delay: 500,
    change: function (event, ui) {
        $("#tt-turma-autoc").val(ui.item == null ? "" : ui.item.label)
        $("#dialog-troca-turma").data("turmaDestino", ui.item == null ? null : ui.item.id)
    }
})

$(".tt-alternar-busca").click(function () {
    let selecaoDeTurma = localStorage.getItem('selecaoDeTurma')
    localStorage.setItem(
        'selecaoDeTurma',
        selecaoDeTurma == 'normal' ? 'autocomplete' : 'normal'
    )

    $(".tt-autocomplete,.tt-normal,.tt-alternar-busca").toggleClass('hidden')
})

$(function () {
    $.ajax({
        url: "dao/unidades.php",
        type: "POST",
        data: {action: "recebeUnidadesFuncionario"},
        context: $('#tt-unidade'),
        beforeSend: bloqueiaUI,
        complete: $.unblockUI,
        success: function (data) {
            this.html(data).change();
        }
    });
})

$("#tt-unidade").change(function () {
    $.ajax({
        url: "dao/cursos.php",
        type: "POST",
        data: {action: "recebeCursos", idunidade: $('#tt-unidade :selected').val()},
        context: $('#tt-curso'),
        beforeSend: bloqueiaUI,
        complete: $.unblockUI,
        success: function (data) {
            this.html(data);
        }
    });
});

$("#tt-curso").change(function () {
    $.ajax({
        url: "dao/series.php",
        type: "POST",
        data: {action: "recebeSeriesComTurmas", idcurso: $('#tt-curso').val()},
        context: $('#tt-serie'),
        beforeSend: bloqueiaUI,
        complete: $.unblockUI,
        success: function (data) {
            this.html('<option value="todos" selected="selected">TODOS</option>' +data);
        }
    });
});

$("#tt-serie").change(function () {
    $.ajax({
        url: "dao/turmas.php",
        type: "POST",
        data: {action: "recebeTurmas2", idserie: $('#tt-serie').val(), situacao: $('#situacao :selected').val(), anoletivomatricula: $('#idanoletivo :selected').val()},
        context: $('#tt-turma'),
        beforeSend: bloqueiaUI,
        complete: $.unblockUI,
        success: function (data) {
            this.html('<option value="todos" selected="selected">TODOS</option>' +data);
        }
    });
});

$("#tt-turma").change(function () {
    $("#dialog-troca-turma").data("turmaDestino", $("#tt-turma").val())
})

$('#modalRematricula').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var alunoId = button.data('aluno-id')
    var matriculaId = button.data('matricula-id')
    var modal = $(this)

    $.get('resp_alunos_lista_kit_rematricula_modal.php?alunoId=' + alunoId + '&matriculaId=' + matriculaId, function (data) {
        modal.find('.modal-body').html(data)
    })
})

function rematriculaAbrirTodos () {
    $('.kit-renovacao-matricula').click()
    $('.kit-renovacao-matricula-link').each(function () {
        console.log($(this))
        window.open($(this).attr('href'), '_blank')
    })
}

function openDocumentosModal() {
    document.getElementById('modal-gerar-documento-aluno').classList.toggle('hidden');
    document.getElementById('searchDocInput').focus();
}
</script>
