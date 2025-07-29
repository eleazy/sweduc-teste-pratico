<?php
include 'headers.php';
include 'dao/conectar.php';

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

include 'permissoes.php';

$hoje = date("Y-m-d");
$query = "SELECT funcionarios.idunidade as idunidade, funcionarios.id as fid, unidade  FROM funcionarios, unidades WHERE funcionarios.idunidade=unidades.id AND  funcionarios.idpessoa=$idpessoalogin";
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$idfuncionario = $row['fid'];
$idfuncionariounidade = $row['idunidade'];
$nomeunidade = $row['unidade'];
echo '<input type="hidden" name="idfuncionario" id="idfuncionario" value="' . $idfuncionario . '">';
echo '<input type="hidden" name="idfuncionariounidade" id="idfuncionariounidade" value="' . $idfuncionariounidade . '">';
?>

<style type="text/css">
    @page {
        size: auto;   /* auto is the initial value */
    }

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

    #mascara {
        position:absolute;
        left:0;
        top:0;
        z-index:9000;
        background-color:#000;
        display:none;
    }

    .fechar {
        display:block;
        text-align:right;
    }
</style>

<div id="dialog-recebe-medias" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Médias</h4>
            </div>

            <div class="modal-body">
                <div
                    id="conteudo-medias"
                    class="text-muted"
                ></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn grey-color" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div id="content-outer">
    <div class="container-fluid py-3">
        <div class="flex flex-wrap">
            <div class="print:hidden">
                <h3>Acadêmico | Lançamento de Notas | Fundamental e Médio <a class="print:hidden" href="#janela1" title="Ajuda" rel="modal"><img src="images/video1.png" title="Ajuda" border="0" /></a></h3>
            </div>

            <div class="print:hidden ml-auto">
                <button type="button" name="printNotas" id="printNotas" disabled="disabled" class="btn primary-color btn-block">
                    <i class="fa fa-print"></i> Imprimir
                </button>
            </div>

            <div class="window print:hidden" id="janela1">
                <a href="#" class="fechar"><img src="images/fechar.png" /></a>
                <center>
                    <video width="853" height="480" controls>
                        <source src="videos/lancamento-de-notas-1.mp4" type="video/mp4">
                        Seu browser não tem suporte para rodar vídeo
                    </video>
                </center>
            </div>
            <div id="mascara"></div>
        </div>

        <form id="formsalvanota" action="dao/notas.php" method="post" target="_blank">
            <div class="box-search noPrint">
                <input type="hidden" name="action" value="salvanota" />
                <input type="hidden" name="idpermissoes" id="idpermissoes" value="<?= $idpermissoes ?>" />
                <input type="hidden" name="idpessoalogin" id="idpessoalogin" value="<?= $idpessoalogin ?>" />
                <div class="row">
                    <div class="col-lg-1">
                        <label for="idanoletivo">Ano Letivo</label>
                        <select name="idanoletivo" id="idanoletivo" class="form-control">
                            <option value="-1"> - </option>
                            <?php
                            $query1 = "SELECT * FROM anoletivo ORDER BY anoletivo DESC";
                            $result1 = mysql_query($query1);
                            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                                ?>
                            <option value="<?= $row1['id'] ?>" <?php echo ($row1['anoletivo'] == date('Y')) ? 'selected="selected"' : ''; ?> ><?= $row1['anoletivo'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="idunidade">Unidade</label>
                        <select name="idunidade" id="idunidade" class="form-control">
                            <option value=" - " selected="selected"> - </option>
                            <?php
                            if ($idpermissoes == "1" || $academico[11] > 1) {
                                echo '<option value="todos" selected="selected">TODOS</option>';
                                $query = "SELECT * FROM unidades GROUP BY unidade ORDER BY unidade ASC";
                            } else {
                                if (($unidades <> "0") && (trim($unidades) <> "")) {
                                    $query = "SELECT * FROM unidades WHERE id IN (" . $unidades . ") ORDER BY unidade ASC";
                                } else {
                                    if ($idfuncionariounidade == "0") {
                                        $query = "SELECT * FROM unidades GROUP BY unidade ORDER BY unidade ASC";
                                    } else {
                                        $query = "SELECT * FROM unidades WHERE id = " . $idfuncionariounidade . " GROUP BY unidade ORDER BY unidade ASC";
                                    }
                                }
                            }
                            $result = mysql_query($query);
                            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                                ?>
                            <option value="<?= $row['id'] ?>"><?= $row['unidade'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <label for="idgrade">Curso :: Série :: Turma :: Disciplina</label>
                        <select name="idgrade" id="idgrade" class="form-control" >
                            <option value="-1"> - </option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label for="idperiodo">Período</label>
                        <select name="idperiodo" id="idperiodo" class="form-control idperiodoDialogo">
                            <option value="-1"> - </option>
                            <?php
                            if (((in_array($configuracoes[7], $arraydo2)) && (in_array($academico[5], $arraydo2))) || $academico[11] > 1) {
                                $query1 = "SELECT * FROM periodos ORDER BY colunaboletim ASC";
                            } else {
                                $query1 = "SELECT * FROM periodos WHERE ( DATE_FORMAT(NOW(),'%m%d') BETWEEN DATE_FORMAT(datade,'%m%d') AND DATE_FORMAT(dataate,'%m%d') ) ORDER BY colunaboletim ASC";
                            }
                                // $query1 = "SELECT * FROM periodos WHERE ( DATE_FORMAT(NOW(),'%m%d') BETWEEN DATE_FORMAT(datade,'%m%d') AND DATE_FORMAT(dataate,'%m%d') ) OR ( datade='0000-00-00' AND dataate='0000-00-00') ORDER BY colunaboletim ASC";
                            $result1 = mysql_query($query1);
                            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                                echo '<option value="' . $row1['id'] . '">' . $row1['periodo'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-top: 15px;">
                    <div class="col-lg-6">
                        <label for="idavaliacao">Avaliação</label>
                        <select name="idavaliacao" id="idavaliacao" class="form-control">
                            <option value="-1"> - </option>
                        </select>
                    </div>
                </div>
                <div id="todas_aviso" class="hide" style="text-shadow: 0 1px 0 rgba(255, 255, 255, 1); background-color: #dedede; color: #FF0000; margin-top: 5px; padding: 3px;"></div>
                <div id="cachenotas"></div>
            </div>

            <div id="lista-notas">
                <!-- Populado por buscaNotas() -->
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $("#idanoletivo, #idunidade").change(function () {
        buscaDados('idgrade', 'buscaTurmaDisciplinas');
        limpaTabela();
        atualizarPeriodosNovo($('#idanoletivo :selected').val());
    });

    $("#idgrade").change(function () {
        limpaTabela();
        atualizarPeriodosNovo($('#idanoletivo :selected').val());
        $("#idavaliacao").html('<option value="">Seleciona Avaliação</option>');
        $("#idperiodo option[value='-1']").prop('selected', true);
        buscaProf();
    });

    $("#idperiodo").change(function () {
        limpaTabela();
        buscaDados('idavaliacao', 'buscaAvaliacoes');
    });

    function verificaFechamentoPeriodo(aid,uid,gid,pid) {
         $.ajax({
            url: 'academico_fechamento_periodo_salva.php',
            type: 'POST',
            data: {action: "verificafechado", aid: aid,uid: uid,gid: gid,pid: pid},
            context: jQuery('#conteudoBusca'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function (data) {
                if(data==1) {
                    swal('Atenção', 'Este período se encontra fechado para essa turma. Você precisa reabrí-lo para editar notas lançadas.', 'warning');
                    return false;
                }
                return true;
            }
        });
    }

    $("#idavaliacao").change(function () {
        if($('#idavaliacao :selected').val() == 0) {
            return false;
        } else {
            buscaNotas();
        }
    });

    function buscaDados(contexto, action) {
        $.ajax({
            url: "dao/notas.php",
            type: 'POST',
            data: {
                action: action,
                idgrade: $('#idgrade :selected').val(),
                idavaliacao: $('#idavaliacao :selected').val(),
                idperiodo: $('#idperiodo :selected').val(),
                idunidade: $('#idunidade :selected').val(),
                idanoletivo: $('#idanoletivo :selected').val(),
                edinfantil:0
            },
            context: $("#" + contexto),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function (data) {
                this.html(data);
                $("#idavaliacao").change();
                $("#gradePrint").html($("#idgrade :selected").text());
            }
        });
    }

    function limpaTabela() {
        $("#headnotas").html("");
        $("#bodynotas").html("");
        $("#salvanota").prop("disabled", "true");
        $("#printNotas").prop("disabled", "true");
    }

    function buscaProf() {
        $.ajax({
            url: "dao/notas.php",
            type: 'POST',
            data: {
                action: "buscaProf",
                idanoletivo: $("#idanoletivo :selected").val(),
                idgrade: $("#idgrade :selected").val(),
                idperiodo: $("#idperiodo :selected").val(),
                idavaliacao: $("#idavaliacao :selected").val()
            },
            context: $("#headnotas"),
            success: function (data) {
                $("#nomeprofessor").html('Professor: '+ data);
                $("#gradePrint").html($("#idgrade :selected").text());
                $("#periodoPrint").html("Período: "+ $("#idperiodo :selected").text());
                $("#anoletivoPrint").html("Ano letivo: "+ $("#idanoletivo :selected").text());
                $("#unidadePrint").html($("#idunidade :selected").text());
            }
        });
    }

    function buscaNotas() {
        if ($("#idperiodo").val() == -1) {
            $("#idperiodo").removeClass("error");
            $("#idperiodo").addClass("error");
            $("#idperiodo").blur(function () {
                if ($("#idperiodo").val() == -1) {
                    $("#idperiodo").removeClass("error");
                    $("#idperiodo").addClass("error");
                }
            });
            $("#idperiodo").focus();

            return;
        }

        bloqueiaUI();

        let csv = null;
        const csvElement = document.getElementById('arquivo-csv');
        if (csvElement && csvElement.files) {
            csv = csvElement.files[0];
        }

        const formData = new FormData();
        formData.append('anoletivoId', $("#idanoletivo :selected").val());
        formData.append('gradeId', $("#idgrade :selected").val());
        formData.append('periodoId', $("#idperiodo :selected").val());
        formData.append('avaliacaoId', $("#idavaliacao :selected").val());
        formData.append('educacaoInfantil', 0);
        if (csv) {
            formData.append('csv', csv);
            formData.append('tipo-id', document.getElementById('tipo-id').value);
            formData.append('posicao-id', document.getElementById('posicao-id').value);
            formData.append('posicao-nota', document.getElementById('posicao-nota').value);
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/academico/notas/listar');

        xhr.onreadystatechange = function () {
            if (xhr.readyState == xhr.DONE) {
                $("#lista-notas").html(xhr.responseText);
                update_rows();
                $("#salvanota").removeAttr("disabled");
                $("#printNotas").removeAttr("disabled");
                $("#gradePrint").html($("#idgrade :selected").text());
                $("#periodoPrint").html($("#idperiodo :selected").text());
                $("#anoletivoPrint").html($("#idanoletivo :selected").text());

                if($("#idavaliacao").val().indexOf(',') > -1) {
                    $("#salvanota").prop("disabled", "true");
                    $("#todas_aviso").html("Somente visualização. Para editar, selecione uma avaliação específica.");
                    $("#todas_aviso").removeClass('hide').addClass('show');
                } else {
                    $("#salvanota").removeAttr("disabled");
                    $("#todas_aviso").html("");
                    $("#todas_aviso").removeClass('show').addClass('hide');
                }

                ativaEnter();
                $.unblockUI();
            }
        };

        xhr.send(formData);
    }

    $("#printNotas").click(function () {
        window.print();
    });

    function medias(idturma, idanoletivo, nomealuno, idaluno, nummatricula, idgrade) {
        $.ajax({
            url: "alunos_medias.php",
            type: 'POST',
            context: $('#conteudo-medias'),
            data: {
                nomealuno: nomealuno,
                idturma: idturma,
                idanoletivo: idanoletivo,
                idaluno: idaluno,
                idgr: idgrade,
                nummatricula: nummatricula,
            },
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function (data) {
                $('#dialog-recebe-medias').modal('toggle');
                this.html(data);
            }
        });
    }

    $(document).ready(function () {
        $("a[rel=modal]").click(function (ev) {
            ev.preventDefault();
            var id = $(this).attr("href");
            var alturaTela = $(document).height();
            var larguraTela = $(window).width();
            //colocando o fundo preto
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
    });

    function isNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode != 46 && charCode > 31
        && (charCode < 48 || charCode > 57))
            return false;

        return true;
    }

    function atualizarPeriodosNovo(id_anoletivo) {
        $.ajax({
            url: "dao/periodos.php" ,
            type: "POST",
            data: {
                action: "periodosNovoSimples",
                idanoletivo: id_anoletivo,
                trimestre: $('#idgrade :selected').val(),
                id_funcionario: $("#idfuncionario").val()
            },
            context: jQuery('.idperiodoDialogo'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function(data){
                this.html('<option value="-1"> - </option>'+data);
            }
        });
    }

    $(function () {
        $('input').checkBox();
        $('#toggle-all').click(function () {
            $('#toggle-all').toggleClass('toggle-checked');
            $('#mainform input[type=checkbox]').checkBox('toggle');
            return false;
        });
    });

    $(document).ready(function () {
        $('.styledselect').selectbox({inputClass: "selectbox_styled"});
        $("#bodynotas .camponota").click(function () {
            alert(this.attr('data-id'));
        });
    });

    function notaEditar (id) {
        $('#cond'+id).val('true');
    }
</script>
