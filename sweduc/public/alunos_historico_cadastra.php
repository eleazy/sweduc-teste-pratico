<?php
include('headers.php');
include('dao/conectar.php');
require_once('function/curso.func.php');
require_once('function/serie.func.php');
require_once('function/historico.func.php');

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}
include('permissoes.php');

$query1 = "SELECT nome, obs_fundamental, obs_medio, obs_individual FROM pessoas, alunos WHERE alunos.id=$idaluno AND pessoas.id=alunos.idpessoa";
$result1 = mysqli_query($conn, $query1);
$row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC);
$nomealuno = $row1['nome'];

$obs_fundamental = $row1['obs_fundamental'];
$obs_medio = $row1['obs_medio'];
$obs_individual = $row1['obs_individual'];

$cursos = buscarCursoHistorico();
?>

<script type="text/javascript">
function atualizaTabela() {
    $.ajax({
        url: "alunos_historico.php",
        type: "POST",
        data: {idaluno: <?= $idaluno ?>, idpermissoes: $("#idpermissoes").val()},
        context: $("#historicoTabela"),
        success: function (data) {
            this.html(data);
        }
    });
}
<?php
$query = "SELECT DISTINCT serie FROM alunos_historico ORDER BY serie ASC";
$result = mysqli_query($conn, $query);
$seriecadastradas = [];
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    $seriecadastradas[] = "'" . addslashes($row['serie']) . "'";
}
$seriecadastradas = implode(",", $seriecadastradas);
?>
    $(function () {
        var serieTags = [<?= $seriecadastradas ?>];
        $(".serieCadastra").autocomplete({
            source: serieTags
        });
    });
<?php
$query = "SELECT DISTINCT disciplina FROM alunos_historico ORDER BY disciplina ASC";
$result = mysqli_query($conn, $query);
$disciplinacadastradas = [];
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    $disciplinacadastradas[] = "'" . addslashes($row['disciplina']) . "'";
}
$disciplinacadastradas = implode(",", $disciplinacadastradas);
?>

<?php
$query = "SELECT DISTINCT escola FROM alunos_historico group by escola ORDER BY escola ASC";
$result = mysqli_query($conn, $query);
$escolascadastradas = [];
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    if (!empty($row['escola'])) {
        $escolascadastradas[] = "'" . addslashes($row['escola']) . "'";
    }
}
$escolascadastradas = implode(",", $escolascadastradas);
?>
    $(function () {
        var escolasTags = [<?= $escolascadastradas ?>];
        $(".escolaCadastra").autocomplete({
            source: escolasTags
        });
    });
<?php
$query = "SELECT DISTINCT local FROM alunos_historico ORDER BY local ASC";
$result = mysql_query($query);
$localcadastradas = [];
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    if (!empty($row['local'])) {
        $localcadastradas[] = "'" . addslashes($row['local']) . "'";
    }
}
$localcadastradas = implode(",", $localcadastradas);
?>
    $(function () {
        var localTags = [<?= $localcadastradas ?>];
        $(".localCadastra").autocomplete({
            source: localTags
        });
    });
<?php
$query = "SELECT DISTINCT situacao FROM alunos_historico ORDER BY situacao ASC";
$result = mysql_query($query);
$situacaocadastradas = [];
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    if (!empty($row['situacao'])) {
        $situacaocadastradas[] = "'" . addslashes($row['situacao']) . "'";
    }
}
$situacaocadastradas = implode(",", $situacaocadastradas);
?>
    $(function () {
        var situacaoTags = [<?= $situacaocadastradas ?>];
        $(".situacaoCadastra").autocomplete({
            source: situacaoTags
        });
    });


// ****** //


    function apagaHistorico(id, idaluno) {
        $.ajax({
            url: "dao/historico.php",
            type: "POST",
            data: {action: "apaga", id: id, idaluno: idaluno},
            context: jQuery(".tabela_historico > tbody"),
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
                retorno = data.split("|");
                status = retorno[0];
                tabela = retorno[1];
                if (status == 'red')
                    criaAlerta('error', 'Erro ao excluir dados para histórico');
                else {
                    this.html(tabela);
                    criaAlerta('success', 'Dados excluidos');
                }
                update_this_rows();
            }
        });
    }

    $("#cadastrar").click(function () {

        if ($("#anoCadastra").val() == "") {

            swal("Atenção", "Preencha o campo ano.", "warning");
            $("#anoCadastra").addClass("inp-form-error");
            $("#anoCadastra").focus();
            $("#anoCadastra").on("blur", function () {

                $("#anoCadastra").attr("class", "form-control");
            });
        } else if ($("#serieCadastra").val() == "") {

            swal("Atenção", "Escolha a série/curso.", "warning");
            $("#serieCadastra").addClass("inp-form-error");
            $("#serieCadastra").focus();
            $("#serieCadastra").on("blur", function () {
                $("#serieCadastra").attr("class", "form-control");
            });
        } else if ($("#situacaoCadastra").val() == "") {

            swal("Atenção", "Preencha o campo situação.", "warning");
            $("#situacaoCadastra").addClass("inp-form-error");
            $("#situacaoCadastra").focus();
            $("#situacaoCadastra").on("blur", function () {

                $("#situacaoCadastra").attr("class", "form-control");
            });
        } else if ($("#frequenciaCadastra").val() == "") {

            swal("Atenção", "Preencha a frequência do aluno.", "warning");
            $("#frequenciaCadastra").addClass("inp-form-error");
            $("#frequenciaCadastra").focus();
            $("#frequenciaCadastra").on("blur", function () {
                $("#frequenciaCadastra").attr("class", "form-control");
            });
        } else if ($("#escolaCadastra").val() == "") {

            swal("Atenção", "Preencha o nome da escola.", "warning");
            $("#escolaCadastra").addClass("inp-form-error");
            $("#escolaCadastra").focus();
            $("#escolaCadastra").on("blur", function () {

                $("#escolaCadastra").attr("class", "form-control");
            });
        } else if ($("#localCadastra").val() == "") {

            swal("Atenção", "Preencha o local da escola.", "warning");
            $("#localCadastra").addClass("inp-form-error");
            $("#localCadastra").focus();
            $("#localCadastra").on("blur", function () {
                $("#localCadastra").attr("class", "form-control");
            });
        } else if ($("#disciplinacadastra").val() == "") {

            swal("Atenção", "Preencha o componente curricular.", "warning");
            $("#disciplinacadastra").addClass("inp-form-error");
            $("#disciplinacadastra").focus();
            $("#disciplinacadastra").on("blur", function () {
                $("#disciplinacadastra").attr("class", "form-control");
            });
        } else if ($("#mediaCadastra").val() == "") {

            swal("Atenção", "Preencha a média.", "warning");
            $("#mediaCadastra").addClass("inp-form-error");
            $("#mediaCadastra").focus();
            $("#mediaCadastra").on("blur", function () {
                $("#mediaCadastra").attr("class", "form-control");
            });
        } else {

            $.ajax({
                url: "dao/historico.php",
                type: "POST",
                data: $("#formhistorico").serialize(),
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
                    retorno = data.split("|");
                    status = retorno[0];
                    msg = retorno[1];
                    $("#idhistorico").val("0");
                    $("#disciplina").val("");
                    $("#media").val("");
                    // insereAlerta (status, "table-content",msg,"","");
                    swal("Atenção", msg);
                    atualizaTabela();
                }
            });
        }
    });
    $("#salvar_obs").click(function () {

        //swal("Atenção", "teste");

        $.ajax({
            url: "dao/historico.php",
            type: "POST",
            data: $("#formhistorico").serialize(),
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
                var retorno = data.split("|");
                var status = retorno[0];
                var msg = retorno[1];
                $("#idhistorico").val("0");
                $("#disciplina").val("");
                $("#media").val("");
                insereAlerta(status, "table-content", msg, "", "");
                atualizaTabela();
            }
        });
    });

    function voltar() {
        $.ajax({
            url: "alunos_busca.php",
            type: 'POST',
            context: jQuery('#conteudo'),
            data: {idaluno: <?= $idaluno ?>, idpermissoes: $("#idpermissoes").val(), idpessoalogin: $("#idpessoalogin").val()},
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
                conteudoL = $('#conteudoLista').html();
                this.html(data);
                $('#conteudoBusca').html(conteudoL);
                $('html, body').animate({scrollTop: $("#conteudoBusca").offset().top}, 'slow');
            }
        });
    }

    /*$(document).ready(function () {
        $(".tabs-menu a").click(function (event) {
            event.preventDefault();
            $(this).parent().addClass("current");
            $(this).parent().siblings().removeClass("current");
            var tab = $(this).attr("href");
            $(".tab-content").not(tab).css("display", "none");
            $(tab).fadeIn();
        });
    });*/
    //Ao clicar em adicionar ele cria uma linha com novos campos
    $("#botaoAdicionar").click(function () {
        $('<div class="conteudoIndividual"><input type="text" placeholder="Nº do Documento" maxlength="6" name="numeroDocumento' + i + '" required/><select name="tipoDocumento' + i + '" required><option value="" disabled selected>Tipo do Documento</option><option value="01">Volvo</option><option value="02">Saab</option></select><select name="subTipoDocumento' + i + '" required><option value="" disabled selected>Subtipo do Documento</option><option value="01">Volvo</option><option value="02">Saab</option></select><a href="#" id="linkRemover">- Remover Campos</a></div>').appendTo(divContent);
        $('#removehidden').remove();
        i++;
        $('<input type="hidden" name="quantidadeCampos" value="' + i + '" id="removehidden">').appendTo(divContent);
    });

    $(".enviar").click(function () {
        var form = $(this).attr('data-form');
        $.ajax({
            url: '/api/v1/academico/historico',
            type: 'POST',
            data: $('form').serialize(),
            context: jQuery('#conteudoBusca'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function (data) {
                criaAlerta('success', data.mensagem);
            }
        });
    });

    $(".addRow").click(function () {
        var tabelaAttr = $(this).attr('tabela');
        var tabela = $("#" + tabelaAttr);

        var cloneUltimaLinha = tabela.find('tbody tr:last').clone();
        var primeiraTd = cloneUltimaLinha
            .children('td:first')
            .html('<input type="text" style="width: 130px" class="form-control disciplinacadastra" name="disciplina[][disciplina]" />');

        var nomeDisciplina = '';
        var novoName = cloneUltimaLinha.find('input').each(function () {
            var novaDisciplina = $(this).attr('name').replace(/disciplina\[[^\]]*]/g, 'disciplina[' + nomeDisciplina + ']');
            $(this).attr('name', novaDisciplina);
            this.value = '';
        });

        primeiraTd.append('<input type="hidden" value="99" name="disciplina[][ordem]">');

        cloneUltimaLinha.appendTo(tabela.find('tbody'));

        cloneUltimaLinha.change(function () {
            if (!event.target.classList.contains('disciplinacadastra')) {
                return;
            }

            var nomeDisciplina = event.target.value;
            var novoName = cloneUltimaLinha.find('input').each(function () {
                var novaDisciplina = $(this).attr('name').replace(/disciplina\[[\w\d]*\]/g, 'disciplina[' + nomeDisciplina + ']');
                $(this).attr('name', novaDisciplina);
            });
        });

        $(function () {
            var disciplinaTags = [<?= $disciplinacadastradas ?>];
            $(".disciplinacadastra").autocomplete({
                source: disciplinaTags
            });
        });

        return false;
    });
    function calcularCh(id, idch) {
        var sum = 0;
        $.each($(".chref" + id), function () {
            sum += Number($(this).val());

        });
        if (sum > 0) {
            $("#" + idch).val(sum);
        }
    }
</script>
<div id="content-outer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-10">
                <h3>Histórico Escolar do Aluno <?= $nomealuno ?></h3>
            </div>
            <div class="col-lg-2">
                <button type="button" class="btn primary-color btn-block" value="Voltar" style="margin-top: 15px;" onClick="voltar()">
                    <i class="fa fa-arrow-left"></i> Voltar
                </button>
            </div>
        </div>

        <div>
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($cursos as $index => $curso) : ?>
                    <li role="presentation" class="<?=$index === 0 ? 'active' : ''?>">
                        <a role="tab" data-toggle="tab" href="#tab-<?= $curso['id']; ?>"><?= $curso['curso']; ?></a>
                    </li>
                <?php endforeach; ?>

                <li>
                    <a role="tab" data-toggle="tab" href="#tab-99">Observações</a>
                </li>
            </ul>

            <div class="tab-content" style="margin-bottom: 15px;">
                <?php foreach ($cursos as $index => $curso) : ?>
                    <?php $series = buscarSerieCurso($curso['id']); // Carrega as séries ?>

                    <div role="tabpanel" class="tab-pane <?=$index === 0 ? 'active' : ''?>" id="tab-<?= $curso['id']; ?>">
                        <form id="mainform<?= $curso['id']; ?>" method="post">
                            <table style="width: 100%;" id="content-table<?= $curso['id']; ?>" class="new-table table-striped prod-table">
                                <thead>
                                    <tr>
                                        <th colspan="11" style="border-bottom: 1px solid #d2d2d2;" class="table-header-repeat line-left-2">
                                            <b><?= $curso['curso']; ?></b>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="table-header-repeat line-left-2">
                                            <b>Disciplinas</b>
                                        </th>

                                        <?php foreach ($series as $serie) : ?>
                                            <th style="border-bottom: 1px solid #d2d2d2;" class="table-header-repeat line-left-2" colspan="2">
                                                <b><?= $serie['serie'] ?></b>
                                                <input type="hidden" value="<?= $serie['serie'] ?>" name="serie[]" />
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <th class="table-header-repeat line-left-2"></th>

                                        <?php foreach ($series as $serie) : ?>
                                            <th class="table-header-repeat line-left-2">
                                                <b>nota</b>
                                            </th>

                                            <th class="table-header-repeat line-left-2">
                                                <b>ch</b>
                                            </th>
                                        <?php endforeach ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (buscarDisciplina($curso['id'], $idaluno) as $countDisciplina => $disciplina) : ?>
                                        <tr id="" data="">
                                            <td style="width:100px; line-height: 30px; text-align: center;" >
                                                <?= $disciplina; ?>

                                                <input
                                                    type="hidden"
                                                    value="<?= $countDisciplina + 1 ?>"
                                                    name="disciplina[<?=$disciplina?>][ordem]"
                                                />
                                            </td>

                                            <?php foreach ($series as $i => $serie) : ?>
                                                <?php $nota = buscarHistoricoNotaCh($idaluno, $disciplina, $series[$i]['serie']); ?>

                                                <td style="width:30px; line-height: 30px; text-align: center;">
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        value="<?= $nota[0]['media'] ?? ''?>"
                                                        name="disciplina[<?=$disciplina?>][notas][<?=$curso['curso']?>][<?= $serie['serie'] ?>][nota]"
                                                    />
                                                </td>

                                                <td style="width:30px; line-height: 30px; text-align: center;">
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        value="<?= $nota[0]['cargahoraria'] ?? ''?>"
                                                        class="chref<?= $curso['id'] . "_" . $i ?>"
                                                        name="disciplina[<?=$disciplina?>][notas][<?=$curso['curso']?>][<?= $serie['serie'] ?>][ch]"
                                                    />
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <input
                                type="button"
                                value="Nova linha"
                                class="btn primary-color addRow"
                                tabela="content-table<?= $curso['id']; ?>"
                                quant="<?= is_countable($series) ? count($series) : 0 ?>" ncampo="<?= $countDisciplina ?>" style="margin: 5px 0 15px 0;"
                            />

                            <table style="width: 100%;" class="new-table table-striped prod-table">
                                <thead>
                                    <tr>
                                        <th class="table-header-repeat line-left-2" style="border-bottom: 1px solid #d2d2d2;" colspan="7">&nbsp;</th>
                                    </tr>
                                    <tr>
                                        <th class="table-header-repeat line-left-2">
                                            <b>Série</b>
                                        </th>
                                        <th class="table-header-repeat line-left-2">
                                            <b>Ano Letivo</b>
                                        </th>
                                        <th class="table-header-repeat line-left-2">
                                            <b>Situação</b>
                                        </th>
                                        <th class="table-header-repeat line-left-2">
                                            <b>Escola</b>
                                        </th>
                                        <th class="table-header-repeat line-left-2">
                                            <b>Local</b>
                                        </th>
                                        <th class="table-header-repeat line-left-2">
                                            <b>Total Carga Horária</b>
                                        </th>
                                        <th class="table-header-repeat line-left-2">
                                            <b>Frequência Total Anual</b>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($series as $i => $serie) : ?>
                                        <?php $historico = buscarHistoricoDadosEscola($idaluno, $serie['serie']); ?>

                                        <tr id="" data="">
                                            <td style="width:100px; line-height: 30px; text-align: center;">
                                                <?= $serie['serie'] ?>
                                            </td>

                                            <td style="width:30px; line-height: 30px; text-align: center;">
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    value="<?=$historico[0]['ano'] ?? ''?>"
                                                    name="curso[<?=$curso['curso']?>][<?= $serie['serie'] ?>][ano]"
                                                />
                                            </td>

                                            <td style="width:30px; line-height: 30px; text-align: center;">
                                                <input
                                                    type="text"
                                                    class="form-control situacaoCadastra"
                                                    value="<?=$historico[0]['situacao'] ?? ''?>"
                                                    style="width: 100px"
                                                    name="curso[<?=$curso['curso']?>][<?= $serie['serie'] ?>][situacao]"
                                                />
                                            </td>

                                            <td style="width:30px; line-height: 30px; text-align: center;">
                                                <input
                                                    type="text"
                                                    class="form-control escolaCadastra"
                                                    value="<?=$historico[0]['escola'] ?? ''?>"
                                                    style="width: 100px"
                                                    name="curso[<?=$curso['curso']?>][<?= $serie['serie'] ?>][escola]"
                                                />
                                            </td>

                                            <td style="width:30px; line-height: 30px; text-align: center;">
                                                <input
                                                    type="text"
                                                    class="form-control localCadastra"
                                                    value="<?=$historico[0]['local'] ?? ''?>"
                                                    style="width: 100px"
                                                    name="curso[<?=$curso['curso']?>][<?= $serie['serie'] ?>][local]"
                                                />
                                            </td>

                                            <td style="width:30px; line-height: 30px; text-align: center;">
                                                <?php $cargaHorariaId = $curso['id'] . "_" . $i ?>

                                                <input
                                                    type="text"
                                                    class="form-control totalCargaHoraria"
                                                    value="<?=$historico[0]['carga_horaria_total'] ?? ''?>"
                                                    onclick="calcularCh('<?=$cargaHorariaId?>', 'totalCargaHoraria<?=$i?>')"
                                                    style="width: 100px"
                                                    id="totalCargaHoraria<?= $i; ?>"
                                                    name="curso[<?=$curso['curso']?>][<?= $serie['serie'] ?>][carga_horaria_total]"
                                                />
                                            </td>

                                            <td style="width:30px; line-height: 30px; text-align: center;">
                                                <input
                                                    type="text"
                                                    class="form-control frequenciaTotalAnual"
                                                    value="<?=$historico[0]['frequencia'] ?? ''?>"
                                                    style="width: 100px"
                                                    name="curso[<?=$curso['curso']?>][<?= $serie['serie'] ?>][frequencia]"
                                                />%
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <input
                                type="button"
                                class="btn primary-color enviar"
                                data-form="mainform<?= $curso['id']; ?>"
                                value="Salvar"
                                style="float:right; margin: 5px 0 5px 0;"
                            />

                            <div class="clearfix"></div>
                        </form>
                    </div>
                <?php endforeach; ?>

                <div role="tabpanel" class="tab-pane" id="tab-99">
                    <form id="mainform99" method="post" class="form-horizontal">
                        <div class="form-group">
                            <label class="control-label col-lg-2">Observação Fundamental</label>
                            <div class="col-lg-5">
                                <textarea name="obs_fundamental" class="form-control" rows="5" rows="5"><?= $obs_fundamental ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-2">Observação Medio</label>
                            <div class="col-lg-5">
                                <textarea name="obs_medio" class="form-control" rows="5" rows="5"><?= $obs_medio ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-2">Observação Individual</label>
                            <div class="col-lg-5">
                                <textarea name="obs_individual" class="form-control" rows="5" rows="5"><?= $obs_individual ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input type="hidden" name="idaluno" value="<?= $idaluno ?>" />
                                <input type="hidden" name="nomealuno" value="<?= $nomealuno ?>" />
                                <input type="button" class="btn primary-color enviar" data-form="mainform99"  value = "Salvar" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="conteudoBusca"></div>
    </div>
</div>
<script type="text/javascript">
    $('#loader').hide();
    function update_this_rows() {
        /*$(".table1 tbody").find("tr:even").css("background-color", "#aaa");
        $(".table1 tbody").find("tr:odd").css("background-color", "#eee");*/
    }
    atualizaTabela();
    //update_this_rows();
</script>
<script type="text/javascript">
    $('#loader').hide();
    /*$(".prod-table tbody").find("tr:even").css("background-color", "#aaa");
    $(".prod-table tbody").find("tr:odd").css("background-color", "#eee");
    $(".prod-table tbody tr td").wrapInner('<div style="page-break-inside: avoid !important;vertical-align:top; margin:0px;" />');
    $("#product-table tbody").find("tr:even").css("background-color", "#aaa");
    $("#product-table tbody").find("tr:odd").css("background-color", "#eee");
    $("#product-table tbody tr td").wrapInner('<div style="page-break-inside: avoid !important;vertical-align:top; margin:0px;" />');
    $('html, body').animate({scrollTop: 0}, 0);*/
</script>
