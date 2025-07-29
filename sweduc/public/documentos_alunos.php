<?php

use App\Academico\Model\Responsavel;
use App\Model\Financeiro\Titulo;
use App\Sistema\Documento\DocumentoAlunosRenderer;
use App\Model\Core\Documento;
use Carbon\Carbon;

if (empty($email)) {
    require_once 'headers.php';
}
require_once 'dao/conectar.php';
if (!$email) {
    require_once 'auth/injetaCredenciais.php';
}
require_once 'lib/barcode.inc.php';

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

const ESPACO = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

$contador = 0;
$alunoINFO = explode("@", $idalunoDOC);

if (isset($alunoINFO)) {
    $codigo_aluno = $alunoINFO[2];
} else {
    $queryCon = "SELECT id,numeroaluno FROM alunos where id=" . $idalunoDOC;
    $resultCon = mysql_query($queryCon);
    $rowCon = mysql_fetch_array($resultCon, MYSQL_ASSOC);
    $codigo_aluno = $rowCon['numeroaluno'];
    $id_aluno = $rowCon['id'];
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>..:: SW ::..</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link href="css/screen.css" rel="stylesheet" type="text/css" media="screen" title="default" />
    <link href="css/default.css" rel="stylesheet" type="text/css" media="screen" title="default" />
    <link href="css/print.css" mce_href="css/print.css" rel="stylesheet" type="text/css" media="print" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.min.css" integrity="sha512-XaXIRtZS8tPFOIDUPIUZIEc7uBiTTo+WQQjFeZ0MJ7jG/RUFh+Hx4yX5Vee6xSnEj2XXaFm545NgREfvyE9Qgw==" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js" integrity="sha512-YHQNqPhxuCY2ddskIbDlZfwY6Vx3L3w9WRbyJCY81xpqLmrM6rL2+LocBgeVHwGY9SXYfQWJ+lcEWx1fKS2s8A==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" integrity="sha512-kHKdovQFIwzs2NABJSo9NgJKZOrRqLPSoqIumoCkaIytTRGgsddo7d0rFyyh8RvYyNNKcMF7C9+4sM7YhMylgg==" crossorigin="anonymous"></script>
    <script src="lib/html2canvas.js" class="jsbin"></script>
    <script src="lib/jquery.blockUI.js" class="jsbin"></script>
    <script src="/js/index.js"></script>

    <script>
        ajaxQueue = {
            queue: [],
            add: function(data) {
                let ajax = function() {
                    return $.ajax(data);
                }

                ajaxQueue.queue.push(ajax);

                if (ajaxQueue.queue.length === 1) {
                    ajaxQueue.run();
                }
            },
            run: function() {
                ajaxQueue.queue[0]().complete(function() {
                    //console.log("request complete");

                    ajaxQueue.queue.shift();
                    if (ajaxQueue.queue.length !== 0) {
                        ajaxQueue.run();
                    }
                })
            }
        }
    </script>

    <?php if (!$email) : ?>
        <script>
            $(function() {
                $(document).on("click", ".button", function() {
                    $("#enviaemail").submit();
                });

                $("#dialog-email").dialog({
                    resizable: false,
                    autoOpen: false,
                    modal: true,
                    buttons: {
                        "ABRIR": function() {
                            idaluno = $(this).data("idaluno");
                            idpessoa = $(this).data("idpessoa");
                            idunidade = $(this).data("idunidade");

                            alunoMail = ($('#alunoMail').is(':checked') ? 1 : 0);
                            paiMail = ($('#paiMail').is(':checked') ? 1 : 0);
                            maeMail = ($('#maeMail').is(':checked') ? 2 : 0);
                            respfinMail = ($('#respfinMail').is(':checked') ? 1 : 0);
                            resppedagMail = ($('#resppedagMail').is(':checked') ? 1 : 0);

                            ajaxQueue.add({
                                url: "dao/alunos.php",
                                data: {
                                    action: "getEmails",
                                    idunidade: idunidade,
                                    idaluno: idaluno,
                                    idpessoa: idpessoa,
                                    alunoMail: alunoMail,
                                    paiMail: paiMail,
                                    maeMail: maeMail,
                                    respfinMail: respfinMail,
                                    resppedagMail: resppedagMail
                                },
                                type: 'POST',
                                context: jQuery('#conteudo'),
                                success: function(ret) {
                                    retorno = ret.split("|");
                                    emailunidade = retorno[0];
                                    data = retorno[1];
                                    data = $.trim(data);

                                    //console.log("DATA: " + data);
                                    //console.log("emailunidade: " + emailunidade);
                                    //console.log("retorno: " + retorno);

                                    $(".noprint").css("display", "none");
                                    html2canvas(document.body, {
                                        onrendered: function(canvas) {
                                            var img = canvas.toDataURL("image/png");
                                            //        $(".noprint").css("display","inline-block");
                                            $("body").html("");
                                            var newForm = $('<form>', {
                                                    'action': '/sistema/emails/enviar',
                                                    'target': '_self',
                                                    'method': 'POST',
                                                    'id': 'enviaemail',
                                                    'enctype': 'multipart/form-data'
                                                })
                                                .append("Para: (emails separados com vírgulas.) ")
                                                .append($('<input>', {
                                                    'name': 'emailto',
                                                    'class': 'inp-form',
                                                    'value': emailunidade,
                                                    'type': 'text'
                                                }).css("width", "97%"))
                                                .append($('<input>', {
                                                    'name': 'emailfrom',
                                                    'class': 'inp-form',
                                                    'value': emailunidade,
                                                    'type': 'hidden'
                                                }))
                                                .append("<br><br>Assunto: ")
                                                .append($('<input>', {
                                                    'name': 'subject',
                                                    'class': 'inp-form',
                                                    'value': 'Relatório',
                                                    'type': 'text'
                                                }).css("width", "97%"))
                                                .append($('<input>', {
                                                    'name': 'anexo',
                                                    'class': 'inp-form',
                                                    'value': img,
                                                    'type': 'hidden'
                                                }))
                                                .append("<br><br>Mensagem:<br> ")
                                                .append($("<textarea>").attr('cols', '180').attr('rows', '10').attr('name', 'bodyemail').val("Relatório"))
                                                .append("<br><br>")
                                                .append($('<input>', {
                                                    'name': 'subject',
                                                    'class': 'button',
                                                    'value': ' ENVIAR EMAIL ',
                                                    'type': 'button'
                                                }).on("click", function() {
                                                    $("#enviaemail").submit();
                                                }))
                                                .append("<br><br>ANEXO:<br>")
                                                .append($('<input>').attr('type', 'image').attr('name', 'imagem').attr('src', img))
                                                .appendTo('body');
                                        }
                                    });
                                }
                            });
                            $(this).dialog("close");
                        },
                        "CANCELAR": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            });

            function geraScreenShoot() {
                ida = $('#ida').val();
                idp = $('#idp').val();
                idu = $('#idu').val();
                $('#dialog-email').data('idaluno', ida).data('idpessoa', idp).data('idunidade', idu).dialog('open');
            }
        </script>
    <?php endif ?>
</head>

<body>
    <?php if (!$email) : ?>
        <div id="displayBox" style="display:none">
            <svg width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" fill="#f0c552">
                <circle cx="60" cy="60" r="45" fill="none" stroke="#fceabb" stroke-width="18" opacity="0.5" />
                <circle cx="60" cy="60" r="45" fill="none" stroke="#f0c552" stroke-width="18" stroke-dasharray="203" stroke-dashoffset="15" stroke-linecap="round">
                    <animateTransform
                        attributeName="transform"
                        type="rotate"
                        from="0 60 60"
                        to="360 60 60"
                        dur="1.2s"
                        repeatCount="indefinite" />
                </circle>
            </svg>
        </div>
        <div id="dialog-email" title="Enviar email." style="display:none;">
            Emails:<br /><br />
            <input type="checkbox" name="aluno" id="alunoMail" value="1" />
            <label for="alunoMail"><span></span>Aluno</label><br />
            <input type="checkbox" name="pai" id="paiMail" value="1" />
            <label for="paiMail"><span></span>Pai</label><br />
            <input type="checkbox" name="mae" id="maeMail" value="1" />
            <label for="maeMail"><span></span>Mãe</label> <br />
            <input type="checkbox" name="respfin" id="respfinMail" value="1" />
            <label for="respfinMail"><span></span>Responsável Financeiro</label><br />
            <input type="checkbox" name="resppedag" id="resppedagMail" value="1" />
            <label for="resppedagMail"><span></span>Responsável Pedagógico</label>
        </div>
        <input type="button" value="   IMPRIMIR   " class="button noprint" onClick="window.print()" />
        <input type="button" value="   EMAIL   " class="button noprint" onClick="geraScreenShoot()" />
    <?php endif ?>

    <?php
    $query = "SELECT * FROM doceditor WHERE id=" . $idDOC;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $colunas = $row['colunas'] + 0;
    $linhas = $row['linhas'] + 0;

    $margemsuperior = $row['margemsuperior'];
    $margemdireita = $row['margemdireita'];
    $margeminferior = $row['margeminferior'];
    $margemesquerda = $row['margemesquerda'];

    $agora = date("d/m/Y H:i:s");
    $mes = date('m');
    switch ($mes) {
        case 1:
            $mes = "janeiro";
            break;
        case 2:
            $mes = "fevereiro";
            break;
        case 3:
            $mes = "março";
            break;
        case 4:
            $mes = "abril";
            break;
        case 5:
            $mes = "maio";
            break;
        case 6:
            $mes = "junho";
            break;
        case 7:
            $mes = "julho";
            break;
        case 8:
            $mes = "agosto";
            break;
        case 9:
            $mes = "setembro";
            break;
        case 10:
            $mes = "outubro";
            break;
        case 11:
            $mes = "novembro";
            break;
        case 12:
            $mes = "dezembro";
            break;
    }
    $hoje = date("d/m/Y");
    $hojeporexenso = date('d') . " de " . $mes . " de " . date('Y');

    if (!function_exists('delete_all_between')) {
        function delete_all_between($beginning, $end, $string)
        {
            $beginningPos = strpos($string, (string) $beginning);
            $endPos = strpos($string, (string) $end);
            if (!$beginningPos || !$endPos) {
                return $string;
            }
            $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
            return str_replace($textToDelete, '', $string);
        }
    }
    if (!function_exists('copia_texto')) {
        function copia_texto($beginning, $end, $string)
        {
            $beginningPos = strpos($string, (string) $beginning);
            $endPos = strpos($string, (string) $end);
            if (!$beginningPos || !$endPos) {
                return "";
            }
            $textToCopy = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
            return $textToCopy;
        }
    }

    if (!function_exists('colocaBrancos')) {
        function colocaBrancos($docFinal)
        {
            $docFinal = str_replace("%VALORES_ANOLETIVO_TITULOS_RECEBIDOS%", "    ", $docFinal);
            $docFinal = str_replace("%VALORES_ANOLETIVO_RECEBIDOS%", "    ", $docFinal);
            $docFinal = str_replace("%BOLSA_RECENTE%", "    ", $docFinal);

            $docFinal = str_replace("%BOLETIM_ELETIVAS%", "", $docFinal);

            $docFinal = str_replace("%BOLETIM_BASE_DIF%", "", $docFinal);
            $docFinal = str_replace("%BOLETIM_INGLES%", "", $docFinal);

            $docFinal = str_replace("%OBS_FUNDAMENTAL%", "", $docFinal);
            $docFinal = str_replace("%OBS_INDIVIDUAL%", "", $docFinal);
            $docFinal = str_replace("%OBS_MEDIO%", "", $docFinal);

            $docFinal = str_replace("%ALUNO_NACIONALIDADE%", "    ", $docFinal);
            $docFinal = str_replace("%ALUNO_UF_NASCIMENTO%", "    ", $docFinal);
            $docFinal = str_replace("%ALUNO_MUNICIPIO_NASCIMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_PROFISSAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_REGISTRO_MEC%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_ENDERECO_MUNICIPIO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_ENDERECO_UF%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_ENDERECO_BAIRRO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_ENDERECO_CEP%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_ENDERECO_LOGRADOURO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_ENDERECO_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_ENDERECO_COMPLEMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_NOME%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_DOCUMENTOS_ENTREGUES%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_DOCUMENTOS_FALTANTES%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNOS_LISTA_TURMA%", "", $docFinal);
            $docFinal = str_replace("%ALUNO_DATA_NASCIMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SEXO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_RACA%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_RG%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_ORGAO_EXPEDIDOR%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_RG_DATA_EXP%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_CPF%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_FILHO_FUNCIONARIO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_CONTRATO_DESEMPENHO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_DE_INCLUSAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_EST_CIV%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_PLANO_SAUDE%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_PLANO_SAUDE_TEL%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_PEDIATRA%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_PEDIATRA_TEL%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_TIPO_SANGUINEO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_RH%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_REMEDIO_FEBRE%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_ALERGIA_REMEDIO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_ALERGIA_ALIMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_EMTRATAMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_REMEDIO_REGULAR%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_SAUDE_OBSERVACOES_MEDICAS%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_TELEFONE%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_TELEFONES%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_CELULAR%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_CELULARES%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_EMAIL%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_EMAILS%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_OBSERVACAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNOS_HISTORICO%", "", $docFinal);
            $docFinal = str_replace("%ALUNOS_HISTORICO_VERTICAL%", "", $docFinal);
            $docFinal = str_replace("%EXALUNOS_ANTIGOS_HISTORICO_VERTICAL%", "", $docFinal);

            $docFinal = str_replace("%MATRICULA_SITUACAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_DATA%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_BOLSA%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_QTD_PARCELAS%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_ANUIDADE%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_ANUIDADE_LÍQUIDO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_VALOR_PARCELAS%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_VALORBRUTO_PARCELAS%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_VALORBRUTO_MENSALIDADE%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_EMPRESA%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_BANCO_BOLETO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_DIA_VENCIMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_DATA_1PARCELA%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_TABELA_TITULOS%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_PLANOHORARIO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_PRESENCIAL%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_CRIACAO%", ESPACO, $docFinal);


            $docFinal = str_replace("%MATRICULA_ANO_LETIVO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MATRICULA_ANO_LETIVO_POSTERIOR%", ESPACO, $docFinal);

            $docFinal = str_replace("%TURMA_CURSO%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_SERIE%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_PROX_CURSO%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_PROX_SERIE%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_NOME%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_ABREVIACAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_TURNO%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_UNIDADE%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_DATA_INICIO%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_DATA_TERMINO%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_DATA_ADIADA%", ESPACO, $docFinal);
            $docFinal = str_replace("%TURMA_DIAS_DA_SEMANA%", ESPACO, $docFinal);

            $docFinal = str_replace("%UNIDADE_NOME%", ESPACO, $docFinal);
            $docFinal = str_replace("%UNIDADE_TELEFONE%", ESPACO, $docFinal);
            $docFinal = str_replace("%UNIDADE_EMAIL%", ESPACO, $docFinal);
            $docFinal = str_replace("%UNIDADE_LOGRADOURO%", ESPACO, $docFinal);
            $docFinal = str_replace("%UNIDADE_NUMERO_LOGRADOURO%", ESPACO, $docFinal);
            $docFinal = str_replace("%UNIDADE_COMPLEMENTO_LOGRADOURO%", ESPACO, $docFinal);
            $docFinal = str_replace("%UNIDADE_CEP%", ESPACO, $docFinal);
            $docFinal = str_replace("%UNIDADE_BAIRRO%", ESPACO, $docFinal);
            $docFinal = str_replace("%UNIDADE_MUNICIPIO%", ESPACO, $docFinal);
            $docFinal = str_replace("%UNIDADE_ESTADO%", ESPACO, $docFinal);

            $docFinal = str_replace("%CONTRATADA_CNPJ%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATADA_RAZAO_SOCIAL%", ESPACO, $docFinal);

            $docFinal = str_replace("%RESPONSAVEIS_NOME%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEIS_CPF%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEIS_RG_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEIS_RG_DATA_EXP%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEIS_RG_ORGAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEIS_NOME_LOGIN_SENHA%", ESPACO, $docFinal);

            $docFinal = str_replace("%RESPONSAVEL_PEDAGOGICO_NOME%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_PEDAGOGICO_CPF%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_PEDAGOGICO_RG_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_PEDAGOGICO_RG_DATA_EXP%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_PEDAGOGICO_RG_ORGAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_PEDAGOGICO_NOME_LOGIN_SENHA%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_PEDAGOGICO_EMAIL%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_PEDAGOGICO_TELEFONE%", ESPACO, $docFinal);

            $docFinal = str_replace("%RESPONSAVEL_FINANCEIRO_NOME%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_FINANCEIRO_CPF%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_FINANCEIRO_RG_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_FINANCEIRO_RG_DATA_EXP%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_FINANCEIRO_RG_ORGAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_FINANCEIRO_NOME_LOGIN_SENHA%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_FINANCEIRO_EMAIL%", ESPACO, $docFinal);
            $docFinal = str_replace("%RESPONSAVEL_FINANCEIRO_TELEFONE%", ESPACO, $docFinal);

            $docFinal = str_replace("%PAI_NOME%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_FILIACAO_NOME_DO_PAI%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_DATA_NASCIMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_CPF%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_EST_CIV%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_RG_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_RG_ORGAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_RG_DATA_EXP%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_ENDERECO_LOGRADOURO%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_ENDERECO_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_ENDERECO_COMPLEMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_ENDERECO_BAIRRO%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_ENDERECO_MUNICIPIO%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_ENDERECO_UF%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_ENDERECO_CEP%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_EMAIL%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_EMAILS%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_TELEFONE%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_TELEFONES%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_CELULAR%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_CELULARES%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_NOME_LOGIN_SENHA%", ESPACO, $docFinal);
            $docFinal = str_replace("%PAI_NACIONALIDADE%", ESPACO, $docFinal);

            $docFinal = str_replace("%MAE_NOME%", ESPACO, $docFinal);
            $docFinal = str_replace("%ALUNO_FILIACAO_NOME_DA_MAE%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_DATA_NASCIMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_CPF%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_EST_CIV%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_RG_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_RG_ORGAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_RG_DATA_EXP%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_ENDERECO_LOGRADOURO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_ENDERECO_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_ENDERECO_COMPLEMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_ENDERECO_BAIRRO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_ENDERECO_MUNICIPIO%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_ENDERECO_UF%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_ENDERECO_CEP%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_EMAIL%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_EMAILS%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_TELEFONE%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_TELEFONES%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_CELULAR%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_CELULARES%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_NOME_LOGIN_SENHA%", ESPACO, $docFinal);
            $docFinal = str_replace("%MAE_NACIONALIDADE%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_NOME%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_DATA_NASCIMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_CPF%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_RG%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_RG_ORGAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_RG_DATA_EXP%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_ENDERECO_LOGRADOURO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_ENDERECO_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_ENDERECO_COMPLEMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_ENDERECO_BAIRRO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_ENDERECO_MUNICIPIO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_ENDERECO_UF%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_ENDERECO_CEP%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_PARENTESCO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_EMAIL%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_EMAILS%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_TELEFONE%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_TELEFONES%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_CELULAR%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_CELULARES%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_PROFISSAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_NACIONALIDADE%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_ESTADOCIVIL%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE_PROFISSAO%", ESPACO, $docFinal);

            $docFinal = str_replace("%CONTRATANTE2_NOME%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_DATA_NASCIMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_CPF%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_RG%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_RG_ORGAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_RG_DATA_EXP%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_ENDERECO_LOGRADOURO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_ENDERECO_NUMERO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_ENDERECO_COMPLEMENTO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_ENDERECO_BAIRRO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_ENDERECO_MUNICIPIO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_ENDERECO_UF%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_ENDERECO_CEP%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_PARENTESCO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_EMAIL%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_EMAILS%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_TELEFONE%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_TELEFONES%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_CELULAR%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_CELULARES%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_PROFISSAO%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_NACIONALIDADE%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_ESTADOCIVIL%", ESPACO, $docFinal);
            $docFinal = str_replace("%CONTRATANTE2_PROFISSAO%", ESPACO, $docFinal);

            return $docFinal;
        }
    }

    if (!function_exists('valorPorExtenso')) {
        function valorPorExtenso($valor = 0)
        {
            $rt = null;
            $singular = ["centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão"];
            $plural = ["centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões"];
            $c = ["", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos"];
            $d = ["", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa"];
            $d10 = ["dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezesete", "dezoito", "dezenove"];
            $u = ["", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove"];
            $z = 0;
            $valor = number_format($valor, 2, ".", ".");
            $inteiro = explode(".", $valor);
            for ($i = 0; $i < count($inteiro); $i++) {
                for ($ii = strlen($inteiro[$i]); $ii < 3; $ii++) {
                    $inteiro[$i] = "0" . $inteiro[$i];
                }
            }
            // $fim identifica onde que deve se dar junção de centenas por "e" ou por ","
            $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
            for ($i = 0; $i < count($inteiro); $i++) {
                $valor = $inteiro[$i];
                $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
                $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
                $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

                $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
                $t = count($inteiro) - 1 - $i;
                $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
                if ($valor == "000") {
                    $z++;
                } elseif ($z > 0) {
                    $z--;
                }
                if (($t == 1) && ($z > 0) && ($inteiro[0] > 0)) {
                    $r .= (($z > 1) ? " de " : "") . $plural[$t];
                }
                if ($r) {
                    $rt = ($rt ?? '') . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim) ? ", " : " e ") : " ") . $r;
                }
            }
            return ($rt ?: "zero");
        }
    }

    $arqOriginal = Documento::find($idDOC)->template;
    if (empty($arqOriginal)) {
        $arqOriginal = file_get_contents(__DIR__ . "/clientes/$cliente/docs/$idDOC.php");
    }

    if (!$arqOriginal && isset($email)) {
        throw new Exception("O documento não foi encontrado");
    }
    $alunoINFO = explode(",", $idalunoDOC);

    $docData = str_replace("%DATA_ATUAL%", $hoje, $arqOriginal);
    $docData = str_replace("%DATA_HORA_ATUAL%", $agora . "h", $docData);

    if (str_contains($docData, "%PAGINA_INICIAL")) {
        $posinicial = strpos($docData, "%PAGINA_INICIAL") + 15;
        $posfinal = stripos($docData, "%", $posinicial + 1);
        $tam = $posfinal - $posinicial;

        $pagina = (strlen($pagina) == 1) ? '0' . $pagina : $pagina;

        $docData = str_replace('%PAGINA_INICIAL@XX%', '%PAGINA_INICIAL@' . $pagina . '%', $docData);

        $posoriginal = strpos($docData, "%PAGINA_INICIAL");
        $tamoriginal = $posfinal + 1 - $posoriginal;

        $ninicial = substr($docData, $posinicial, $tam);
        $ninicial1 = explode("@", $ninicial);
        $pagina = $ninicial1[1];
        $docData = str_replace(substr($docData, $posoriginal, $tamoriginal), "", $docData);
    }

    // SISTEMA
    $docData = str_replace("%DATA_ANO_ATUAL%", date("Y"), $docData);
    $docData = str_replace("%DATA_ATUAL_EXTENSO%", $hojeporexenso, $docData);
    while (str_contains($docData, "%QUEBRA_PAGINA")) {
        $docData = preg_replace('/%QUEBRA_PAGINA%/', '<div style="float:right;">Página ' . $pagina . '</div><div style="page-break-after:always">&nbsp;</div>', $docData, 1);
        $pagina++;
    }

    $docData = str_replace("%USUARIO%", $nomefuncDOC, $docData);
    // SISTEMA

    $cols = 1;
    $rows = 1;

    $idaluno = "";

    $temhist = strpos($docData, "%ALUNOS_HISTORICO%");
    if ($temhist !== false) {
        foreach ($alunoINFO as $key => $idINFO) {
            $linha = '';
            $info = explode("@", $idINFO);
            $idunidade = $info[0];
            $idaluno = $info[1];
            $nummatricula = $info[2];

            $cabeca = 0;
            $query = "SELECT * FROM alunos_historico WHERE alunos_historico.idaluno=$idaluno GROUP BY ano,escola,serie DESC";
            $result = mysql_query($query);
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                if ($cabeca == 0) {
                    $linha .= '<table border="1" cellpadding="1" cellspacing="1" style="width:880px;vertical-align:top;">';
                    $linha .= '<tr><th colspan="7" style="height:60px; text-align:center; width:580px;">' . $row['nomealuno'] . '</th></tr>';
                    $linha .= '<tr><th width="20%" style="height:60px; text-align:center;" >Escola / Local</th>';
                    $linha .= '<th width="5%" style="height:60px; text-align:center;" >Ano Letivo</th>';
                    $linha .= '<th width="15%" style="height:60px; text-align:center;" >Série</th>';
                    $linha .= '<th width="20%" style="height:60px; text-align:center;" >Componentes Curriculares</th>';
                    $linha .= '<th width="10%" style="height:60px; text-align:center;" >Média Final</th>';
                    $linha .= '<th width="10%" style="height:60px; text-align:center;" >Carga Horária</th>';
                    $linha .= '<th width="10%" style="height:60px; text-align:center;" >Frequência</th>';
                    $cabeca = 1;
                }
                $linha .= '<tr><td style="height:50px;text-align:center;">' . $row['escola'] . '<br />' . $row['local'] . '</td>';
                $linha .= '<td style="height:50px;text-align:center;">' . $row['ano'] . '</td>';
                $linha .= '<td style="height:50px;text-align:center;">' . $row['serie'] . '</td><td style="height:50px;text-align:center;">';

                $query1 = "SELECT situacao, disciplina FROM alunos_historico WHERE idaluno=$idaluno AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    if ($row1['situacao'] == 1) {
                        $sit = " aprovado ";
                    } else {
                        $sit = " reprovado ";
                    }
                    $linha .= "<br />" . $row1['disciplina'] . " ($sit)<br /><br /><hr />";
                }
                $linha .= '</td><td style="height:50px;text-align:center;">';
                $query1 = "SELECT media,disciplina FROM alunos_historico WHERE idaluno=$idaluno AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    $linha .= "<br />" . $row1['media'] . "<br /><br /><hr />";
                }
                $linha .= '</td><td style="height:50px;text-align:center;">';
                $query1 = "SELECT cargahoraria,disciplina FROM alunos_historico WHERE idaluno=$idaluno AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    $linha .= "<br />" . $row1['cargahoraria'] . "h<br /><br /><hr />";
                }
                $linha .= '</td><td style="height:50px;text-align:center;">';
                $query1 = "SELECT frequencia,disciplina FROM alunos_historico WHERE idaluno=$idaluno AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    $linha .= "<br />" . $row1['frequencia'] . "<br /><br /><hr />";
                }

                $linha .= '</td></tr>';
            }

            $linha .= '</table>';
            $historicoAlunos[$key] = $linha;
        }
    }

    $temhistV = strpos($docData, "%ALUNOS_HISTORICO_VERTICAL%");
    if ($temhistV !== false) {
        foreach ($alunoINFO as $key => $idINFO) {
            $linha = '';
            $info = explode("@", $idINFO);
            $idunidade = $info[0];
            $idaluno = $info[1];
            $nummatricula = $info[2];

            $cntDisciplinas = 0;
            unset($Disc);

            $linha .= '<table border="1" cellpadding="0" cellspacing="0" style="width:880px;vertical-align:top;" class="tabelacomborda">';
            $linha .= '<thead><tr><th></th>';
            $query1 = "SELECT disciplina FROM alunos_historico WHERE alunos_historico.idaluno=$idaluno GROUP BY disciplina ORDER BY ordem ASC";
            $result1 = mysql_query($query1);
            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                $linha .= '<th><div class="rotate">' . $row1['disciplina'] . '</div></th>';
                $cntDisciplinas++;
                $Disc[] = $row1['disciplina'];
            }

            $linha .= '<th><div class="rotate">Carga Horária</div></th>';
            $linha .= '<th><div class="rotate">Frequência</div></th>';
            $linha .= '<th><div class="rotate">Resultado Final</div></th></thead><tbody>';

            $aprovado = "SIM";
            $serieAtual = "";
            $queryA = "SELECT ano FROM alunos_historico WHERE alunos_historico.idaluno=$idaluno GROUP BY ano ORDER BY ano ASC";
            $resultA = mysql_query($queryA);
            while ($rowA = mysql_fetch_array($resultA, MYSQL_ASSOC)) {
                $primeiralinha = 0;
                $notaDisc[][$rowA['ano']] = " - ";
                $proximoAno = $rowA['ano'] + 1;

                $queryB = "SELECT * FROM alunos_historico WHERE idaluno=$idaluno AND ano='" . $rowA['ano'] . "'";
                $resultB = mysql_query($queryB);
                while ($rowB = mysql_fetch_array($resultB, MYSQL_ASSOC)) {
                    $notaDisc[$rowB['disciplina']][$rowA['ano']] = $rowB['media'];
                    if ($primeiralinha == 0) {
                        $serieAtual = explode("@&@", $rowB['serie']);
                        $serieAtual = $serieAtual[1];
                        $linha .= '<tr><td rowspan="2">' . str_replace("@&@", " ", $rowB['serie']) . '</td>';
                        $linha .= '<td colspan="' . ($cntDisciplinas + 2) . '">' . $rowB['escola'] . ' - ' . $rowB['local'] . '</td><td>' . $rowB['ano'] . '</td></tr><tr>';
                    }
                    $primeiralinha = 1;
                    $cargahoraria = $rowB['cargahoraria'];
                    $frequencia = $rowB['frequencia'];
                    $situacao = $rowB['situacao'];
                    if ($situacao == "REPROVADO") {
                        $aprovado = "NÃO";
                    }
                }

                foreach ($Disc as $disciplina) {
                    $linha .= "<td>";
                    if ($notaDisc[$disciplina][$rowA['ano']]) {
                        $linha .= $notaDisc[$disciplina][$rowA['ano']];
                    } else {
                        $linha .= " - ";
                    }
                    $linha .= "</td>";
                }
                $linha .= '<td>' . $cargahoraria . 'h</td><td>' . $frequencia . '</td><td>' . substr($situacao, 0, 2) . '</td></tr>';
            }
            $linha .= '</tbody></table><br />';
            $linha .= '<table border="0" cellpadding="0" cellspacing="0" style="width:880px;vertical-align:top;">';
            $linha .= '<tr><th >OBSERVAÇÕES</th>';
            $linha .= '<tr><th style="border:1px solid #000; width:880px;"><br /><br /><br /><br /><br /></th>';
            $linha .= '</table><br />';

            $queryC = "SELECT serie, curso FROM series, cursos WHERE series.idcurso=cursos.id AND ordem = (SELECT ordem+1 FROM series WHERE serie='$serieAtual')";
            $resultC = mysql_query($queryC);
            $rowC = mysql_fetch_array($resultC, MYSQL_ASSOC);

            $linha .= '<table border="0" cellpadding="0" cellspacing="0" style="width:880px;vertical-align:top;">';
            $linha .= '<tr><th>Promovido: ' . $aprovado . '</th>';
            $linha .= '<tr><th>O(A) aluno(a) deverá ser matriculado(a) no ' . $rowC['serie'] . ' do ' . $rowC['curso'] . ' no ano letivo de ' . $proximoAno . '</th>';
            $linha .= '</table><br />';


            $historicoAlunosV[$key] = $linha;
        }
    }

    $temhistAV = strpos($docData, "%EXALUNOS_ANTIGOS_HISTORICO_VERTICAL%");
    if ($temhistAV !== false) {
        foreach ($alunoINFO as $key => $nomealuno) {
            $linha = '';

            $cntDisciplinas = 0;
            unset($Disc);

            $linha .= '<table border="1" cellpadding="0" cellspacing="0" style="width:880px;vertical-align:top;" class="tabelacomborda">';
            $linha .= '<thead><tr><th></th>';
            $query1 = "SELECT disciplina FROM alunos_historico WHERE alunos_historico.nomealuno='$nomealuno' GROUP BY disciplina ORDER BY disciplina ASC";
            $result1 = mysql_query($query1);
            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                $linha .= '<th><div class="rotate">' . $row1['disciplina'] . '</div></th>';
                $cntDisciplinas++;
                $Disc[] = $row1['disciplina'];
            }

            $linha .= '<th><div class="rotate">Carga Horária</div></th>';
            $linha .= '<th><div class="rotate">Frequência</div></th>';
            $linha .= '<th><div class="rotate">Resultado Final</div></th></thead><tbody>';

            $aprovado = "SIM";
            $serieAtual = "";
            $queryA = "SELECT ano FROM alunos_historico WHERE alunos_historico.nomealuno='$nomealuno' GROUP BY ano ORDER BY ano ASC";
            $resultA = mysql_query($queryA);
            while ($rowA = mysql_fetch_array($resultA, MYSQL_ASSOC)) {
                $primeiralinha = 0;
                $notaDisc[][$rowA['ano']] = " - ";
                $proximoAno = $rowA['ano'] + 1;

                $queryB = "SELECT * FROM alunos_historico WHERE nomealuno='$nomealuno' AND ano='" . $rowA['ano'] . "'";
                $resultB = mysql_query($queryB);
                while ($rowB = mysql_fetch_array($resultB, MYSQL_ASSOC)) {
                    $notaDisc[$rowB['disciplina']][$rowA['ano']] = $rowB['media'];
                    if ($primeiralinha == 0) {
                        $serieAtual = explode("@&@", $rowB['serie']);
                        $serieAtual = $serieAtual[1];
                        $linha .= '<tr><td rowspan="2">' . str_replace("@&@", " ", $rowB['serie']) . '</td>';
                        $linha .= '<td colspan="' . ($cntDisciplinas + 2) . '">' . $rowB['escola'] . ' - ' . $rowB['local'] . '</td><td>' . $rowB['ano'] . '</td></tr><tr>';
                    }
                    $primeiralinha = 1;
                    $cargahoraria = $rowB['cargahoraria'];
                    $frequencia = $rowB['frequencia'];
                    $situacao = $rowB['situacao'];
                    if ($situacao == "REPROVADO") {
                        $aprovado = "NÃO";
                    }
                }

                foreach ($Disc as $disciplina) {
                    $linha .= "<td>";
                    if ($notaDisc[$disciplina][$rowA['ano']]) {
                        $linha .= $notaDisc[$disciplina][$rowA['ano']];
                    } else {
                        $linha .= " - ";
                    }
                    $linha .= "</td>";
                }
                $linha .= '<td>' . $cargahoraria . 'h</td><td>' . $frequencia . '</td><td>' . substr($situacao, 0, 2) . '</td></tr>';
            }
            $linha .= '</tbody></table><br />';
            $linha .= '<table border="0" cellpadding="0" cellspacing="0" style="width:880px;vertical-align:top;">';
            $linha .= '<tr><th >OBSERVAÇÕES</th>';
            $linha .= '<tr><th style="border:1px solid #000; width:880px;"><br /><br /><br /><br /><br /></th>';
            $linha .= '</table><br />';

            $historicoAlunosAV[$key] = $linha;
        }
    }

    $renderer = new DocumentoAlunosRenderer();
    foreach ($alunoINFO as $key => $idINFO) {
        $info = explode("@", $idINFO);
        $idunidade = $info[0];
        $alunoId = $info[1];
        $ida = $info[1];
        $nummatricula = $info[2];
        $idempresa = $info[4];

        echo '<input type="hidden" id="ida" value="' . $ida . '" />';
        echo '<input type="hidden" id="idu" value="' . $idunidade . '" />';

        if (!isset($sup_rodape)) {
            $sup_rodape = copia_texto("%RODAPE_INICIO%", "%RODAPE_FIM%", $docData);
            $sup_rodape = str_replace("%RODAPE_INICIO%", "    ", $sup_rodape);
            $sup_rodape = str_replace("%RODAPE_FIM%", "    ", $sup_rodape);
        }

        $docData = delete_all_between("%RODAPE_INICIO%", "%RODAPE_FIM%", $docData);
        $docFinal = $docData;
        //$renderer->loopMatricula($docFinal, ['alunoId' => $alunoId, 'nummatricula' => $nummatricula]);
        $contador++;
        $docFinal = str_replace("%CONTADOR%", $contador, $docFinal);
        $docFinal = str_replace("%EXALUNO_NOME%", $idINFO, $docFinal);
        $tem_localnascpais = strpos($docData, "%ALUNO_NACIONALIDADE%");
        $tem_localnascuf = strpos($docData, "%ALUNO_UF_NASCIMENTO%");
        $tem_localnasc = strpos($docData, "%ALUNO_MUNICIPIO_NASCIMENTO%");

        if ($tem_localnascpais !== false || $tem_localnascuf !== false || $tem_localnasc !== false) {
            // LOCAL NASCIMENTO ALUNO
            $query = "SELECT nacionalidade, nom_pais, nom_cidade, nom_estado,sgl_estado FROM alunos JOIN pessoas ON pessoas.id = alunos.idpessoa LEFT JOIN paises ON pessoas.idpaisnascimento = paises.cod_pais LEFT JOIN estados ON pessoas.idestadonascimento = estados.id LEFT JOIN cidades ON pessoas.idcidadenascimento = cidades.id WHERE alunos.id=" . $ida;
            $result = mysql_query($query);
            $row = mysql_fetch_array($result, MYSQL_ASSOC);
            $docFinal = str_replace("%ALUNO_NACIONALIDADE%", strlen($row['nacionalidade']) ? $row['nacionalidade'] : $row['nom_pais'], $docFinal);
            $docFinal = str_replace("%ALUNO_UF_NASCIMENTO%", $row['sgl_estado'], $docFinal);
            $docFinal = str_replace("%ALUNO_MUNICIPIO_NASCIMENTO%", $row['nom_cidade'], $docFinal);
        }
        //observacoes - fabio souza

        // DADOS PESSOAIS DO ALUNO
        $query = "SELECT
                    pessoas.id as pid,alunos.id as a_id,
                    (CASE
                       WHEN pessoas.raca = 1 THEN 'Branco(a)'
                       WHEN pessoas.raca = 2 THEN 'Negro(a)'
                       WHEN pessoas.raca = 3 THEN 'Amarelo(a)'
                       WHEN pessoas.raca = 4 THEN 'Pardo(a)'
                       WHEN pessoas.raca = 5 THEN 'Indigena'
                       WHEN pessoas.raca = 6 THEN 'Não Informado'
                       WHEN pessoas.raca = Null THEN 'Não Informado'
                       ELSE 'Não Declarada'
                       END) AS raca,
                    nom_cidade,
                    nom_estado,
                    sgl_estado,
                    bairro,
                    cep,
                    logradouro,
                    numero,
                    complemento,
                    nome,
                    nome_original,
                    datanascimento,
                    DATE_FORMAT(datanascimento,'%d/%m/%Y') AS 'dtnascimento',
                    DATE_FORMAT(rg_expedido_em,'%d/%m/%Y') AS 'rg_data_expedicao',
                    sexo,
                    estadocivil,
                    rg,
                    orgaoexp,
                    cpf,
                    alunos.filho_funcionario,
                    alunos.contrato_desempenho,
                    alunos.aluno_inclusao,
                    alunos.profissao as profissao,
                    registromec,
                    alunos.numeroaluno as 'alunos_numeroaluno',
                    planosaude,
                    planosaude_tel,
                    pediatra,
                    pediatra_tel,
                    tiposanguineo,
                    observacoes,
                    fatorRH,
                    medicamentofebre,
                    alergiamedicamentos,
                    alergiaalimentos,
                    tratamentosmedicos,
                    medicamentosregulares,
                    observacoesmedicas,
                    obs_medio,
                    obs_fundamental,
                    obs_individual,
                    foto
                FROM pessoas, alunos, cidades, estados, estadocivil, sexo
                WHERE pessoas.id=alunos.idpessoa
                AND (pessoas.idestado=estados.id OR pessoas.idestado < 1)
                AND (pessoas.idcidade=cidades.id OR pessoas.idcidade < 1)
                AND (pessoas.idsexo=sexo.id OR pessoas.idsexo=0)
                AND (pessoas.idestadocivil=estadocivil.id OR pessoas.idestadocivil=0)
                AND alunos.id='$ida'
                LIMIT 1";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);

        $nome_aluno = $row['nome'];
        $nome_original = $row['nome_original'];

        // DADOS USUÁRIO DO ALUNO
        $tem_ALUNO_LOGIN = strpos($docData, "%ALUNO_LOGIN%");
        $tem_ALUNO_SENHA = strpos($docData, "%ALUNO_SENHA%");

        if ($tem_ALUNO_LOGIN !== false || $tem_ALUNO_SENHA !== false) {
            $queryUsuario = "SELECT login,senha FROM usuarios, alunos, pessoas WHERE usuarios.idpessoa=alunos.idpessoa AND usuarios.idpessoa=pessoas.id AND pessoas.id=alunos.idpessoa AND alunos.id=" . $ida;
            $resultUsuario = mysql_query($queryUsuario);
            $rowUsuario = mysql_fetch_array($resultUsuario, MYSQL_ASSOC);

            $docFinal = str_replace("%ALUNO_LOGIN%", $rowUsuario['login'], $docFinal);
            $docFinal = str_replace("%ALUNO_SENHA%", $rowUsuario['senha'], $docFinal);
        }

        echo '<input type="hidden" id="idp" value="' . $row['pid'] . '" />';

        // FOTO DO ALUNO
        if ($row['foto'] || file_exists("clientes/" . $cliente . "/fotos/" . $row['pid'] . ".png")) {
            $docFinal = str_replace(
                "images/shared/aluno.png",
                "/api/v1/perfil/{$row['pid']}/img?w=105&h=140&fit=crop",
                $docFinal
            );
        } else {
            $docFinal = str_replace("%FOTO_ALUNO%", "  foto  ", $docFinal);
        }

        $docFinal = str_replace("%ALUNO_PROFISSAO%", $row['profissao'], $docFinal);
        $docFinal = str_replace("%ALUNO_REGISTRO_MEC%", $row['registromec'], $docFinal);
        $docFinal = str_replace("%ALUNO_ENDERECO_MUNICIPIO%", $row['nom_cidade'], $docFinal);
        $docFinal = str_replace("%ALUNO_ENDERECO_UF%", $row['sgl_estado'], $docFinal);
        $docFinal = str_replace("%ALUNO_ENDERECO_BAIRRO%", $row['bairro'], $docFinal);
        $docFinal = str_replace("%ALUNO_ENDERECO_CEP%", $row['cep'], $docFinal);
        $docFinal = str_replace("%ALUNO_ENDERECO_LOGRADOURO%", $row['logradouro'], $docFinal);
        $docFinal = str_replace("%ALUNO_ENDERECO_NUMERO%", $row['numero'], $docFinal);
        $docFinal = str_replace("%ALUNO_ENDERECO_COMPLEMENTO%", $row['complemento'], $docFinal);
        $docFinal = str_replace("%ALUNO_NOME%", $row['nome'], $docFinal);
        $docFinal = str_replace("%NOME_CERTIDAO_NASCIMENTO%", $row['nome_original'], $docFinal);
        $docFinal = str_replace("%ALUNO_DATA_NASCIMENTO%", $row['dtnascimento'], $docFinal);
        $docFinal = str_replace("%ALUNO_SEXO%", $row['sexo'], $docFinal);
        $docFinal = str_replace("%ALUNO_RACA%", $row['raca'], $docFinal);
        $docFinal = str_replace("%ALUNO_RG%", $row['rg'], $docFinal);
        $docFinal = str_replace("%ALUNO_ORGAO_EXPEDIDOR%", $row['orgaoexp'], $docFinal);
        $docFinal = str_replace("%ALUNO_RG_DATA_EXP%", $row['rg_data_expedicao'], $docFinal);
        $docFinal = str_replace("%ALUNO_CPF%", $row['cpf'], $docFinal);
        $docFinal = str_replace("%ALUNO_CONTRATO_DESEMPENHO%", $row['contrato_desempenho'] ? 'SIM' : 'NÃO', $docFinal);
        $docFinal = str_replace("%ALUNO_FILHO_FUNCIONARIO%", $row['filho_funcionario'] ? 'SIM' : 'NÃO', $docFinal);
        $docFinal = str_replace("%ALUNO_DE_INCLUSAO%", $row['aluno_inclusao'] ? 'Sim' : '', $docFinal);
        $docFinal = str_replace("%ALUNO_EST_CIV%", $row['estadocivil'], $docFinal);
        $docFinal = str_replace("%ALUNO_NUMERO%", $row['alunos_numeroaluno'], $docFinal);
        $docFinal = str_replace("%ALUNO_NUMERO_BARRAS%", '<iframe frameborder="0" scrolling="no" width="260" height="70" style="padding-left:9px" src="barcode?cod=' . $row['alunos_numeroaluno'] . '"></iframe>', $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_PLANO_SAUDE%", $row['planosaude'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_PLANO_SAUDE_TEL%", $row['planosaude_tel'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_PEDIATRA%", $row['pediatra'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_PEDIATRA_TEL%", $row['pediatra_tel'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_TIPO_SANGUINEO%", $row['tiposanguineo'] == '-1' ? '' : $row['tiposanguineo'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_RH%", $row['fatorRH'] == '-1' ? '' : $row['fatorRH'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_REMEDIO_FEBRE%", $row['medicamentofebre'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_ALERGIA_REMEDIO%", $row['alergiamedicamentos'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_ALERGIA_ALIMENTO%", $row['alergiaalimentos'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_EMTRATAMENTO%", $row['tratamentosmedicos'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_REMEDIO_REGULAR%", $row['medicamentosregulares'], $docFinal);
        $docFinal = str_replace("%ALUNO_SAUDE_OBSERVACOES_MEDICAS%", $row['observacoesmedicas'], $docFinal);
        $docFinal = str_replace("%ALUNO_OBSERVACAO%", nl2br($row['observacoes']), $docFinal);
        $docFinal = str_replace("%OBS_MEDIO%", nl2br($row["obs_medio"]), $docFinal);
        $docFinal = str_replace("%OBS_FUNDAMENTAL%", nl2br($row["obs_fundamental"]), $docFinal);
        $docFinal = str_replace("%OBS_INDIVIDUAL%", nl2br($row["obs_individual"]), $docFinal);

        $docFinal = str_replace("%ALUNOS_HISTORICO%", $historicoAlunos[$key] . "%ALUNOS_HISTORICO%", $docFinal);
        $docFinal = str_replace("%ALUNOS_HISTORICO_VERTICAL%", $historicoAlunosV[$key] . "%ALUNOS_HISTORICO_VERTICAL%", $docFinal);
        $docFinal = str_replace("%EXALUNOS_ANTIGOS_HISTORICO_VERTICAL%", $historicoAlunosAV[$key] . "%EXALUNOS_ANTIGOS_HISTORICO_VERTICAL%", $docFinal);

        // TELEFONES ALUNO
        $tem_tel = strpos($docData, "%ALUNO_TELEFONE%");
        $tem_cel = strpos($docData, "%ALUNO_TELEFONES%");
        $tem_tels = strpos($docData, "%ALUNO_CELULAR%");
        $tem_cels = strpos($docData, "%ALUNO_CELULARES%");

        if ($tem_tel !== false || $tem_cel !== false || $tem_tels !== false || $tem_cels !== false) {
            $query = "SELECT telefone, idtipotel FROM alunos, telefones WHERE alunos.idpessoa=telefones.idpessoa AND alunos.id=" . $ida;
            $result = mysql_query($query);
            unset($todoscelulares);
            unset($todostelefones);
            $telefone = "";
            $celular = "";
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                if ($row['idtipotel'] == 1) {
                    $celular = $row['telefone'];
                    $todoscelulares[] = $row['telefone'];
                } else {
                    $telefone = $row['telefone'];
                    $todostelefones[] = $row['telefone'];
                }
            }
            if (is_array($todoscelulares) && count($todoscelulares) > 0) {
                $todoscelulares = implode(",", $todoscelulares);
            } else {
                $todoscelulares = "";
            }

            if (is_array($todostelefones) && count($todostelefones) > 0) {
                $todostelefones = implode(",", $todostelefones);
            } else {
                $todostelefones = "";
            }

            $docFinal = str_replace("%ALUNO_TELEFONE%", $telefone, $docFinal);
            $docFinal = str_replace("%ALUNO_TELEFONES%", $todostelefones, $docFinal);
            $docFinal = str_replace("%ALUNO_CELULAR%", $celular, $docFinal);
            $docFinal = str_replace("%ALUNO_CELULARES%", $todoscelulares, $docFinal);
        }

        // EMAILS ALUNO
        $email = '';
        $tem_email = strpos($docData, "%ALUNO_EMAIL%");
        $tem_emails = strpos($docData, "%ALUNO_EMAILS%");

        if ($tem_email !== false || $tem_emails !== false) {
            $query = "SELECT email FROM alunos, emails WHERE alunos.idpessoa=emails.idpessoa AND alunos.id=" . $ida;
            $result = mysql_query($query);
            $todosemails = [];
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $email = $row['email'];
                $todosemails[] = $row['email'];
            }
            $todosemails = implode(",", $todosemails);
            $docFinal = str_replace("%ALUNO_EMAIL%", $email, $docFinal);
            $docFinal = str_replace("%ALUNO_EMAILS%", $todosemails, $docFinal);
        }

        $tem_MATRICULA_TABELA_TITULOS = strpos($docData, "%MATRICULA_TABELA_TITULOS%");
        if ($tem_MATRICULA_TABELA_TITULOS !== false) {
            $tabelaTitMat = "<table class='tabelacomborda'><tr><td>Número do Título</td><td>Data do Vencimento</td><td>Valor</td><td>Bolsa</td><td>Valor T&iacute;tulo</td></tr>";
            $query = "SELECT titulo, valor,alunos_matriculas.bolsa,alunos_matriculas.qtdparcelas, DATE_FORMAT(datavencimento,'%d/%m/%Y') AS 'dtvencimento' FROM alunos_matriculas, alunos_fichafinanceira WHERE alunos_fichafinanceira.situacao<2 AND alunos_matriculas.idaluno=alunos_fichafinanceira.idaluno AND alunos_fichafinanceira.matricula=1 AND alunos_matriculas.nummatricula=" . $nummatricula . " AND alunos_fichafinanceira.nummatricula=" . $nummatricula;
            $result = mysql_query($query);
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $tabelaTitMat .= "<tr><td>" . $row['titulo'] . "</td><td>" . $row['dtvencimento'] . "</td><td>" . money_format('%.2n', $row['valor']) . "</td><td>" . money_format('%.2n', $row['bolsa'] / $row['qtdparcelas']) . "</td><td>" . money_format('%.2n', $row['valor'] - ($row['bolsa'] / $row['qtdparcelas'])) . "</td></tr>";
            }

            $tabelaTitMat .= "</table>";
            $docFinal = str_replace("%MATRICULA_TABELA_TITULOS%", $tabelaTitMat, $docFinal);
        }



        $temFichaDebito = str_contains($docData, "%FICHA_DEBITOS%") || str_contains($docData, "%FICHA_DEBITOS_ESPERADO%");
        $temFichaDebitoMatricula = str_contains($docData, "%FICHA_DEBITOS_MATRICULA%") || str_contains($docData, "%FICHA_DEBITOS_MATRICULA_ESPERADO%");
        $temFichaDebitoEsperado = str_contains($docData, "%FICHA_DEBITOS_ESPERADO%") || str_contains($docData, "%FICHA_DEBITOS_MATRICULA_ESPERADO%");
        if ($temFichaDebito xor $temFichaDebitoMatricula) {
            $titulosModel = Titulo::situacao('vencidos')
                ->where('idaluno', $alunoId);

            if ($temFichaDebitoMatricula) {
                $titulosModel->where('nummatricula', $nummatricula);
            }

            $titulos = $titulosModel->get();

            $tabeladeb  = '<table class="new-table table-striped prod-table" cellpadding="0" cellspacing="0" style="width: 100%;"><tr>';
            $tabeladeb .= '<th class="table-header-repeat">Vencto.</th>';
            $tabeladeb .= '<th class="table-header-repeat">Número</th>';
            $tabeladeb .= '<th class="table-header-repeat">Descrição</th>';
            $tabeladeb .= '<th class="table-header-repeat" style="width:80px;">Valor</th>';
            $tabeladeb .= '<th class="table-header-repeat">Multa</th>';
            $tabeladeb .= '<th class="table-header-repeat">Juros</th>';
            $tabeladeb .= '<th class="table-header-repeat" style="width:80px;">Valor Final</th>';
            $tabeladeb .= '</tr>';

            $valtotalprevisto = 0;
            $valtotalrecebido = 0;

            foreach ($titulos as $titulo) {
                $tabeladeb .= '<tr>';
                $tabeladeb .= '<td class="brd">' . $titulo->vencimento->locale('pt_BR')->isoFormat('L') . '</td>';
                $tabeladeb .= '<td class="brd">' . $titulo->titulo . '</td>';
                $tabeladeb .= '<td class="brd">' .  $titulo->itens->pluck('eventofinanceiro')->join('<br>') . '</td>';
                $tabeladeb .= '<td class="brd">R$ ' . number_format($temFichaDebitoEsperado ? $titulo->esperado : $titulo->valor, 2, ',', '.') . '</td>';
                $tabeladeb .= '<td class="brd">R$ ' . number_format($titulo->multaCorrente, 2, ',', '.') . '</td>';
                $tabeladeb .= '<td class="brd">R$ ' . number_format($titulo->jurosCorrente, 2, ',', '.') . '</td>';
                $tabeladeb .= '<td class="brd-ult">R$ ' . number_format($titulo->total, 2, ',', '.') . '</td>';
                $tabeladeb .= '</tr>';
            }

            $tabeladeb .= '<tr>';
            $tabeladeb .= '<td class="brd" colspan="3" style="background-color:#efefef;"><strong>Total</strong></td>';
            $tabeladeb .= '<td class="brd">R$ ' . number_format($temFichaDebitoEsperado ? $titulos->sum('esperado') : $titulos->sum('valor'), 2, ',', '.') . '</td>';
            $tabeladeb .= '<td class="brd" colspan="2" style="background-color:#efefef;">&nbsp;</td>';
            $tabeladeb .= '<td class="brd-ult">R$ ' . number_format($titulos->sum('total'), 2, ',', '.') . '</td>';
            $tabeladeb .= '</tr>';
            $tabeladeb .= '<table>';

            $docFinal = str_replace(
                [
                    '%FICHA_DEBITOS%',
                    '%FICHA_DEBITOS_MATRICULA%',
                    '%FICHA_DEBITOS_ESPERADO%',
                    '%FICHA_DEBITOS_MATRICULA_ESPERADO%',
                ],
                $tabeladeb,
                $docFinal
            );
        }

        //DADOS DE TÍTULOS DE MATRÍCULA DO ALUNO NO ANO LETIVO
        $tem_VALORES_ANOLETIVO_TITULOS_RECEBIDOS = strpos($docData, "%VALORES_ANOLETIVO_TITULOS_RECEBIDOS%");
        $tem_VALORES_ANOLETIVO_RECEBIDOS = strpos($docData, "%VALORES_ANOLETIVO_RECEBIDOS%");
        if ($tem_VALORES_ANOLETIVO_TITULOS_RECEBIDOS !== false || $tem_VALORES_ANOLETIVO_RECEBIDOS !== false) {
            $queryFin = "SELECT SUM(valor) as sumvalor, SUM(valorrecebido) as sumrecebido FROM alunos_fichafinanceira, alunos_matriculas, anoletivo WHERE (alunos_fichafinanceira.situacao=1 OR alunos_fichafinanceira.situacao=6) AND alunos_fichafinanceira.idaluno=alunos_matriculas.idaluno AND YEAR(datarecebimento)=anoletivo.anoletivo AND anoletivo.id=alunos_matriculas.anoletivomatricula AND alunos_matriculas.nummatricula=" . $nummatricula;
            $resultFin = mysql_query($queryFin);
            $rowFin = mysql_fetch_array($resultFin, MYSQL_ASSOC);
            $docFinal = str_replace("%VALORES_ANOLETIVO_TITULOS_RECEBIDOS%", money_format('%.2n', $rowFin['sumvalor']), $docFinal);
            $docFinal = str_replace("%VALORES_ANOLETIVO_RECEBIDOS%", money_format('%.2n', $rowFin['sumrecebido']), $docFinal);
        }

        //DADOS DE TÍTULOS DE MATRÍCULA DO ALUNO NO ANO LETIVO
        $tem_BOLSA_RECENTE = strpos($docData, "%BOLSA_RECENTE%");
        if ($tem_BOLSA_RECENTE !== false) {
            $mescorrente = date('m');

            $q = "SELECT aff.bolsa,aff.bolsapercentual,aff.titulo,DATE_FORMAT(aff.datavencimento,'%d/%m/%Y') AS dtvencimento,p.nome,am.nummatricula,t.turma
                            FROM alunos_fichafinanceira aff
                            INNER JOIN alunos_matriculas am ON am.nummatricula=aff.nummatricula
                            INNER JOIN alunos a ON a.id=am.idaluno
                            INNER JOIN pessoas p ON a.idpessoa=p.id
                            LEFT JOIN turmas t ON am.turmamatricula=t.id
                            WHERE aff.nummatricula=" . $nummatricula . "
                            AND MONTH(aff.datavencimento) <= '" . $mescorrente . "'
                            AND (aff.bolsa>0 OR aff.bolsapercentual>0)
                            AND aff.situacao in (0,1,6)
                            ORDER BY aff.datavencimento DESC LIMIT 1";

            $rf = mysql_query($q);
            $r = mysql_fetch_array($rf, MYSQL_ASSOC);

            if (mysql_num_rows($rf) > 0 && $r['bolsapercentual'] > 0) {
                $output =  $r['bolsapercentual'];
            } else {
                $output = '';
            }

            $docFinal = str_replace("%BOLSA_RECENTE%", $output, $docFinal);
        }

        //DADOS DE MATRÍCULA E TURMA DO ALUNO  **********************************
        $tem_PLANOHORARIO = strpos($docData, "%MATRICULA_PLANOHORARIO");
        if ($tem_PLANOHORARIO !== false) {
            $qph = "SELECT am.idplanohorario,p.codigo FROM alunos_matriculas am INNER JOIN planohorarios p  ON am.idplanohorario=p.id  where am.nummatricula=" . $nummatricula . " AND am.idunidade=" . $idunidade;
            $rph = mysql_query($qph);
            $rowp = mysql_fetch_array($rph, MYSQL_ASSOC);
            $docFinal = str_replace("%MATRICULA_PLANOHORARIO%", $rowp['codigo'], $docFinal);
        }

        $tem_MATRICULA0 = strpos($docData, "%MATRICULA_");
        $tem_MATRICULA1 = strpos($docData, "%ALUNO_NUMERO");
        $tem_TURMA0 = strpos($docData, "%TURMA_");
        $tem_UNIDADE0 = strpos($docData, "%UNIDADE_");
        $tem_CONTRATADA0 = strpos($docData, "%CONTRATADA_");

        if ($tem_MATRICULA0 !== false || $tem_MATRICULA1 !== false || $tem_TURMA0 !== false || $tem_UNIDADE0 !== false || $tem_CONTRATADA0 !== false) {
            $query = "SELECT
                        alunos_matriculas.presencial,
                        alunos_matriculas.bolsa as matbolsa,
                        alunos_matriculas.turmamatricula,
                        alunos_matriculas.status,
                        alunos_matriculas.qtdparcelas as matqtdparcelas,
                        alunos_matriculas.valorAnuidade as matvalorAnuidade,
                        anoletivo,
                        anoletivo.id as anoid,
                        turno,
                        turmas.turma,
                        series.serie as serie,
                        cursos.curso as curso,
                        unidade,
                        unidades.telefone as unitel,
                        unidades.email as uniemail,
                        unidades.endereco as uniendereco,
                        unidades.numero as uninumero,
                        unidades.complemento as unicomplemento,
                        unidades.bairro as unibairro,
                        unidades.cidade as unicidade,
                        unidades.uf as uniuf,
                        unidades.cep as unicep,
                        razaosocial,
                        cnpj,
                        nummatricula,
                        DATE_FORMAT(datamatricula,'%d/%m/%Y') AS 'dtmatricula',
                        DATE_FORMAT(datastatus,'%d/%m/%Y') AS 'dtstatus',
                        DATE_FORMAT(turmas.iniciada_em,'%d/%m/%Y') as iniciada_em,
                        DATE_FORMAT(turmas.terminada_em,'%d/%m/%Y') as terminada_em,
                        DATE_FORMAT(turmas.adiada_para,'%d/%m/%Y') as adiada_para,
                        turmas.dias_da_semana
                    FROM
                        alunos_matriculas,
                        anoletivo,
                        turnos,
                        turmas,
                        series,
                        cursos,
                        unidades,
                        empresas,
                        unidades_empresas
                    WHERE alunos_matriculas.anoletivomatricula = anoletivo.id
                    AND turmas.idturno = turnos.id
                    AND alunos_matriculas.idaluno = '$ida'
                    AND alunos_matriculas.turmamatricula = turmas.id
                    AND turmas.idserie = series.id
                    AND series.idcurso = cursos.id
                    AND cursos.idunidade = unidades.id
                    AND unidades_empresas.idempresa = empresas.id
                    AND unidades_empresas.idunidade = unidades.id
                    AND alunos_matriculas.nummatricula = '$nummatricula'
                    AND alunos_matriculas.idunidade = '$idunidade'
                    AND unidades_empresas.idempresa = '$idempresa'";
            $result = mysql_query($query);
            $row = mysql_fetch_array($result, MYSQL_ASSOC);

            $turmamatricula = $row['turmamatricula'];

            $queryMAT = "SELECT DAY(data1parcela) as diavcto, DATE_FORMAT(data1parcela,'%d/%m/%Y') AS 'vctoprimeira' FROM alunos_fichafinanceira WHERE matricula=1 AND nummatricula=" . $row['nummatricula'] . " AND idaluno=" . $ida . " LIMIT 1";
            $resultMAT = mysql_query($queryMAT);
            $rowMAT = mysql_fetch_array($resultMAT, MYSQL_ASSOC);

            $docFinal = str_replace("%MATRICULA_DIA_VENCIMENTO%", $rowMAT['diavcto'], $docFinal);
            $docFinal = str_replace("%MATRICULA_DATA_1PARCELA%", $rowMAT['vctoprimeira'], $docFinal);

            $docFinal = str_replace("%TURMA_NOME%", $row['turma'], $docFinal);
            $docFinal = str_replace("%TURMA_ABREVIACAO%", $row['alergiaalimentos'], $docFinal);
            $docFinal = str_replace("%TURMA_TURNO%", $row['turno'], $docFinal);
            $docFinal = str_replace("%TURMA_UNIDADE%", $row['unidade'], $docFinal);
            $docFinal = str_replace("%TURMA_DATA_INICIO%", $row['iniciada_em'], $docFinal);
            $docFinal = str_replace("%TURMA_DATA_TERMINO%", $row['terminada_em'], $docFinal);
            $docFinal = str_replace("%TURMA_DATA_ADIADA%", $row['adiada_para'], $docFinal);
            $docFinal = str_replace("%TURMA_DIAS_DA_SEMANA%", '<span class="diasdasemana">' . $row['dias_da_semana'] . '</span>', $docFinal);
            $docFinal = str_replace("%MATRICULA_ANO_LETIVO%", $row['anoletivo'], $docFinal);
            $docFinal = str_replace("%MATRICULA_ANO_LETIVO_POSTERIOR%", 1 + $row['anoletivo'], $docFinal);

            $idanoletivo = $row['anoid'];

            $docFinal = str_replace("%UNIDADE_NOME%", $row['unidade'], $docFinal);
            $docFinal = str_replace("%UNIDADE_TELEFONE%", $row['unitel'], $docFinal);
            $docFinal = str_replace("%UNIDADE_EMAIL%", $row['uniemail'], $docFinal);
            $docFinal = str_replace("%UNIDADE_LOGRADOURO%", $row['uniendereco'], $docFinal);
            $docFinal = str_replace("%UNIDADE_NUMERO_LOGRADOURO%", $row['uninumero'], $docFinal);
            $docFinal = str_replace("%UNIDADE_COMPLEMENTO_LOGRADOURO%", $row['unicomplemento'], $docFinal);
            $docFinal = str_replace("%UNIDADE_CEP%", $row['unicep'], $docFinal);
            $docFinal = str_replace("%UNIDADE_BAIRRO%", $row['unibairro'], $docFinal);
            $docFinal = str_replace("%UNIDADE_MUNICIPIO%", $row['unicidade'], $docFinal);
            $docFinal = str_replace("%UNIDADE_ESTADO%", $row['uniuf'], $docFinal);

            $docFinal = str_replace("%CONTRATADA_CNPJ%", $row['cnpj'], $docFinal);
            $docFinal = str_replace("%CONTRATADA_RAZAO_SOCIAL%", $row['razaosocial'], $docFinal);
        }

        $tem_MATRICULA_CRIACAO = strpos($docData, "%MATRICULA_CRIACAO%");
        if ($tem_MATRICULA_CRIACAO !== false) {
            $qmc = "SELECT p.nome FROM alunos_matriculas am
                                    INNER JOIN funcionarios f ON f.id=am.idfuncionario
                                    INNER JOIN pessoas p ON f.idpessoa=p.id where am.nummatricula=" . $nummatricula;
            $rmc = mysql_query($qmc);
            $rowMc = mysql_fetch_array($rmc, MYSQL_ASSOC);

            $matDets  = "<table border='0' width='100%' style='border:1px solid #dedede'>";
            $matDets .= "<tr><td style='width:45%'>" . $rowMc['nome'] . "</td></tr>";
            $matDets .= "</table>";

            $docFinal = str_replace("%MATRICULA_CRIACAO%", $matDets, $docFinal);
        }

        // RESPONSAVEIS
        $temVariavel = fn($variavel) => str_contains($docData, (string) $variavel);
        $usaVariaveisDeResponsavel = !empty(array_filter([
            '%PAI_NOME_LOGIN_SENHA%',
            '%MAE_NOME_LOGIN_SENHA%',
            '%RESPONSAVEIS_NOME%',
            '%RESPONSAVEIS_RG_NUMERO%',
            '%RESPONSAVEIS_RG_ORGAO%',
            '%RESPONSAVEIS_RG_DATA_EXP%',
            '%RESPONSAVEIS_CPF%',
            '%RESPONSAVEIS_NOME_LOGIN_SENHA%',
            '%RESPONSAVEIS_ESTADO%',
            '%RESPONSAVEIS_NACIONALIDADE%',
            '%RESPONSAVEIS_NASCIMENTO%',
            '%RESPONSAVEIS_PARENTESCO%',
            '%RESPONSAVEIS_ESTADO_CIVIL%',
            '%RESPONSAVEIS_PROFISSAO%',
            '%RESPONSAVEIS_EMPRESA%',
            '%RESPONSAVEIS_CELULAR%',
            '%RESPONSAVEIS_CEP%',
            '%RESPONSAVEIS_CIDADE%',
            '%RESPONSAVEIS_ENDERECO%',
        ], $temVariavel));

        if ($usaVariaveisDeResponsavel) {
            $encloseRow = fn(?string $data) => "<tr><td style='text-align:center;padding:2px 0px;'><div style='overflow: hidden; word-wrap: break-word;'>" . $data ?? '' . "</div></td></tr>";
            $encloseTable = fn(array $data) => "<table border='0' width='100%'> " . join('', array_map($encloseRow, $data)) . " </table>";
            $presentDate = fn(?string $data) => !empty($data) && $data != '0000-00-00' ? Carbon::parse($data)->locale('pt_BR')->isoFormat('L') : '';

            $responsaveis = Responsavel::with('pessoa')->where('idaluno', $ida)->get();
            foreach ($responsaveis as $responsavel) {
                $respData['NOME'][] = $responsavel->pessoa->nome;
                $respData['NASCIMENTO'][] = $presentDate($responsavel->pessoa->datanascimento);
                $respData['PARENTESCO'][] = $responsavel->parentesco->parentesco;
                $respData['ESTADO_CIVIL'][] = $responsavel->pessoa->estadocivil;
                $respData['NACIONALIDADE'][] = $responsavel->pessoa->paisNascimento->nacionalidade;
                $respData['PROFISSAO'][] = $responsavel->pessoa->profissao;
                $respData['EMPRESA'][] = $responsavel->pessoa->empresa;
                $respData['CELULAR'][] = $responsavel->pessoa->celulares->first();
                $respData['RG_NUMERO'][] = $responsavel->pessoa->rg;
                $respData['RG_DATA_EXP'][] = $presentDate($responsavel->pessoa->rg_expedido_em);
                $respData['RG_ORGAO'][] = $responsavel->pessoa->orgaoexp;
                $respData['CPF'][] = $responsavel->pessoa->cpf;

                $respData['CEP'][] = $responsavel->pessoa->cep;
                $respData['ESTADO'][] = $responsavel->pessoa->estado->sgl_estado;
                $respData['CIDADE'][] = $responsavel->pessoa->cidade;
                $respData['ENDERECO'][] = $responsavel->pessoa->endereco;
            }

            foreach ($respData as $key => $var) {
                $docFinal = str_replace("%RESPONSAVEIS_$key%", $encloseTable($var ?? ''), $docFinal);
            }
            $respData = [];

            $loginResp = "<table border='0' width='90%' style='margin-left:auto;margin-right:auto;'>";
            $loginResp .= "<tr><td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Nome</div></td>";
            $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Login</div></td>";
            $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Senha</div></td>";
            $loginResp .= "</tr>";

            $queryRP = "SELECT nome, login, senha FROM pessoas, responsaveis, usuarios WHERE responsaveis.idpessoa=pessoas.id AND responsaveis.idpessoa=usuarios.idpessoa AND responsaveis.idaluno=" . $ida . " GROUP BY nome ORDER BY nome ASC ";
            $resultRP = mysql_query($queryRP);
            while ($rowRP = mysql_fetch_array($resultRP, MYSQL_ASSOC)) {
                $loginResp .= "<tr><td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowRP['nome'] . "</div></td>";
                $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowRP['login'] . "</div></td>";
                $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowRP['senha'] . "</div></td>";
                $loginResp .= "</tr>";
            }
            $loginResp .= "</table>";


            $loginPai = "<table border='0' width='90%' style='margin-left:auto;margin-right:auto;'>";
            $loginPai .= "<tr><td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Nome</div></td>";
            $loginPai .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Login</div></td>";
            $loginPai .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Senha</div></td>";
            $loginPai .= "</tr>";

            $queryPL = "SELECT nome, login, senha FROM pessoas, responsaveis, usuarios WHERE responsaveis.idpessoa=pessoas.id AND responsaveis.idpessoa=usuarios.idpessoa AND responsaveis.idaluno=" . $ida . " AND responsaveis.idparentesco=1 GROUP BY nome ORDER BY nome ASC ";
            $resultPL = mysql_query($queryPL);
            while ($rowPL = mysql_fetch_array($resultPL, MYSQL_ASSOC)) {
                $loginPai .= "<tr><td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowPL['nome'] . "</div></td>";
                $loginPai .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowPL['login'] . "</div></td>";
                $loginPai .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowPL['senha'] . "</div></td>";
                $loginPai .= "</tr>";
            }
            $loginPai .= "</table>";

            $loginMae = "<table border='0' width='90%' style='margin-left:auto;margin-right:auto;'>";
            $loginMae .= "<tr><td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Nome</div></td>";
            $loginMae .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Login</div></td>";
            $loginMae .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Senha</div></td>";
            $loginMae .= "</tr>";

            $queryML = "SELECT nome, login, senha FROM pessoas, responsaveis, usuarios WHERE responsaveis.idpessoa=pessoas.id AND responsaveis.idpessoa=usuarios.idpessoa AND responsaveis.idaluno=" . $ida . " AND responsaveis.idparentesco=2 GROUP BY nome ORDER BY nome ASC ";
            $resultML = mysql_query($queryML);
            while ($rowML = mysql_fetch_array($resultML, MYSQL_ASSOC)) {
                $loginMae .= "<tr><td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowML['nome'] . "</div></td>";
                $loginMae .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowML['login'] . "</div></td>";
                $loginMae .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowML['senha'] . "</div></td>";
                $loginMae .= "</tr>";
            }
            $loginMae .= "</table>";
            $docFinal = str_replace("%RESPONSAVEIS_NOME_LOGIN_SENHA%", $loginResp, $docFinal);
            $docFinal = str_replace("%PAI_NOME_LOGIN_SENHA%", $loginPai, $docFinal);
            $docFinal = str_replace("%MAE_NOME_LOGIN_SENHA%", $loginMae, $docFinal);
        }

        $tem_ALUNO_DOCUMENTOS_ENTREGUES = strpos($docData, "%ALUNO_DOCUMENTOS_ENTREGUES%");
        $tem_ALUNO_DOCUMENTOS_FALTANTES = strpos($docData, "%ALUNO_DOCUMENTOS_FALTANTES%");

        if ($tem_ALUNO_DOCUMENTOS_ENTREGUES !== false || $tem_ALUNO_DOCUMENTOS_FALTANTES !== false) {
            // MATRICULA - DOCUMENTOS ENTREGUES
            $query = "SELECT  documento FROM alunos_documentos, documentos WHERE alunos_documentos.iddocumento=documentos.id AND alunos_documentos.idaluno=" . $ida;
            $result = mysql_query($query);
            $docsEntregues = [];
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $docsEntregues[] = $row['documento'];
            }
            $docsEntregues = implode(",", $docsEntregues);
            $docFinal = str_replace("%ALUNO_DOCUMENTOS_ENTREGUES%", $docsEntregues, $docFinal);

            // MATRICULA - DOCUMENTOS FALTANTES
            $query = <<<QUERY
                        SELECT distinct(d.documento)
                        FROM documentos d
                        WHERE
                        (
                            d.idserie = 0
                            OR
                            d.idserie IN
                            (
                                SELECT idserie FROM turmas WHERE id IN
                                (
                                    SELECT turmamatricula FROM alunos_matriculas where idaluno = {$ida} and nummatricula = {$nummatricula}
                                )
                            )
                        )
                        AND d.id NOT IN
                        (
                            SELECT iddocumento
                            FROM alunos_documentos ad
                            WHERE ad.idaluno = {$ida}
                        )
                        ORDER BY documento ASC
                        QUERY;
            $result = mysql_query($query);
            $docsFaltantes = [];
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $docsFaltantes[] = $row['documento'];
            }
            $docsFaltantes = implode(",", $docsFaltantes);
            $docFinal = str_replace("%ALUNO_DOCUMENTOS_FALTANTES%", $docsFaltantes, $docFinal);
        }

        //BOLETIM
        $mostraAvaliacao = 0;
        if (strpos($docFinal, "%BOLETIM_AVALIACAO%") > -1) {
            $docFinal = str_replace("%BOLETIM_AVALIACAO%", "", $docFinal);
            $mostraAvaliacao = 1;
        }

        if (strpos($docFinal, "%BOLETIM%") > -1) {
            $docFinal = str_replace("%BOLETIM%", "<span id='BOLETIM" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%BOLETIM_MODELO2%") > -1) {
            $docFinal = str_replace("%BOLETIM_MODELO2%", "<span id='BOLETIM_MODELO2_" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%BOLETIM_CETS%") > -1) {
            $docFinal = str_replace("%BOLETIM_CETS%", "<span id='BOLETIM_CETS_" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%BOLETIM_CETS_MES%") > -1) {
            $docFinal = str_replace("%BOLETIM_CETS_MES%", "<span id='BOLETIM_CETS_MES_" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%BOLETIM_CETS_FUNDAMENTAL%") > -1) {
            $docFinal = str_replace("%BOLETIM_CETS_FUNDAMENTAL%", "<span id='BOLETIM_CETS_FUNDAMENTAL_" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO%") > -1) {
            $docFinal = str_replace("%MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO%", "<span id='MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO_" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO%") > -1) {
            $docFinal = str_replace("%MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO%", "<span id='MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO_" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%BOLETIM_FALTAS_DISCIPLINA%") > -1) {
            $docFinal = str_replace("%BOLETIM_FALTAS_DISCIPLINA%", "<span id='BOLETIMFD" . $ida . "'>Carregando o boletim com faltas...</span>", $docFinal);
        }
        if (strpos($docFinal, "%BOLETIM_INGLES%") > -1) {
            $docFinal = str_replace("%BOLETIM_INGLES%", "<span id='BOLETIMENG" . $ida . "'>Carregando o boletim de Inglês...</span>", $docFinal);
        }
        if (strpos($docFinal, "%BOLETIM_PONTOS%") > -1) {
            $docFinal = str_replace("%BOLETIM_PONTOS%", "<span id='BOLETIM_PONTOS" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%BOLETIM_PONTOSPF%") > -1) {
            $docFinal = str_replace("%BOLETIM_PONTOSPF%", "<span id='BOLETIM_PONTOSPF" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%PONTOS4BI%") > -1) {
            $docFinal = str_replace("%PONTOS4BI%", "<span id='PONTOS4BI" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%PONTOSPF%") > -1) {
            $docFinal = str_replace("%PONTOSPF%", "<span id='PONTOSPF" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%BOLETIM_PONTOSPF_NOTA%") > -1) {
            $docFinal = str_replace("%BOLETIM_PONTOSPF_NOTA%", "<span id='BOLETIM_PONTOSPF_NOTA" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }
        if (strpos($docFinal, "%BOLETIM_BASE_DIF%") > -1) {
            $docFinal = str_replace("%BOLETIM_BASE_DIF%", "<span id='BOLETIM_BASE_DIF" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_FALTAS_DISCIPLINA_") > -1) {
            preg_match('~BOLETIM_FALTAS_DISCIPLINA_(.*?)%~', $docFinal, $output);
            $docFinal = str_replace("%BOLETIM_FALTAS_DISCIPLINA_" . $output[1] . "%", "<input type='hidden' id='mesboletim_faltas_disciplinas' name='mesboletim_faltas_disciplinas' value='" . $output[1] . "'> <span id='BOLETIMFDX" . $ida . "'>Carregando o boletim com faltas...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_FALTAS_DISCIPLINA_CE_") > -1) {
            preg_match('~BOLETIM_FALTAS_DISCIPLINA_CE_(.*?)%~', $docFinal, $output);
            $docFinal = str_replace("%BOLETIM_FALTAS_DISCIPLINA_CE_" . $output[1] . "%", "<input type='hidden' id='mesboletim_faltas_disciplinas_ce' name='mesboletim_faltas_disciplinas_ce' value='" . $output[1] . "'> <span id='BOLETIMFDXCE" . $ida . "'>Carregando o boletim com faltas...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM2_FALTAS_DISCIPLINA_LIMITEREC_") > -1) {
            preg_match('~BOLETIM2_FALTAS_DISCIPLINA_LIMITEREC_(.*?)%~', $docFinal, $output);
            $docFinal = str_replace("%BOLETIM2_FALTAS_DISCIPLINA_LIMITEREC_" . $output[1] . "%", "<input type='hidden' id='mesboletim_faltas_disciplinas_limiterec' name='mesboletim_faltas_disciplinas_limiterec' value='" . $output[1] . "'> <span id='BOLETIMFDXLIM" . $ida . "'>Carregando o boletim com faltas...</span>", $docFinal);
        }

        if (strpos($docFinal, "%ALUNO_RELATORIO_AVALIACAO") > -1) {
            $posinicial = strpos($docFinal, "%ALUNO_RELATORIO_AVALIACAO") + 27;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%ALUNO_RELATORIO_AVALIACAO");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $params = explode("@", substr($docFinal, $posinicial, $tam));
            $colunaboletim = $params[0];
            $aluno_avaliacao_ordenacao = $params[1];
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='ALUNO_RELATORIO_AVALIACAO" . $ida . "'>Carregando o relatório de avaliação...</span>", $docFinal);
        }

        $databoletim = "00";

        if (strpos($docFinal, "%MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO") > -1) {
            $posinicial = strpos($docFinal, "%MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO") + 12;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO_" . $ida . "'>Carregando valor bolsa percentual...</span>", $docFinal);
        }

        if (strpos($docFinal, "%MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO") > -1) {
            $posinicial = strpos($docFinal, "%MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO") + 12;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO_" . $ida . "'>Carregando valor da mensalidade...</span>", $docFinal);
        }

        if (strpos($docFinal, "%MATRICULA_VALORBRUTO_EVENTO") > -1) {
            preg_match("/%MATRICULA_VALORBRUTO_EVENTO(\d{8})_MES_ANO(\d{2})_(\d{4})%/", $docFinal, $matches);

            if (!empty($matches)) {
                $evento = $matches[1];
                $month = $matches[2];
                $year = $matches[3];

                $docFinal = str_replace($matches[0], "<span id='MATRICULA_VALORBRUTO_EVENTO_" . $ida . "'>Carregando valor do evento...</span>", $docFinal);
            }
        }

        if (strpos($docFinal, "%BOLETIM_ELETIVAS%") > -1) {
            $docFinal = str_replace("%BOLETIM_ELETIVAS%", "<span id='BOLETIMELET" . $ida . "'>Carregando o boletim de Eletivas...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_DIVERSIFICADA") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_DIVERSIFICADA") + 22;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_DIVERSIFICADA");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletimdiversificada = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIM_DIVERSIFICADA" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_GERAL") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_GERAL") + 13;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_GERAL");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletimdiversificada = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIM_GERAL" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_EVOLUCAO_GERAL") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_EVOLUCAO_GERAL") + 13;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_EVOLUCAO_GERAL");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletimdiversificada = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIM_EVOLUCAO_GERAL" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_MES") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_MES") + 12;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_MES");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIM" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_CETS_MES") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_CETS_MES") + 12;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_CETS_MES");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIM_CETS_MES_" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_CETS_FALTAS") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_CETS_FALTAS") + 12;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_CETS_FALTAS");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIM_CETS_FALTAS_" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_CETS_FUNDAMENTAL") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_CETS_FUNDAMENTAL") + 12;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_CETS_FUNDAMENTAL");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIM_CETS_FUNDAMENTAL_" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_V2_MES") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_V2_MES") + strlen("%BOLETIM_V2_MES");
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_V2_MES");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIM_V2_" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_CE_MES") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_CE_MES") + 15;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_CE_MES");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIMCE" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIMV_MES") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIMV_MES") + 13;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIMV_MES");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIMVERMELHO" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIMV_BIM_MES") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIMV_BIM_MES") + strlen("%BOLETIMV_BIM_MES");
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIMV_BIM_MES");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIMVERMELHO" . $ida . "' data-tipo-periodo='bimestre'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIMV_TRI_MES") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIMV_TRI_MES") + strlen("%BOLETIMV_TRI_MES");
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIMV_TRI_MES");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIMVERMELHO" . $ida . "' data-tipo-periodo='trimestre'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_PADRAO") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_PADRAO") + 15;
            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_PADRAO");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIMPADRAO" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%BOLETIM_FALTAS_MES") > -1) {
            $posinicial = strpos($docFinal, "%BOLETIM_FALTAS_MES") + 18;

            $posfinal = stripos($docFinal, "%", $posinicial + 1);
            $tam = $posfinal - $posinicial;
            $posoriginal = strpos($docFinal, "%BOLETIM_FALTAS_MES");
            $tamoriginal = $posfinal + 1 - $posoriginal;
            $databoletim = substr($docFinal, $posinicial, $tam);
            $docFinal = str_replace(substr($docFinal, $posoriginal, $tamoriginal), "<span id='BOLETIM_FALTAS" . $ida . "'>Carregando o boletim...</span>", $docFinal);
        }

        if (strpos($docFinal, "%INFORME_RENDIMENTOS%") > -1) {
            $docFinal = str_replace("%INFORME_RENDIMENTOS%", "<span id='INFORME_RENDIMENTOS" . $ida . "'>Carregando informe...</span>", $docFinal);
        }

        if (strpos($docFinal, "%INFORME_RENDIMENTOS_DATA_RECEBIMENTO%") > -1) {
            $docFinal = str_replace("%INFORME_RENDIMENTOS_DATA_RECEBIMENTO%", "<span id='INFORME_RENDIMENTOS_DATA_RECEBIMENTO" . $ida . "'>Carregando informe...</span>", $docFinal);
        }

        //FICHA INDIVIDUAL DO ALUNO
        if (strpos($docFinal, "%ALUNO_FICHA_INDIVIDUAL%") > -1) {
            $docFinal = str_replace("%ALUNO_FICHA_INDIVIDUAL%", "<span id='ALUNO_FICHA_INDIVIDUAL" . $ida . "'></span>", $docFinal);
        }

        $base_dif = strpos($docFinal, 'BASE_DIF');
        $mostra_base_dif = ($base_dif === false) ? '0' : '1';
        $docFinal = colocaBrancos($docFinal);

        $docData = delete_all_between("%INICIO_UNICO%", "%FIM_UNICO%", $docData);
        $docFinal = str_replace("%INICIO_UNICO%", "    ", $docFinal);
        $docFinal = str_replace("%FIM_UNICO%", "    ", $docFinal);

        echo $tabelaboletim . $docFinal;

        if ($key < count($alunoINFO) - 1) {
            if ($cols == $colunas) {
                $cols = 1;
                if ($rows == $linhas) {
                    echo '<div style="float:right;">Página ' . $pagina . '</div><div style="page-break-after:always">&nbsp;</div>';
                    $rows = 1;
                } else {
                    $rows++;
                }
            } else {
                $cols++;
            }
        }
        ?>

        <script>
            $(naturalizaDiasDaSemana())

            <?php if ($idanoletivo) { ?>
                $(document).ready(function() {
                    if ($("#ALUNO_FICHA_INDIVIDUAL<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_fichaindividual.php",
                            type: "POST",
                            context: $("#ALUNO_FICHA_INDIVIDUAL<?= $ida ?>"),
                            data: {
                                idaluno: <?= $ida ?>,
                                idanoletivo: <?= $idanoletivo ?>
                            },
                            success: function(data) {
                                $("#ALUNO_FICHA_INDIVIDUAL<?= $ida ?>").html(data);
                            }
                        });
                    }

                    if ($("#BOLETIMFD<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_faltas.php",
                            type: "POST",
                            context: $("#BOLETIMFD<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>'
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIMFDX<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_faltas_x.php",
                            type: "POST",
                            context: $("#BOLETIMFDX<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                mesboletim_faltas_disciplinas: $("#mesboletim_faltas_disciplinas").val()
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIMFDXCE<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_faltas_x_ce.php",
                            type: "POST",
                            context: $("#BOLETIMFDXCE<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                mesboletim_faltas_disciplinas: $("#mesboletim_faltas_disciplinas").val()
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIMFDXLIM<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_faltas_x_limite_apr.php",
                            type: "POST",
                            context: $("#BOLETIMFDXLIM<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                mesboletim_faltas_disciplinas: $("#mesboletim_faltas_disciplinas_limiterec").val()
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIMENG<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_ingles.php",
                            type: "POST",
                            context: $("#BOLETIMENG<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>'
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIMELET<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_eletivas.php",
                            type: "POST",
                            context: $("#BOLETIMELET<?= $ida ?>"),
                            data: {
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                databoletim: "<?= $databoletim ?>",
                                comfaltas: 0
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "bolsas-mes-ano",
                            type: "POST",
                            context: $("#MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO_<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                'geraBoletim': 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "mensalidades-mes-ano",
                            type: "POST",
                            context: $("#MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO_<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                'geraBoletim': 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim.php",
                            type: "POST",
                            context: $("#BOLETIM<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                'geraBoletim': 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_V2_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim.php",
                            type: "POST",
                            context: $("#BOLETIM_V2_<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                geraBoletim: 1,
                                usaPeriodosPorCursos: true
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_CETS_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_cets.php",
                            type: "POST",
                            context: $("#BOLETIM_CETS_<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                geraBoletim: 1,
                                usaPeriodosPorCursos: true
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_CETS_MES_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_cets_mes.php",
                            type: "POST",
                            context: $("#BOLETIM_CETS_MES_<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                'geraBoletim': 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_CETS_FALTAS_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_cets_faltas.php",
                            type: "POST",
                            context: $("#BOLETIM_CETS_FALTAS_<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                'geraBoletim': 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_CETS_FUNDAMENTAL_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_cets_fundamental.php",
                            type: "POST",
                            context: $("#BOLETIM_CETS_FUNDAMENTAL_<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                'geraBoletim': 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIMCE<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_ce.php",
                            type: "POST",
                            context: $("#BOLETIMCE<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                'geraBoletim': 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIMVERMELHO<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_vermelho.php",
                            type: "POST",
                            context: $("#BOLETIMVERMELHO<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                tipoPeriodo: $("#BOLETIMVERMELHO<?= $ida ?>").data('tipoPeriodo')
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_GERAL<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_geral.php",
                            type: "POST",
                            context: $("#BOLETIM_GERAL<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>'
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_EVOLUCAO_GERAL<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_evolucao_geral.php",
                            type: "POST",
                            context: $("#BOLETIM_EVOLUCAO_GERAL<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>'
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_MODELO2_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_modelo2.php",
                            type: "POST",
                            context: $("#BOLETIM_MODELO2_<?= $ida ?>"),
                            data: {
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_CETS_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_cets.php",
                            type: "POST",
                            context: $("#BOLETIM_CETS_<?= $ida ?>"),
                            data: {
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIMPADRAO<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_padrao.php",
                            type: "POST",
                            context: $("#BOLETIMPADRAO<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_FALTAS<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim.php",
                            type: "POST",
                            context: $("#BOLETIM_FALTAS<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                pontosparaPF: "0",
                                mostraAvaliacao: '<?= $mostraAvaliacao ?>',
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 1,
                                base_dif: '<?= $mostra_base_dif ?>'
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_BASE_DIF<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_diversificada.php",
                            type: "POST",
                            context: $("#BOLETIM_BASE_DIF<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0
                            },
                            success: function(data) {
                                this.html(data)
                            }
                        });
                    }

                    if ($("#BOLETIM_DIVERSIFICADA<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_diversidicada.php",
                            type: "POST",
                            context: $("#BOLETIM_DIVERSIFICADA<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletimdiversificada ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>'
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_PONTOS<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim.php",
                            type: "POST",
                            context: $("#BOLETIM_PONTOS<?= $ida ?>"),
                            data: {
                                mostrapontos: "1",
                                pontosparaPF: "0",
                                mostraAvaliacao: '<?= $mostraAvaliacao ?>',
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>'
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_PONTOSPF<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim.php",
                            type: "POST",
                            context: $("#BOLETIM_PONTOSPF<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                pontosparaPF: "1",
                                mostraAvaliacao: '<?= $mostraAvaliacao ?>',
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>'
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#PONTOS4BI<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_pontos.php",
                            type: "POST",
                            context: $("#PONTOS4BI<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                pontosparaPF: "1",
                                mostraAvaliacao: '<?= $mostraAvaliacao ?>',
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#PONTOSPF<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_pontospf.php",
                            type: "POST",
                            context: $("#PONTOSPF<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                pontosparaPF: "1",
                                mostraAvaliacao: '<?= $mostraAvaliacao ?>',
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#BOLETIM_PONTOSPF_NOTA<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "alunos_boletim_pontospf_nota.php",
                            type: "POST",
                            context: $("#BOLETIM_PONTOSPF_NOTA<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                pontosparaPF: "1",
                                mostraAvaliacao: '<?= $mostraAvaliacao ?>',
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#INFORME_RENDIMENTOS<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "financeiro_informe_rendimentos_doceditor.php",
                            type: "POST",
                            context: $("#INFORME_RENDIMENTOS<?= $ida ?>"),
                            data: {
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#INFORME_RENDIMENTOS_DATA_RECEBIMENTO<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "financeiro_informe_rendimentos_doceditor.php",
                            type: "POST",
                            context: $("#INFORME_RENDIMENTOS_DATA_RECEBIMENTO<?= $ida ?>"),
                            data: {
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                dataRecebimento: 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#ALUNO_RELATORIO_AVALIACAO<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "academico_turmas_mapa_notas_avaliacao.php",
                            type: "POST",
                            context: $("#ALUNO_RELATORIO_AVALIACAO<?= $ida ?>"),
                            data: {
                                idalunoRel: '<?= $ida ?>',
                                colunaboletim: '<?= $colunaboletim ?>',
                                idturma: '<?= $turmamatricula ?>',
                                idanoletivo: '<?= $idanoletivo ?>',
                                disciplina: "0@0",
                                idpermissoes: '<?= $idpermissoes ?>',
                                ordem: '<?= $aluno_avaliacao_ordenacao ?>'
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    //DADOS VALOR BRUTO MENSALIDADES E BOLSAS
                    if ($("#MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "bolsas-mes-ano",
                            type: "POST",
                            context: $("#MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL_MES_ANO_<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                'geraBoletim': 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "mensalidades-mes-ano",
                            type: "POST",
                            context: $("#MATRICULA_VALORBRUTO_MENSALIDADE_MES_ANO_<?= $ida ?>"),
                            data: {
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                'geraBoletim': 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                    if ($("#MATRICULA_VALORBRUTO_EVENTO_<?= $ida ?>").length > 0) {
                        ajaxQueue.add({
                            url: "evento-mes-ano",
                            type: "POST",
                            context: $("#MATRICULA_VALORBRUTO_EVENTO_<?= $ida ?>"),
                            data: {
                                evento: "<?= $evento ?>",
                                month: "<?= $month ?>",
                                year: "<?= $year ?>",
                                mostrapontos: "0",
                                mostraAvaliacao: "<?= $mostraAvaliacao ?>",
                                databoletim: "<?= $databoletim ?>",
                                idaluno: <?= $ida ?>,
                                nummatricula: <?= $nummatricula ?>,
                                idanoletivo: <?= $idanoletivo ?>,
                                idunidade: <?= $idunidade ?>,
                                comfaltas: 0,
                                base_dif: '<?= $mostra_base_dif ?>',
                                'geraBoletim': 1
                            },
                            success: function(data) {
                                this.html(data);
                            }
                        });
                    }

                });
            <?php }
            ?>
        </script>
        <?php
    }
    echo $sup_rodape;
    ?>

    <script>
        $(".historico").each(function() {
            const alunoId = this.dataset['id'];
            const anosHistorico = this.dataset['anosHistorico'];
            const ocultarCargaHoraria = this.dataset['ocultarCargaHoraria'];
            const frequencia = this.dataset['frequencia'];

            ajaxQueue.add({
                url: "alunos_historico.php",
                type: "POST",
                context: $(this),
                data: {
                    idaluno: alunoId,
                    doc: '1',
                    anosHistorico: anosHistorico,
                    ocultarCargaHoraria: ocultarCargaHoraria,
                    frequencia: frequencia
                },
                success: function(data) {
                    this.html(data);
                }
            });
        });
    </script>
</body>
</html>
