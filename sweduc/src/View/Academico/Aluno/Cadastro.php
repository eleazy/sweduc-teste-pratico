<?php

declare(strict_types=1);

require __DIR__ . '/../../../../public/function/config.php';
require __DIR__ . '/../../../../public/permissoes.php';

function motivoSituacao($id)
{
    $stmt = "SELECT * FROM motivo where id=" . $id;

    $result = mysql_query($stmt);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    return $row['motivo'];
}

function temRespFin($idaluno)
{
    if (empty($idaluno)) {
        return 0;
    }
    $q = "SELECT * FROM responsaveis WHERE idaluno = " . $idaluno . " AND respfin = 1";

    $result = mysql_query($q);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    return $row['id'] ?? 0;
}
?>

<script type="text/javascript">
    var conteudoBuscaPag = 'alunos_busca.php';
    $('#cep').blur(function() {
        var cep = $(this).val();
        $.ajax({
            url: 'https://viacep.com.br/ws/' + cep + '/json/',
            type: 'GET',
            success: function(data) {
                if (!data.hasOwnProperty('erro')) {
                    $('#idestado option:contains(' + data.uf + ')').attr('selected', 'selected');
                    $('#logradouro').val(data.logradouro);
                    $('#bairro').val(data.bairro);
                    $('#idcidade option:contains(' + data.localidade.toLowerCase() + ')').attr('selected', 'selected');
                    $.ajax({
                        url: "dao/estados.php",
                        type: "POST",
                        data: {
                            action: "recebeCidades",
                            idpermissoes: $("#idpermissoes").val(),
                            idpessoalogin: $("#idpessoalogin").val(),
                            idestado: $('#idestado :selected').val()
                        },
                        context: jQuery('#idcidade'),
                        beforeSend: bloqueiaUI,
                        complete: $.unblockUI,
                        success: function(data2) {
                            this.append(data2);
                            $("select#idcidade option").each(function() {
                                if ($(this).html().toLowerCase() == data.localidade.toLowerCase()) {
                                    $(this).attr('selected', 'selected');
                                }
                            });
                        }
                    });
                    $('#numero').focus();
                }
            }
        });
    });

    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
        $('.clockpicker').clockpicker();

        $('#myTabs a').click(function(e) {
            e.preventDefault();
            $(this).tab('show');
        });

        $("#valorAnuidade").maskMoney({
            prefix: 'R$ ',
            allowNegative: true,
            thousands: '.',
            decimal: ',',
            affixesStay: false
        });
        $("#descontoparcelas").maskMoney({
            prefix: 'R$ ',
            allowNegative: true,
            thousands: '.',
            decimal: ',',
            affixesStay: false
        });
        $("#descontoparcelaspercentual").maskMoney({
            prefix: '',
            allowNegative: true,
            thousands: '.',
            decimal: ',',
            affixesStay: false
        });
    });

    function mudarSituacao(situacao, confirmaMsg) {
        swal({
            title: "Atenção",
            text: confirmaMsg,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#64B5F6",
            confirmButtonText: "Confirmar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: true
        }, function() {
            var idunidadeb = $("#idunidadecadastraFEITA").val();
            $.ajax({
                url: "dao/alunos.php",
                type: "POST",
                data: {
                    action: "mudaSituacao",
                    idaluno: $("#idaluno").val(),
                    matriculaId: $("#idmatricula").val(),
                    idunidade: idunidadeb,
                    turmamatriculadoantiga: $("#turmamatriculadoantiga").val(),
                    nummatricula: $("#nummatriculaFEITA").val(),
                    turmamatriculado: $("#turmamatriculado").val(),
                    idunidadematriculado: $("#idunidadematriculado").val(),
                    idempresamatriculado: $("#idempresamatriculado").val(),
                    planohorariosmatriculado: $("#planohorariosmatriculado :selected").val(),
                    anoletivomatriculado: $("#anoletivomatriculado").val(),
                    situacao: situacao,
                    motivoSituacao: $("#motivoSituacao :selected").val(),
                    obsSituacao: $("#obsSituacao").val(),
                    escoladestino: $("#escoladestino").val()
                },
                success: function(data) {
                    var retorno = data.split("|");

                    const alunoId = $("#idaluno").val();
                    const matriculaId = $("#idmatricula").val();
                    sweduc.carregarUrl(
                        'alunos_cadastra.php' +
                        '?alunoId=' + alunoId +
                        '&matriculaId=' + matriculaId
                    );
                }
            });
        });
    }

    $("#btMmudaSituacao").click(function() {
        var valor = $('#situcaoMatricula :selected').val();
        var motivosit = $('#motivoSituacao :selected').val();
        var confirmaMsg = $('#situcaoMatricula :selected').attr('aria-details');

        if (valor == 0) {
            alert("Selecione a situação.");
            return false;
        }

        if (motivosit == 0 && valor > 1) {
            alert("Selecione o motivo.");
            return false;
        }

        mudarSituacao(valor, confirmaMsg);
    });

    $.mask.definitions['#'] = '[0123]';
    $.mask.definitions['@'] = '[01]';
    $.mask.definitions['&'] = '[12]';
    $.mask.definitions['$'] = '[012]';
    $.mask.definitions['%'] = '[012345]';
    $('.date').mask("#9/@9/&999", {
        placeholder: 'dd/mm/yyyy'
    }).datepicker({
        changeMonth: true,
        changeYear: true,
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        dateFormat: "dd/mm/yy"
    });

    $('.timep').mask("$9:%9", {
        placeholder: 'hh:mm'
    }).timepicker({
        // Options
        timeSeparator: ':', // The character to use to separate hours and minutes. (default: ':')
        showLeadingZero: true, // Define whether or not to show a leading zero for hours < 10.(default: true)
        showMinutesLeadingZero: true, // Define whether or not to show a leading zero for minutes < 10. (default: true)
        showPeriod: false, // Define whether or not to show AM/PM with selected time. (default: false)
        showPeriodLabels: false, // Define if the AM/PM labels on the left are displayed. (default: true)
        periodSeparator: ' ', // The character to use to separate the time from the time period.
        altField: '#alternate_input', // Define an alternate input to parse selected time to
        defaultTime: '12:34', // Used as default time when input field is empty or for inline timePicker
        // (set to 'now' for the current time, '' for no highlighted time, default value: now)

        // trigger options
        showOn: 'focus', // Define when the timepicker is shown.
        // 'focus': when the input gets focus, 'button' when the button trigger element is clicked,
        // 'both': when the input gets focus and when the button is clicked.
        button: null, // jQuery selector that acts as button trigger. ex: '#trigger_button'

        // Localization
        hourText: 'Hora', // Define the locale text for "Hours"
        minuteText: 'Minuto', // Define the locale text for "Minute"
        amPmText: ['AM', 'PM'], // Define the locale text for periods

        // Position
        myPosition: 'left top', // Corner of the dialog to position, used with the jQuery UI Position utility if present.
        atPosition: 'left bottom', // Corner of the input to position

        // buttons
        showCloseButton: false, // shows an OK button to confirm the edit
        closeButtonText: 'Done', // Text for the confirmation button (ok button)
        showNowButton: false, // Shows the 'now' button
        nowButtonText: 'Now', // Text for the now button
        showDeselectButton: false, // Shows the deselect time button
        deselectButtonText: 'Deselect' // Text for the deselect button

    });

    $('.cpf').mask("999.999.999-99", {
        placeholder: 'xxx.xxx.xxx-xx'
    });

    $("#cpf").blur(function() {
        const CPF = document.getElementById('cpf').value;

        if (!(checaCPF(CPF))) {
            swal('Atenção', 'CPF inválido', 'error');
            $("#cpf").val("");
            return;
        }

        $.ajax({
            url: "dao/alunos.php",
            type: 'POST',
            context: jQuery('#cpf'),
            data: {
                action: "buscaCPF",
                cpf: $(this).val()
            },
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function(data) {
                if (data > 0) {
                    swal('Atenção', 'Já existe um aluno(a) cadastrado com esse CPF.', 'error');
                    $("#rg").focus();
                }
            }
        });
    });

    function voltar() {
        btVoltar(conteudoBuscaPag, conteudoBuscaParam);
    }

    function cadastraAlunoEletiva(ideletiva, idaluno, nummatricula, idanoletivo, observacao) {
        $.ajax({
            url: "dao/disciplinas.php",
            type: 'POST',
            // context: jQuery('#conteudo'),
            data: {
                action: "cadastraeletiva",
                nummatricula: nummatricula,
                idaluno: idaluno,
                ideletiva: ideletiva,
                idanoletivo: idanoletivo,
                observacao: observacao
            },
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function(data) {
                var msg = data.split('|');

                if (msg[2] == 1) {
                    $("#btneletiva" + ideletiva).removeClass("green-color");
                    $("#btneletiva" + ideletiva).addClass("btn-danger");
                    $("#btneletiva" + ideletiva).val("Remover aluno");
                }
                if (msg[2] == 0) {
                    $("#btneletiva" + ideletiva).removeClass("btn-danger");
                    $("#btneletiva" + ideletiva).addClass("green-color");
                    $("#btneletiva" + ideletiva).val("Inscrever aluno");
                    $("#obseletiva" + ideletiva).val("");

                }

                alert(msg[1]);
            }
        });
    }

    $('#associaresponsavel').click(function() {
        $('.associaResp').toggle();
    });

    $('#tipoMatriculaFichas').click(function() {
        $('#matrTipo1').toggle();
        $('#matrTipo2').toggle();
    });

    $("#nome").blur(function() {
        var temNomeAluno = false;
        $.ajax({
            url: "dao/alunos.php",
            type: 'POST',
            context: jQuery('#nome'),
            data: {
                action: "buscaNome",
                nome: $(this).val()
            },
            complete: function() {
                if (!temNomeAluno) {
                    buscaAlunoMarketing();
                }
            },
            success: function(data) {
                if (data > 0) {
                    criaAlerta('error', 'Já existe um aluno com esse nome');
                    temNomeAluno = true;
                }
            }
        });
    });

    function buscaAlunoMarketing() {
        $.getJSON("dao/prospeccao.php", {
            action: "buscaAlunoMarketing",
            nome: $("#nome").val()
        }, function(data) {
            if (data.nome != '') {
                swal('Atenção', 'Esse aluno foi encontrado no marketing e será atualizado', 'warning');
                $('#idprospeccao').val(data.id);
            }
        })
    }

    $("#limpaform").click(function() {
        $("#dialog-confirm").dialog({
            resizable: false,
            modal: true,
            buttons: {
                "Limpar": function() {
                    $("#cadastroAlunos").get(0).reset();
                    $(this).dialog("close");
                },
                Cancel: function() {
                    $(this).dialog("close");
                }
            }
        });
    });

    $("#descontoparcelaspercentual").blur(function() {
        valParcelas();
    });
    $("#descontoparcelas").blur(function() {
        valParcelas();
    });

    $("#descontoparcelas").focus(function() {
        $("#descontoparcelaspercentual").val("");
    });

    $("#tipobolsa1").click(function() {
        $("#descontoparcelas").val("0,00");
        $("#descontoparcelaspercentual").val("0,00");

        $("#descontoparcelaspercentual").attr("disabled", "disabled");
        $("#descontoparcelas").removeAttr("disabled");
        valParcelas();
    });

    $("#tipobolsa2").click(function() {
        $("#descontoparcelas").val("0,00");
        $("#descontoparcelaspercentual").val("0,00");

        $("#descontoparcelas").attr("disabled", "disabled");
        $("#descontoparcelaspercentual").removeAttr("disabled");
        valParcelas();
    });

    $("#valorAnuidade").blur(function() {
        if ($("#tipoMatriculaFichas:checked").val() == "1") {
            valParcelasTipo2();
            $("#valorAnuidade").val($("#valorAnuidade").val().replace(".", ","));
            $("#valorAnuidade").parseNumber({
                format: "#,##0.00",
                locale: "br"
            });
            $("#valorAnuidade").formatNumber({
                format: "#,##0.00",
                locale: "br"
            });
        } else
            valParcelas();
    });

    $(document.body).on('blur', '.vTipo2', function() {
        //$(this).val($(this).val().replace(".", "").replace(",", "."));
        $(this).parseNumber({
            format: "#,##0.00",
            locale: "br"
        });
        $(this).formatNumber({
            format: "#,##0.00",
            locale: "br"
        });
        valParcelasTipo2();
    });

    function valParcelasTipo2() {
        var a = 0;
        $(".vTipo2").each(function() {
            a += parseFloat($(this).val().replace(".", "").replace(",", "."));
        });
        $("#valorDaAnuidadeAlunoTipo2").val(a);
        $("#valorDaAnuidadeAlunoTipo2").val($("#valorDaAnuidadeAlunoTipo2").val().replace(".", ","));
        $("#valorDaAnuidadeAlunoTipo2").parseNumber({
            format: "#,##0.00",
            locale: "br"
        });
        $("#valorDaAnuidadeAlunoTipo2").formatNumber({
            format: "#,##0.00",
            locale: "br"
        });
    }

    $("#valorparcelas").blur(function() {
        valParcelas();
    });

    function hFormatNumber(num) {
        num = parseFloat(num);
        return num
            .toFixed(2) // always two decimal digits
            .replace(".", ",") // replace decimal point character with ,
            .replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1."); // use . as a separator
    }

    function valParcelas() {

        if ($("#valorAnuidade").val().replace(".", "").replace(",", ".") > 0) {
            if ($("#tipobolsa1").is(":checked")) { // DINHEIRO
                var descnum = $("#descontoparcelas").val();
                var descVal = parseFloat($("#descontoparcelas").val().replace(".", "").replace(",", "."));
                var valTit = parseFloat($("#valorAnuidade").val().replace(".", "").replace(",", "."));

                $("#descontoparcelaspercentual").val(parseFloat((descVal / valTit) * 100).toFixed(2));
                $("#descontoparcelaspercentual").val(hFormatNumber($("#descontoparcelaspercentual").val()));
                $("#descontoparcelas").val(descnum);

            } else if ($("#tipobolsa2").is(":checked")) { // PERCENTUAL
                var descperc = $("#descontoparcelaspercentual").val();

                var descVal = parseFloat($("#descontoparcelaspercentual").val().replace(".", "").replace(",", "."));
                var valTit = parseFloat($("#valorAnuidade").val().replace(".", "").replace(",", "."));
                $("#descontoparcelas").val(parseFloat((valTit * descVal) / 100).toFixed(2));
                $("#descontoparcelas").val(hFormatNumber($("#descontoparcelas").val()));
                $("#descontoparcelaspercentual").val(descperc);
            }
        } else {
            $("#descontoparcelas").val("0,00");
            $("#descontoparcelas").parseNumber({
                format: "#,##0.00",
                locale: "br"
            });
            $("#descontoparcelas").formatNumber({
                format: "#,##0.00",
                locale: "br"
            });
            $("#descontoparcelaspercentual").val("0,00");
        }

        $("#descontoparcelas").val($("#descontoparcelas").val());
        $("#valorparcelas").val((parseFloat($("#valorAnuidade").val().replace(".", "").replace(",", ".")) - parseFloat($("#descontoparcelas").val().replace(".", "").replace(",", "."))) / parseFloat($("#qtdeparcelas").val()));
        $("#valorparcelas").val($("#valorparcelas").val().replace(".", ","));
        $("#valorparcelas").parseNumber({
            format: "#,##0.00",
            locale: "br"
        });
        $("#valorparcelas").formatNumber({
            format: "#,##0.00",
            locale: "br"
        });
        $("#valorDaAnuidadeAluno").val(parseFloat($("#valorAnuidade").val().replace(".", "").replace(",", ".")) - parseFloat($("#descontoparcelas").val().replace(".", "").replace(",", ".")));
        $("#valorDaAnuidadeAluno").val($("#valorDaAnuidadeAluno").val().replace(".", ","));
        $("#valorDaAnuidadeAluno").parseNumber({
            format: "#,##0.00",
            locale: "br"
        });
        $("#valorDaAnuidadeAluno").formatNumber({
            format: "#,##0.00",
            locale: "br"
        });
        $("#valorAnuidade").parseNumber({
            format: "#,##0.00",
            locale: "br"
        });
        $("#valorAnuidade").formatNumber({
            format: "#,##0.00",
            locale: "br"
        });
    }

    $("#planosparcelamento").change(function() {
        $("#qtdeparcelas").val($("#planosparcelamento").val());
        valParcelas();
    });

    $("#qtdeparcelas").blur(function() {
        if ($("#qtdeparcelas").val() > 50)
            $("#qtdeparcelas").val("50");
        if ($("#qtdeparcelas").val() < 1)
            $("#qtdeparcelas").val("1");

        valParcelas();
    });

    $("#addresp").click(function() {
        if ($("#paren").val() == "-1") {
            swal("Atenção", "Defina o parentesco", "warning");
            return
        }

        $.ajax({
            url: "dao/pessoas.php",
            type: "POST",
            data: {
                action: "pessoa_por_id",
                id: $("#resp").val(),
            },
            success: function(data) {
                let pessoa = JSON.parse(data)
                let responsavelFinanceiro = $('input[name="respfinanceiro"]:checked').val() == 1
                let validacoesRespFinanceiro = [{
                        cond: !checaCPF(pessoa.cpf, true),
                        item: "O CPF"
                    },
                    {
                        cond: pessoa.idpais < 1,
                        item: "O pais"
                    },
                    {
                        cond: pessoa.idestado < 1,
                        item: "O estado"
                    },
                    {
                        cond: pessoa.idcidade < 1,
                        item: "A cidade"
                    },
                    {
                        cond: pessoa.bairro == "",
                        item: "O bairro"
                    },
                    {
                        cond: pessoa.cep == "",
                        item: "O CEP"
                    },
                    {
                        cond: pessoa.logradouro == "",
                        item: "O logradouro"
                    },
                ]

                let valido = true
                if (responsavelFinanceiro) {
                    validacoesRespFinanceiro.some(element => {
                        if (element.cond) {
                            swal("Atenção", element.item + " do responsável financeiro não pode ser invalido", "warning")
                            valido = false
                            return true
                        }
                    });
                }

                if (valido) {
                    associaResponsavel()
                }
            }
        })
    });

    function associaResponsavel() {
        $.ajax({
            url: "dao/alunos.php",
            type: "POST",
            data: {
                action: "associaResponsavel",
                idaluno: $("#idaluno").val(),
                idpessoa: $("#resp").val(),
                idparentesco: $("#paren").val(),
                respfinanceiro: $('input[name="respfinanceiro"]:checked').val(),
                respfinanceiro2: $('input[name="respfinanceiro2"]:checked').val(),
                resppedagogico: $('input[name="resppedagogico"]:checked').val(),
                autorizado: $('input[name="autorizado"]:checked').val(),
                visualiza_financeiro: $('input[name="visualiza_financeiro"]:checked').val(),
                visualiza_pedagogico: $('input[name="visualiza_pedagogico"]:checked').val()
            },
            beforeSend: bloqueiaUI,
            success: function() {
                const alunoId = $("#idaluno").val();
                const matriculaId = $("#idmatricula").val();

                sweduc.carregarUrl(
                    'alunos_cadastra.php' +
                    '?matriculaId=' + matriculaId +
                    '&aba=but_responsaveis'
                );
            }
        });
    }

    cidade = "";

    <?php if ($idaluno) { ?>
        $("#entrevistadepartamento").change(function() {
            $.ajax({
                url: "dao/funcionarios.php",
                type: 'POST',
                context: jQuery('#entrevistaRespEscola'),
                data: {
                    action: "recebeFuncionariosPorDepartamento",
                    idpermissoes: $("#idpermissoes").val(),
                    idpessoalogin: $("#idpessoalogin").val(),
                    iddepartamento: $("#entrevistadepartamento").val()
                },
                beforeSend: bloqueiaUI,
                complete: $.unblockUI,
                success: function(data) {
                    this.html(data);
                }
            });
        });

        $("#but_financeiro").click(function() {
            const matriculaId = document.getElementById('idmatricula').value;

            sweduc.carregarUrl(
                'alunos_fichafin.php' +
                '?idmatricula=' + matriculaId
            );
        });

        $("#entrevistaRespAluno").change(function() {
            if ($(this).val() == -1) {
                $("#entrevistaRespAlunoOutro").css('display', 'inline-block');
                $("#entrevistaRespAlunoOutro").val("");
            } else if ($(this).val() == -2) {
                $("#entrevistaRespAlunoOutro").css('display', 'inline-block');
                $("#entrevistaRespAlunoOutro").val($("#nome").val());
            } else {
                $("#entrevistaRespAlunoOutro").val("");
                $("#entrevistaRespAlunoOutro").css('display', 'none');
            }
        });

        $("#trocaPHorarios").click(function() {
            swal({
                title: "Atenção",
                text: "Confirma trocar a plano de horários ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#64B5F6",
                confirmButtonText: "Confirmar",
                cancelButtonText: "Cancelar",
                closeOnConfirm: true
            }, function() {
                var idunidadeb = $("#idunidadecadastraFEITA").val();
                $.ajax({
                    url: "dao/alunos.php",
                    type: "POST",
                    data: {
                        action: "trocaPHorarios",
                        idaluno: $("#idaluno").val(),
                        idunidade: idunidadeb,
                        turmamatriculadoantiga: $("#turmamatriculadoantiga").val(),
                        nummatricula: $("#nummatriculaFEITA").val(),
                        turmamatriculado: $("#turmamatriculado").val(),
                        idunidadematriculado: $("#idunidadematriculado").val(),
                        idempresamatriculado: $("#idempresamatriculado").val(),
                        planohorariosmatriculado: $("#planohorariosmatriculado :selected").val(),
                        anoletivomatriculado: $("#anoletivomatriculado").val(),
                        motivoSituacao: $("#motivoSituacao :selected").val(),
                        obsSituacao: $("#obsSituacao").val(),
                        escoladestino: $("#escoladestino").val()
                    },
                    success: function(data) {
                        var retorno = data.split("|");

                        const alunoId = $("#idaluno").val();
                        const matriculaId = $("#idmatricula").val();

                        sweduc.carregarUrl(
                            'alunos_cadastra.php' +
                            '?alunoId=' + alunoId +
                            '&matriculaId=' + matriculaId
                        );
                    }
                });
            });
        });

        $("#addentrevista").click(function() {
            $.ajax({
                url: "dao/alunos.php",
                type: "POST",
                data: {
                    action: "addentrevista",
                    identrevista: $("#identrevista").val(),
                    idpessoalogin: $("#idpessoalogin").val(),
                    idpermissoes: $("#idpermissoes").val(),
                    idaluno: $("#idaluno").val(),
                    iddepartamento: $("#entrevistadepartamento").val(),
                    idpessoaresponsavel: $("#entrevistaRespAluno").val(),
                    outro: $("#entrevistaRespAlunoOutro").val(),
                    idpessoafuncionario: $("#entrevistaRespEscola").val(),
                    assunto: $("#entrevistaassunto").val(),
                    data: $("#entrevistadata").val(),
                    hora: $("#entrevistahora").val(),
                    datarealizada: $("#entrevistadatarealizada").val(),
                    horarealizada: $("#entrevistahorarealizada").val(),
                    resumo: $("#entrevistaresumo").val()
                },
                beforeSend: bloqueiaUI,
                success: function(data) {
                    var retorno = data.split('|');
                    if (retorno[0] == 'green') {
                        criaAlerta('success', 'Entrevista salva com sucesso');
                    } else {
                        criaAlerta('error', 'Ocorreu um erro ao salvar a entrevista');
                    }

                    const alunoId = $("#idaluno").val();
                    const matriculaId = $("#idmatricula").val();

                    sweduc.carregarUrl(
                        'alunos_cadastra.php' +
                        '?alunoId=' + alunoId +
                        '&matriculaId=' + matriculaId +
                        '&acao=editar' +
                        '&aba=but_entrevistas' +
                        '&modo=' + $("#modo").val()
                    );
                }
            });
        });
    <?php } ?>

    $("#addaut").click(function() {
        $("#table-autorizacoes").append(
            "<tr height='50px' ><td style='padding:0px 10px;border-bottom:1px solid #000;'><input type='hidden' name='nomeautorizado[]' value='" + $("#aut_nome").val() + "'>" + $("#aut_nome").val() + "</td>" +
            "<td style='padding:0px 10px;border-bottom:1px solid #000;'><input type='hidden' name='aut_parentesco[]' value='" + $("#aut_paren option:selected").val() + "'>" + $("#aut_paren option:selected").text() + "</td>" +
            "<td style='padding:0px 10px;border-bottom:1px solid #000;'><input type='hidden' name='aut_documento[]' value='" + $("#aut_doc").val() + "'>" + $("#aut_doc").val() + "</td>" +
            "<td style='padding:0px 10px;border-bottom:1px solid #000;'><input type='hidden' name='aut_telefone[]' value='" + $("#aut_tel").val() + "'>" + $("#aut_tel").val() +
            "   -    <input type='button' value=' X ' class='button bgred' onclick='$(this).parent().parent().remove();'>" +
            "</td></tr>"
        );
    });

    function removeLigacao(tabela, id) {
        swal({
            title: "Atenção",
            text: "Deseja executar essa ação?!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Deletar!",
            cancelButtonText: "Cancelar",
            closeOnConfirm: true
        }, function() {
            $.ajax({
                url: "dao/alunos.php",
                type: "POST",
                data: {
                    action: "removeLigacao",
                    idpermissoes: $("#idpermissoes").val(),
                    idpessoalogin: $("#idpessoalogin").val(),
                    tabela: tabela,
                    id: id
                },
                context: jQuery('#row_' + id),
                beforeSend: bloqueiaUI,
                complete: $.unblockUI,
                success: function(data) {
                    $(this).remove();
                    if (tabela == 'alunos_documentos') {
                        $("#documentosentregues").html(data);
                    }

                    if (tabela == 'alunos_entrevistas') {
                        criaAlerta('success', 'Entrevista removida com sucesso');
                        $('#linha-entrevista-' + id).remove();
                    }

                    if (tabela == 'responsaveis') {
                        criaAlerta('success', 'Responsável removido com sucesso');
                        $('#responsaveis' + id).remove();
                    }

                    if (tabela == 'alunos_ocorrencias') {
                        criaAlerta('success', 'Ocorrência removida com sucesso');
                    }
                }
            });
        });
    }

    function mostraObrigatorios() {
        $(".matObr").html('<div class="error-left"></div><div class="error-inner">*Obrigatório</div>');
    }

    function escondeObrigatorios() {
        $(".matObr").html('');
    }

    $("#idunidadecadastra").change(function() {
        pegaDados();
    });
    $("#anoletivomatricula").change(function() {
        pegaDados();
    });

    function pegaDados() {
        if ('<?= $cliente ?>' == 'alfacem') {
            if ($("#idunidadecadastra").val() != 1) {
                $('#planohorarios option').each(function(id, element) {
                    $('#planohorarios option').prop('selected', $(element).val() == 1)
                    $(element).prop('disabled', $(element).val() != 1)
                })
            } else {
                $('#planohorarios option').each(function(id, element) {
                    $(element).prop('disabled', false)
                })
            }
        }

        if ($("#idunidadecadastra").val() == "-1") {
            $("#anoletivomatricula").val("-1");
            $("#nummatricula").val("");
            escondeObrigatorios();
        } else
            mostraObrigatorios();

        $.ajax({
            url: "dao/empresas.php",
            type: "POST",
            data: {
                action: "empresasUnidades",
                idpermissoes: $("#idpermissoes").val(),
                idpessoalogin: $("#idpessoalogin").val(),
                idunidade: $('#idunidadecadastra :selected').val()
            },
            context: jQuery('#idempresacadastra'),
            success: function(data) {
                this.html(data);
            }
        });

        $.ajax({
            url: "dao/cursos.php",
            type: "POST",
            data: {
                action: "recebeCursos",
                idpermissoes: $("#idpermissoes").val(),
                idpessoalogin: $("#idpessoalogin").val(),
                idunidade: $('#idunidadecadastra :selected').val(),
                anoLetivo: $('#anoletivomatricula :selected').text(),
                usaAnoLimite: true
            },
            context: jQuery('#cursomatricula'),
            success: function(data) {
                this.html(data);
                $("#cursomatricula").change();
            }
        });
    }

    $("#idempresacadastra").change(function() {
        if ($("#idempresacadastra").val() <= "0") {
            return false;
        }
        $.ajax({
            url: "dao/contasbanco.php",
            type: "POST",
            data: {
                action: "recebeContasEmpresaSimples",
                idpermissoes: $("#idpermissoes").val(),
                idpessoalogin: $("#idpessoalogin").val(),
                idunidade: $('#idunidadecadastra :selected').val(),
                idempresa: $('#idempresacadastra :selected').val()
            },
            context: jQuery('#idcontasbanco'),
            success: function(data) {
                this.html(data);
            }
        });
    });

    $("#idunidadematriculado").change(function() {
        $.ajax({
            url: "dao/empresas.php",
            type: "POST",
            data: {
                action: "empresasUnidades",
                idpermissoes: $("#idpermissoes").val(),
                idpessoalogin: $("#idpessoalogin").val(),
                idunidade: $('#idunidadematriculado :selected').val()
            },
            context: jQuery('#idempresamatriculado'),
            success: function(data) {
                this.html(data);
            }
        });

        $.ajax({
            url: "dao/cursos.php",
            type: "POST",
            data: {
                action: "recebeCursos",
                idpermissoes: $("#idpermissoes").val(),
                idpessoalogin: $("#idpessoalogin").val(),
                idunidade: $('#idunidadematriculado :selected').val()
            },
            context: jQuery('#cursomatriculado'),
            success: function(data) {
                this.html(data);
                $("#cursomatriculado").change();
            }
        });
    });

    $("#cursomatriculado").change(function() {
        $.ajax({
            url: "dao/series.php",
            type: "POST",
            data: {
                action: "recebeSeriesComTurmas",
                idpermissoes: $("#idpermissoes").val(),
                idpessoalogin: $("#idpessoalogin").val(),
                idcurso: $('#cursomatriculado :selected').val()
            },
            context: jQuery('#seriematriculado'),
            success: function(data) {
                this.html(data);
                $("#seriematriculado").change();
            }
        });
    });

    $("#seriematriculado").change(function() {
        var serieId = $('#seriematriculado :selected').val();
        var anoletivomatricula = $("#anoletivomatriculado :selected").val();

        buildOptions(
            '/api/v1/academico/series/' + serieId + '/turmas?periodoLetivoId=' + anoletivomatricula,
            document.querySelector('#turmamatriculado'), {
                value: function(x) {
                    return x.id;
                },
                text: function(turma) {
                    var limite = turma.quantalunos == 0 ? "Sem limite" : turma.quantalunos;
                    return turma.turma + " (Matriculados: " + turma.matriculados + " / Limite: " + limite + ")";
                },
                placeholder: 'Selecione a turma',
            }
        );

        $.ajax({
            url: '/api/v1/academico/series/' + serieId + '/turmas?periodoLetivoId=' + anoletivomatricula,
            context: $('#turmamatriculado'),
            success: function(data) {
                var options = '';
                options += '<option value="" selected disabled>Selecione a turma</option>';

                var turmas = JSON.parse(data);
                turmas.forEach(turma => {
                    var limite = turma.quantalunos == 0 ? "Sem limite" : turma.quantalunos;
                    options += "<option value=\"" + turma.id + "\">";
                    options += turma.turma + " (Matriculados: " + turma.matriculados + " / Limite: " + limite + ")";
                    options += "</option>";
                });

                this.html(options);
            }
        });
    });

    $("#turmamatricula").change(function() {
        $.ajax({
            url: "dao/turmas.php",
            type: "POST",
            data: {
                action: "quantsAlunos",
                idpermissoes: $("#idpermissoes").val(),
                idpessoalogin: $("#idpessoalogin").val(),
                idturma: $('#turmamatricula :selected').val(),
                anoletivomatricula: $("#anoletivomatricula :selected").val()
            },
            context: jQuery('#turmamatriculaquantalunos'),
            success: function(data) {
                this.html(data);

                var turmaLimitada = $("#quantlimitealunosturma").val() > 0;
                var turmaLotada = parseInt($("#quantalunosturma").val()) >= parseInt($("#quantlimitealunosturma").val());
                var turmaProibeLotacao = <?= getConfig('TURMA_PROIBE_SUPERLOTACAO') ? 'true' : 'false' ?>;
                $('#turma-erro-msg').remove()
                $('#turmamatricula').removeClass('input-error')
                if (turmaLimitada && turmaLotada && turmaProibeLotacao) {
                    $('#turmamatricula').addClass('input-error')
                    $('#turmamatricula').parent().append('<div id="turma-erro-msg"><small class="error-msg">Não é possível adicionar mais alunos nesta turma</small></div>')
                }
            }
        });
    });

    $("#anoletivomatricula").change(function() {
        $("#cursomatricula").change()
    });

    $("#cursomatricula").change(function() {
        $.ajax({
            url: "dao/series.php",
            type: "POST",
            data: {
                action: "recebeSeriesComTurmas",
                idpermissoes: $("#idpermissoes").val(),
                idpessoalogin: $("#idpessoalogin").val(),
                idcurso: $('#cursomatricula :selected').val()
            },
            context: jQuery('#seriematricula'),
            success: function(data) {
                this.html(data);
                $("#seriematricula").change();
            }
        });
    });


    $("#seriematricula").change(function() {
        var serieId = $('#seriematricula :selected').val();
        var anoletivomatricula = $("#anoletivomatricula :selected").val();

        if (!serieId || !anoletivomatricula) {
            return;
        }

        buildOptions(
            '/api/v1/academico/series/' + serieId + '/turmas?periodoLetivoId=' + anoletivomatricula,
            document.querySelector('#turmamatricula'), {
                value: function(x) {
                    return x.id;
                },
                text: function(turma) {
                    var limite = turma.quantalunos == 0 ? "Sem limite" : turma.quantalunos;
                    return turma.tituloCompleto + " (Matriculados: " + turma.matriculados + " / Limite: " + limite + ")";
                },
                placeholder: 'Selecione a turma',
            }
        );

        if ($('#valorAnuidade').length > 0) {
            $.ajax({
                url: "dao/series.php",
                type: "POST",
                data: {
                    action: "recebeAnuidade",
                    idpermissoes: $("#idpermissoes").val(),
                    idpessoalogin: $("#idpessoalogin").val(),
                    idserie: $('#seriematricula :selected').val()
                },
                context: jQuery('#valorAnuidade'),
                success: function(data) {
                    var retorno = data.split("|");
                    this.val(retorno[0]);
                    valParcelas();
                }
            });

            $.ajax({
                url: "dao/planosparcelamento.php",
                type: "POST",
                data: {
                    action: "recebePlanos",
                    idpermissoes: $("#idpermissoes").val(),
                    idpessoalogin: $("#idpessoalogin").val(),
                    idserie: $('#seriematricula :selected').val()
                },
                context: jQuery('#planosparcelamento'),
                success: function(data) {
                    this.html(data);
                }
            });
        }

        $.ajax({
            url: "dao/documentos.php",
            type: "POST",
            data: {
                action: "recebeDocumentos",
                idpermissoes: $("#idpermissoes").val(),
                idpessoalogin: $("#idpessoalogin").val(),
                idserie: $('#seriematricula :selected').val()
            },
            context: jQuery('#documentosentregues'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function(data) {
                this.html(data);
            }
        });

    });


    $("#turmamatricula").on('DOMSubtreeModified', function() {

        var serieId = $('#seriematricula :selected').val();
        var anoletivomatricula = $("#anoletivomatricula :selected").val();

        $.ajax({
            url: "dao/turmas.php",
            type: "POST",
            data: {
                action: "verificaturmaativa",
                serieId: serieId,
                anoletivomatricula: anoletivomatricula
            },
            success: function(data) {
                var turmas = JSON.parse(data);
                $("#turmamatricula option[value='" + turmas['id'] + "']").remove();
            }
        });
    });

    $("#idestadonascimento").change(function() {
        $.ajax({
            url: "dao/estados.php",
            type: "POST",
            data: {
                action: "recebeCidades",
                idpermissoes: $("#idpermissoes").val(),
                idpessoalogin: $("#idpessoalogin").val(),
                idestado: $('#idestadonascimento :selected').val()
            },
            context: jQuery('#idcidadenascimento'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function(data) {
                this.html(data);
            }
        });
    });

    function noneAll() {
        $("#tab_alunos").css("display", "none");
        $("#tab_dadosadicionais").css("display", "none");
        $("#tab_matricula").css("display", "none");
        $("#tab_nova_matricula").css("display", "none");
        $("#tab_saude").css("display", "none");
        $("#tab_cadastro_responsavel").css("display", "none");
        $("#tab_responsaveis").css("display", "none");
        $("#tab_ocorrencias").css("display", "none");
        $("#tab_documentos").css("display", "none");
        $("#tab_entrevistas").css("display", "none");
        $("#but_aluno").parent().removeClass("active");
        $("#but_dadosadicionais").parent().removeClass("active");
        $("#but_anexodocumentos").parent().removeClass("active");
        $("#but_matricula").parent().removeClass("active");
        $("#but_saude").parent().removeClass("active");
        $("#but_responsaveis").parent().removeClass("active");
        $("#but_ocorrencias").parent().removeClass("active");
        $("#but_documentos").parent().removeClass("active");
        $("#but_entrevistas").parent().removeClass("active");

        $("#tab_eletivas").css("display", "none");
        $("#but_eletivas").parent().removeClass("active");
    }

    <?php
    if (!empty($identrevista)) {
        echo '$("#but_entrevistas").click();';
    }
    if (!empty($aba)) {
        echo '$("#' . $aba . '").click()';
    }
    ?>

    function submeterFormulario() {
        const form = document.getElementById('cadastroAlunos');
        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/dao/alunos.php');

        bloqueiaUI();
        xhr.onreadystatechange = function() {
            $.unblockUI();

            if (xhr.readyState === XMLHttpRequest.DONE) {
                $.unblockUI();

                var status = xhr.status;
                if (status === 0 || (status >= 200 && status < 400)) {
                    const data = JSON.parse(xhr.responseText);
                    const matriculaId = data.matriculaId;

                    // sweduc.carregarUrl(
                    //     'alunos_cadastra.php' +
                    //     '?matriculaId=' + matriculaId +
                    //     '&acao=editar'
                    // );
                    sweduc.carregarUrl(
                        'alunos_fichafin.php' +
                        '?idmatricula=' + matriculaId
                    );

                    criaAlerta('success', data.msg);
                } else {
                    criaAlerta('error', xhr.responseText);
                }
            }
        };

        xhr.send(formData);
    }

    /**
     * Identifica se há preenchimento de
     * campos referentes à matricula
     */
    function campoDeMatriculaPreenchido() {
        if ($.trim($("#idunidadecadastra").val()) != "-" && $.trim($("#idunidadecadastra").val()) != "-1") {
            return true
        }
        if ($.trim($("#idempresacadastra").val()) != "-1") {
            return true
        }
        if ($.trim($("#cursomatricula").val()) != "-1" && $.trim($("#cursomatricula").val()) != "todos") {
            return true
        }
        if ($.trim($("#eventosMatricula").val()) != "-1") {
            return true
        }
        if ($.trim($("#seriematricula").val()) != "-1" && $.trim($("#seriematricula").val()) != "") {
            return true
        }
        if ($.trim($("#turmamatricula").val()) != "-1" && $.trim($("#turmamatricula").val()) != "") {
            return true
        }

        return false
    }

    /**
     * Valida o formulário com base nos requisitos
     * e submete caso esteja de acordo
     */
    function cadastraOuAtualizaAluno(matriculado) {
        // Impede a submissão do formulário caso o nome do aluno não esteja preenchido
        if ($.trim($("#nome").val()) == "") {
            $("#but_aluno").click();
            exibirErroDeValidacao($("#nome"), "O nome é obrigatório.");
            return;
        }

        /**
         * Caso o aluno não esteja matriculado ou
         * Algum campo de matricula esteja preenchido durante a atualização, indicando uma rematricula
         *
         * Impede a submissão do formulário
         */
        if (!!!matriculado || matriculado < 0 || campoDeMatriculaPreenchido()) {
            if (!validaMatricula()) {
                return;
            }
        }

        if (matriculado > 0 || matriculado) {
            $("#descontoparcelas").removeAttr("disabled");
        }

        const selectedOption = $('#idcontasbanco :selected');
        const selectedPessoaResponsavel = document.querySelector('#resp_pessoa_id');
        const associouResponsavelExistente = selectedPessoaResponsavel && selectedPessoaResponsavel.value > 0;
        const jaPossuiRespFin = <?= json_encode((bool) temRespFin($aluno->id)) ?>;

        if (selectedOption) {
            const bancoNum = selectedOption.data('banconum');
            if (bancoNum == '461' && !jaPossuiRespFin && !associouResponsavelExistente) {
                if ($.trim($("#resp_nome").val()) == "") {
                    exibirErroDeValidacao($("#resp_nome"), "O cadastro de responsável financeiro é obrigatório para o banco Asaas.");
                    document.getElementById('but_cadastro_responsavel').click();
                    $("#resp_nome").scrollIntoView();
                    $("#resp_nome").focus();
                    return;
                }

                if ($("#idparentesco :selected").val() == -1) {
                    exibirErroDeValidacao($("#idparentesco"), "Escolha o parentesco!");
                    return;
                }

                if (!checaCPF($("#resp_cpf").val(), true)) {
                    exibirErroDeValidacao($("#resp_cpf"), "CPF inválido");
                    return;
                }

                if ($.trim($("#resp_cep").val()) == "") {
                    exibirErroDeValidacao($("#resp_cep"), "O cadastro de responsável financeiro requer o preencimento do CEP");
                    return
                }

                if ($.trim($("#resp_logradouro").val()) == "") {
                    exibirErroDeValidacao($("#resp_logradouro"), "O cadastro de responsável financeiro requer o preencimento do endereço");
                    return
                }
                if ($.trim($("#resp_numero").val()) == "") {
                    exibirErroDeValidacao($("#resp_numero"), "O cadastro de responsável financeiro requer o preencimento do número");
                    return
                }

                if ($.trim($("#resp_uf").val()) == "") {
                    exibirErroDeValidacao($("#resp_uf"), "O cadastro de responsável financeiro requer o preencimento do UF");
                    return
                }

                if ($.trim($("#resp_cidade").val()) == "") {
                    exibirErroDeValidacao($("#resp_cidade"), "O cadastro de responsável financeiro requer o preencimento da cidade");
                    return
                }

                if ($('input[name="respfinanceiro"]:checked').length == 0) {
                    exibirErroDeValidacao($("#respfinanceiro"), "Definir como responsável financeiro é obrigatório para o banco Asaas.");
                    return;
                }
            }
        }

        submeterFormulario();
    }

    function turmaPermiteCadastro() {
        var cadastrar = true;

        var turmaLimitada = $("#quantlimitealunosturma").val() > 0;
        var turmaLotada = $("#quantalunosturma").val() >= $("#quantlimitealunosturma").val()
        var turmaProibeLotacao = <?= getConfig('TURMA_PROIBE_SUPERLOTACAO') ? 'true' : 'false' ?>;

        if (turmaLimitada && turmaLotada) {
            cadastrar = false;

            if (!turmaProibeLotacao) {
                cadastrar = confirm('A turma está cheia. Matricular mesmo assim?');
            }
        }

        return cadastrar;
    }

    function validaMatricula() {
        if (!turmaPermiteCadastro()) {
            exibirErroDeValidacao(idUnidadeCadastra, "Não é permitido matrícular mais alunos nessa turma.")
            return false;
        }

        var idUnidadeCadastra = $("#idunidadecadastra")
        if ($.trim(idUnidadeCadastra.val()) == "-1" || $.trim(idUnidadeCadastra.val()) == "-") {
            exibirErroDeValidacao(idUnidadeCadastra, "Para efetivar a matrícula a unidade é obrigatória.")
            $("#but_nova_matricula").click();
            document.getElementById('idunidadecadastra').scrollIntoView();
            return false;
        }

        var idEmpresaCadastra = $("#idempresacadastra")
        if ($.trim(idEmpresaCadastra.val()) == "-1") {
            exibirErroDeValidacao(idEmpresaCadastra, "Para efetivar a matrícula selecione a empresa.")
            return false;
        }

        var idDataMatricula = $("#datamatricula")
        if ($.trim(idDataMatricula.val()) == "") {
            exibirErroDeValidacao(idDataMatricula, "Para efetivar a matrícula a data da matrícula é obrigatória.")
            return false;
        }

        var idCursoMatricula = $("#cursomatricula")
        if ($.trim(idCursoMatricula.val()) == "-1" || $.trim(idCursoMatricula.val()) == "todos" || $.trim(idCursoMatricula.val()) == "") {
            exibirErroDeValidacao(idCursoMatricula, "Para efetivar a matrícula selecione o curso.")
            return false;
        }

        var idSerieMatricula = $("#seriematricula")
        if ($.trim(idSerieMatricula.val()) == "-1" || $.trim(idSerieMatricula.val()) == "todos" || $.trim(idSerieMatricula.val()) == "") {
            exibirErroDeValidacao(idSerieMatricula, "Para efetivar a matrícula a série da matrícula é obrigatória.")
            return false;
        }

        var idTurmaMatricula = $("#turmamatricula")
        if ($.trim(idTurmaMatricula.val()) == "-1" || $.trim(idTurmaMatricula.val()) == "") {
            exibirErroDeValidacao(idTurmaMatricula, "Para efetivar a matrícula selecione a Turma.")
            return false;
        }

        var idEventosMatricula = $("#eventosMatricula")
        if (idEventosMatricula.val() == -1) {
            exibirErroDeValidacao(idEventosMatricula, "Para efetivar a matrícula selecione um evento financeiro.")
            return false;
        }

        var idData1Parcela = $("#data1parcela")
        if (idData1Parcela.val() == "") {
            exibirErroDeValidacao(idData1Parcela, "Para efetivar a matrícula selecione o dia de pagamento.")
            return false;
        }

        return true
    }

    $("#butPlusParcela").click(function() {
        i = parseInt($("#qtdeparcelasTipo2").val());
        $("#bodyAddParcelas").append('<tr valign="top" id="parcRow' + i + '"><th valign="top" width="80px" style="padding:0px 5px;text-align:right;">Valor da Parcela(R$):                                 </th><th><input type="text" name="valorparcelasTipo2[]" id="valorparcelasTipo2' + i + '" class="inp-form vTipo2" value="0,00" /></th><td style="padding:0px 5px;">Data do vencimento:&nbsp;<input type="date" name="data1parcelaTipo2[]" id="data1parcelaTipo2' + i + '" class="inp-form" /></td></tr>');
        $("input[name='data1parcelaTipo2[]']").mask("#9/@9/&999", {
            placeholder: 'dd/mm/yyyy'
        }).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy"
        });
        $("#qtdeparcelasTipo2").val((i + 1));
    });

    $("#butMinusParcela").click(function() {
        i = $("#qtdeparcelasTipo2").val() - 1;
        if (i > 0) {
            $("#parcRow" + i).remove();
            $("#qtdeparcelasTipo2").val(i);
            valParcelasTipo2();
        }
    });

    $("#planohorarios").change(function() {
        var valor = $("#planohorarios :selected").attr('valor');
        if (valor > 0) {
            $('#valorAnuidade').val(valor.replace(".", ","));
            $('#valorDaAnuidadeAluno').val(valor.replace(".", ","));
            $('#valorparcelas').val(valor.replace(".", ","));
        }
    });

    $("#planohorariosmatriculado").change(function() {

        var valor = $("#planohorariosmatriculado :selected").attr('valor');
        if (valor > 0) {
            $('#valorAnuidade').val(valor.replace(".", ","));
            $('#valorDaAnuidadeAluno').val(valor.replace(".", ","));
            $('#valorparcelas').val(valor.replace(".", ","));
        }
    });

    <?php
    $queryescolaorigem = "SELECT DISTINCT escola FROM alunos_historico WHERE escola <> '' group by escola ORDER BY escola ASC";
    $resultescolaorigem = mysql_query($queryescolaorigem);
    $escolascadastradas = [];

    while ($rowescolaorigem = mysql_fetch_array($resultescolaorigem, MYSQL_ASSOC)) {
        $escolascadastradas[] = addslashes($rowescolaorigem['escola']);
    }

    $escolascadastradas = '\'' . implode('\',\'', $escolascadastradas) . '\'';
    ?>

    function addInput(divName) {
        var newdiv = document.createElement('div');
        newdiv.innerHTML = "Nome: <input type='text' id='ana_nome' name='ana_nome[]' class='inp-form'  value=''>&nbsp;&nbsp;&nbsp;&nbsp;Turma:<input type='text' id='ana_turma' name='ana_turma[]' class='inp-form'  value=''>";
        document.getElementById(divName).appendChild(newdiv);
    }

    $("#tabeladesconto").change(function() {
        var sel_desc = $('#tabeladesconto').find(":selected").val();
        var splitval = sel_desc.split("_");

        var desc = splitval[1].replace('.', ',');

        $("#descontoparcelaspercentual").val(desc);
        $("#descontoparcelaspercentual").parseNumber({
            format: "#,##0.00",
            locale: "br"
        });
        $("#descontoparcelaspercentual").formatNumber({
            format: "#,##0.00",
            locale: "br"
        });
        $("#tipobolsa2").prop("checked", true);
        $("#descontoparcelaspercentual").focus();
        valParcelas();
    });

    function showFrm(frmElement) {
        $('#frm-ficha-anamnese').hide();
        $('#frm-ficha-basica').hide();
        $('#frm-ficha-sindrome-gripal').hide();

        $(frmElement).show();
    }

    $('#ficha-basica').on('click', function() {
        showFrm('#frm-ficha-basica');
    });

    $('#ficha-anamnese').on('click', function() {
        showFrm('#frm-ficha-anamnese');

        var idaluno = '<?= $idaluno ?? '0' ?>';
        $.ajax({
            url: 'aluno_ficha_anamnese.php',
            type: 'post',
            data: {
                idaluno: idaluno
            },
            context: $('#frm-ficha-anamnese'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function(data) {
                $(this).empty().html(data);
            }
        });
    });

    $('#ficha-sindrome-gripal').on('click', function() {
        showFrm('#frm-ficha-sindrome-gripal');

        var idaluno = '<?= $idaluno ?? '0' ?>';
        $.ajax({
            url: 'aluno_ficha_sindrome_gripal_visualizar.php',
            type: 'post',
            data: {
                idaluno: idaluno
            },
            context: $('#frm-ficha-sindrome-gripal'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function(data) {
                $(this).empty().html(data);
            }
        });
    });

    $(function() {
        var escolasTags = [<?= $escolascadastradas ?>];
        $("#escolaCadastra").autocomplete({
            source: escolasTags
        });
    });

    function atualizaAnuidade() {
        let aluno_id = $('#idaluno').val()
        let matricula_id = $('#idmatricula').val()
        let mensalidade = $('#editarAnuidade').find('input[name=anuidade-mensalidade]').val()
        let parcelas = $('#editarAnuidade').find('input[name=anuidade-parcelas]').val()
        let bolsa = $('#editarAnuidade').find('input[name=anuidade-bolsa-perc]').val()

        $.ajax({
            url: '/dao/alunos.php',
            type: 'POST',
            data: {
                action: 'atualizarAnuidade',
                aluno_id: aluno_id,
                matricula_id: matricula_id,
                mensalidade: mensalidade,
                parcelas: parcelas,
                bolsa: bolsa
            },
            beforeSend: bloqueiaUI,
            success: function() {
                const alunoId = $("#idaluno").val();
                const matriculaId = $("#idmatricula").val();

                sweduc.carregarUrl(
                    'alunos_cadastra.php' +
                    '?alunoId=' + alunoId +
                    '&matriculaId=' + matriculaId
                );
            },
            error: function(xhr) {
                $.unblockUI()
                var response = JSON.parse(xhr.responseText)
                criaAlerta('error', response.msg)
            }
        })
    }

    $('#editarAnuidade').on('change', function() {
        let mensalidade = $('#editarAnuidade').find('input[name=anuidade-mensalidade]').first()
        let parcelas = $('#editarAnuidade').find('input[name=anuidade-parcelas]').first()
        let bolsaPerc = $('#editarAnuidade').find('input[name=anuidade-bolsa-perc]').first()
        let bolsaAbs = $('#editarAnuidade').find('input[name=anuidade-bolsa-abs]').first()

        if (event.target.id == 'anuidade-mensalidade' ||
            event.target.id == 'anuidade-bolsa-perc') {
            bolsaAbs.val((bolsaPerc.val() / 100 * mensalidade.val()).toFixed(2))
        }

        if (event.target.id == 'anuidade-bolsa-abs') {
            bolsaPerc.val((bolsaAbs.val() / mensalidade.val()) * 100)
        }
    })

    function gerarUsuario() {
        var pessoaId = $("#idpessoa").val()
        $.ajax({
            url: '/dao/usuarios.php?action=cadastrarUsuarioAluno',
            type: 'POST',
            context: $('#aviso-usuario-nao-criado'),
            data: {
                pessoaId: pessoaId
            },
            success: function(data) {
                response = JSON.parse(data)

                this.remove()
                $('#usuarioaluno').val(response.usuario)
                $('#senhaaluno').val(response.senha)

                criaAlerta('success', response.msg)
            },
            error: function() {
                criaAlerta('error', 'Não foi possível criar usuário.')
            }
        })
    }
</script>

<div id="form__telefone" style="display:none"> <br />
    <div class="col-lg-6" style="padding-left: 0; margin-top: 10px;">
        <input type="text" name="telefone[]" class="form-element" />
    </div>
    <div class="col-lg-6">
        <select name="tipotelefone[]" style="margin-top: 10px;" class="form-element">
            <option value="0">Não informado</option>
            <?php
            $query1 = "SELECT * FROM tipotel ORDER BY id ASC";
            $result1 = mysql_query($query1);
            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                ?>
                <option value="<?= $row1['id'] ?>"><?= $row1['tipotel'] ?></option>
            <?php } ?>
        </select>
    </div>
</div>

<div id="dialog-confirm" title="Limpar o formul&aacute;rio todo ?" style="display:none">
    <span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>
    Isso irá limpar todos os campos de todas as abas da ficha do aluno que está sendo preenchida. Confirma?
</div>

<div id="confirmaAcaoMatricula" title="" style="display:none">
    <span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>
    <span id="textoDialogo"></span>
</div>

<div id="content-outer" class="py-3">
    <input type="hidden" name="idfuncionario" id="idfuncionario" value="<?= $idfuncionario ?>">
    <input type="hidden" name="idfuncionariounidade" id="idfuncionariounidade" value="<?= $idfuncionariounidade ?>">

    <div
        id="dialog-respCadastra"
        title="Cadastro de Responsável"
        style="position:absolute;width:1000px;height:800px;display:none;background-color:#fff;z-index:100;"></div>

    <div class="container-fluid">
        <div class="flex py-3">
            <h3 class="p-0 m-0">
                Alunos |
                <?= $idpermissoes > 0 ? "Cadastrar" : "PERFIL" ?>

                <br />

                <?php if (!empty($nome)) : ?>
                    Aluno(a): <?= $nome ?>
                    (<?= $rowUnidade['unidade'] ?>)
                    (Turma: <?= $rowTurma['turma'] ?>)
                <?php endif ?>

                <?php if (!empty($rowFin) && $rowFin['cnt'] > 0) : ?>
                    <span
                        class="badge"
                        style="margin-left: 5px; color:#fff; background-color: #d11717;">
                        <?= $rowFin['cnt'] ?>
                    </span>
                <?php endif ?>
            </h3>

            <div class="ml-auto">
                <?php if ($idaluno && $idpermissoes > 0) : ?>
                    <button
                        class="btn primary-color btn-block"
                        onclick="voltar()"
                        style="text-decoration: none;">
                        <i class="fa fa-arrow-alt-circle-left fa-1x"></i>
                        Voltar
                    </button>
                <?php endif ?>
            </div>
        </div>

        <div>
            <ul class="nav nav-tabs" role="tablist">
                <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_aluno"))) { ?>
                    <li role="presentation" class="active">
                        <a href="#tab_alunos" id="but_aluno" aria-controls="tab_alunos" role="tab" data-toggle="tab"><i class="fa fa-user"></i> Aluno </a>
                    </li>
                <?php } ?>

                <?php if (!empty($idaluno) && (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_matricula")))) : ?>
                    <li role="presentation">
                        <a href="#tab_matricula" id="but_matricula" aria-controls="tab_matricula" role="tab" data-toggle="tab"><i class="fa fa-certificate"></i> Matrícula</a>
                    </li>
                <?php endif ?>

                <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_nova_matricula"))) : ?>
                    <li role="presentation">
                        <a href="#tab_nova_matricula" id="but_nova_matricula" aria-controls="tab_nova_matricula" role="tab" data-toggle="tab">
                            <i class="fa fa-certificate"></i> Nova Matrícula
                        </a>
                    </li>
                <?php endif ?>

                <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_dadosadicionais"))) { ?>
                    <li role="presentation">
                        <a href="#tab_dadosadicionais" id="but_dadosadicionais" aria-controls="tab_dadosadicionais" role="tab" data-toggle="tab"><i class="fa fa-address-card"></i> Dados Adicionais</a>
                    </li>
                <?php } ?>

                <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_anexodocumentos"))) { ?>
                    <li role="presentation">
                        <a href="#tab_anexodocumentos" id="but_anexodocumentos" aria-controls="tab_anexodocumentos" role="tab" data-toggle="tab"><i class="fa fa-file-upload"></i> Documentos</a>
                    </li>
                <?php } ?>

                <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_saude"))) { ?>
                    <li role="presentation">
                        <a href="#tab_saude" id="but_saude" aria-controls="tab_saude" role="tab" data-toggle="tab"><i class="fa fa-medkit"></i> Saúde</a>
                    </li>
                <?php } ?>

                <?php if (empty($idaluno)) : ?>
                    <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_cadastro_responsavel"))) { ?>
                        <li role="presentation">
                            <a href="#tab_cadastro_responsavel" id="but_cadastro_responsavel" aria-controls="tab_cadastro_responsavel" role="tab" data-toggle="tab"><i class="fa fa-users"></i>Responsável</a>
                        </li>
                    <?php } ?>
                <?php endif ?>

                <?php if ($idaluno) { ?>
                    <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_responsaveis"))) { ?>
                        <li role="presentation">
                            <a href="#tab_responsaveis" id="but_responsaveis" aria-controls="settings" role="tab" data-toggle="tab"><i class="fa fa-users"></i> Responsáveis</a>
                        </li>
                    <?php } ?>
                    <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_ocorrencias"))) { ?>
                        <li role="presentation">
                            <a href="#tab_ocorrencias" id="but_ocorrencias" aria-controls="tab_ocorrencias" role="tab" data-toggle="tab"><i class="fa fa-clipboard"></i> Ocorrências</a>
                        </li>
                    <?php } ?>
                    <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_entrevistas"))) { ?>
                        <li role="presentation">
                            <a href="#tab_entrevistas" id="but_entrevistas" aria-controls="tab_entrevistas" role="tab" data-toggle="tab"><i class="fa fa-bullhorn"></i> Entrevistas</a>
                        </li>
                    <?php } ?>
                    <?php if (in_array($financeiro[2], $arraydo1)) { ?>
                        <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_financeiro"))) { ?>
                            <li role="presentation">
                                <a href="#tab_financeiro" id="but_financeiro" aria-controls="settings" role="tab" data-toggle="tab"><i class="fa fa-dollar-sign"></i> Financeiro</a>
                            </li>
                        <?php } ?>
                    <?php } ?>

                    <?php if (empty($modo) || (($modo == "ababloqueio") && ($aba == "but_eletivas"))) { ?>
                        <li role="presentation">
                            <a href="#tab_eletivas" id="but_eletivas" aria-controls="tab_eletivas" role="tab" data-toggle="tab"><i class="fa fa-book-open"></i> Eletivas</a>
                        </li>
                    <?php } ?>
                <?php } // if idaluno
                ?>
            </ul>

            <form id="cadastroAlunos" name="cadastroAlunos" action="dao/alunos.php" target="_blank" method="post" class="form-horizontal">
                <input type="hidden" name="action" value="<?= empty($idaluno) ? 'cadastrar' : 'atualizar' ?>" />
                <input type="hidden" name="idaluno" id="idaluno" value="<?= $idaluno ?? '' ?>" />
                <input type="hidden" name="idunidade" id="idunidade" value="<?= $idunidade ?? '' ?>" />
                <input type="hidden" name="idempresa" id="idempresa" value="<?= $idempresa ?? '' ?>" />
                <input type="hidden" name="nummatricula" id="nummatricula" value="<?= $nummatricula ?? '' ?>" />
                <input type="hidden" name="idmatricula" id="idmatricula" value="<?= $idmatricula ?? '' ?>" />
                <input type="hidden" name="aba" id="aba" value="<?= $aba ?? '' ?>" />
                <input type="hidden" name="modo" id="modo" value="<?= $modo ?? '' ?>" />
                <input type="hidden" name="idpessoa" id="idpessoa" value="<?= $idpessoa ?? '' ?>" />
                <input type="hidden" name="idfuncionario" id="idfuncionario" value="<?= $idfuncionario ?? '' ?>">
                <input type="hidden" name="idprospeccao" id="idprospeccao" />
                <input type="hidden" name="racas" id="racas" value="<?= $raca ?>" />


                <div class="tab-content">
                    <?php $this->insert('Academico/Aluno/Aba/Aluno', get_defined_vars()) ?>

                    <?php if ($idaluno) : ?>
                        <?php $this->insert('Academico/Aluno/Aba/Matricula', get_defined_vars()) ?>
                    <?php endif ?>

                    <?php $this->insert(
                        'Academico/Aluno/Aba/NovaMatricula',
                        compact(
                            'contrato_desempenho',
                            'escola_origem',
                            'idfuncionariounidade',
                            'idpermissoes',
                            'periodoPadraoMatricula',
                            'periodosLetivos',
                            'unidades',
                        )
                    ) ?>

                    <?php $this->insert('Academico/Aluno/Aba/DadosAdicionais', get_defined_vars()) ?>
                    <?php $this->insert('Academico/Aluno/Aba/AnexoDocumentos', get_defined_vars()) ?>
                    <?php $this->insert('Academico/Aluno/Aba/Saude', get_defined_vars()) ?>

                    <?php if (empty($idaluno)) : ?>
                        <?php $this->insert('Academico/Aluno/Aba/NovoResponsavel') ?>
                    <?php endif ?>

                    <?php if (!empty($idaluno)) : ?>
                        <?php $this->insert('Academico/Aluno/Aba/Responsaveis', get_defined_vars()) ?>
                        <?php $this->insert('Academico/Aluno/Aba/Ocorrencias', get_defined_vars()) ?>
                        <?php $this->insert('Academico/Aluno/Aba/Entrevistas', get_defined_vars()) ?>
                        <?php $this->insert('Academico/Aluno/Aba/Eletivas', get_defined_vars()) ?>
                    <?php endif ?>
                </div>

                <?php if ($usuario->autorizado('academico-alunos-editar')) : ?>
                    <div style="margin: 15px 0 0 0;">
                        <input type="button" value="<?php echo $idaluno > 0 ? 'Atualizar cadastro' : 'Cadastrar Aluno' ?>" class="btn green-color" onclick="cadastraOuAtualizaAluno('<?= $turmamatricula ?>')" />
                    </div>
                <?php endif ?>

                <form id="formimprimeentrevista" name="formimprimeentrevista" method="post" action="alunos_entrevistas_imprimir.php" target="_blank">
                    <input type="hidden" name="idaluno" id="p_idaluno" value="" />
                    <input type="hidden" name="nummatricula" id="p_nummatricula" value="" />
                    <input type="hidden" name="identrevista" id="p_identrevista" value="" />
                </form>
            </form>
        </div>
    </div>
</div>
