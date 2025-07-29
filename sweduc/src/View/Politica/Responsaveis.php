<div id="content-outer">
    <div class="container-fluid">
        <h3>Configurações | Escola | Perfil de Pais</h3>

        <div class="box-search">
            <?php foreach ($unidades as $row) : ?>
                <h2 class="section-forms">Unidade: <?= $row['unidade'] ?></h2>

                <?php foreach ($row['cursos'] as $rowC) : ?>
                    <?php $pais = str_pad($rowC['perfilpais'], 16); ?>
                    <?php $paisAtalhos = str_pad($rowC['perfilpaisAtalhos'], 16); ?>

                    <h3 style="margin-top: 0;">Curso: <?=$rowC['curso']?></h3>

                    <div class="hidden">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-0" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[0] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-0">Perfil</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-0" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[0] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-0">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-1" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[1] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-1">Financeiro</label>
                         <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-1" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[1] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-1">Atalho</label>
                        </div>
                    </div>

                    <div class="hidden">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-2" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[2] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-2">Histórico</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-2" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[2] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-2">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-3" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[3] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-3">Boletim</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-3" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[3] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-3">Atalho</label>
                        </div>
                    </div>

                    <div hidden class="col-md-3">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-4" value="<?=$row['id']?>" />
                        <label for="pa<?=$rowC['id']?>-4">Boletim + Avaliações</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-4" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[4] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-4">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-5" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[5] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-5">Avaliações</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-5" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[5] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-5">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-6" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[6] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-6">Ocorrencias</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-6" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[6] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-6">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-7" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[7] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-7">Dados cadastrais</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-7" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[7] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-7">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-8" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[8] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-8">Solicitações</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-8" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[8] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-8">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-9" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[9] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-9">Planejamento</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-9" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[9] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-9">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-10" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[10] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-10">Trabalho de casa</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-10" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[10] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-10">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-11" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[11] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-11">Arquivos</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-11" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[11] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-11">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-12" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[12] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-12">Boletim eletivas</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-12" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[12] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-12">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-13" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[13] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-13">Informe de rendimentos</label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-13" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[13] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-13">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-6 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-14" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[14] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-14">

                            Questionário de Sindrome Geral
                            <i
                                class="fa fa-info-circle"
                                style="color: #ffaa00;"
                                data-toggle="tooltip"
                                data-title=
                                "Devido a pandemia, construimos um questionário
                                onde o responsável pode reportar o estado
                                de saude do aluno em relação a doenças respiratórias">
                            </i>
                        </label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-14" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[14] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-14">Atalho</label>
                        </div>
                    </div>

                    <div class="col-md-3 flex">
                        <input type="checkbox" class="checa<?=$rowC['id']?>" name="pa[]" id="pa<?=$rowC['id']?>-15" value="<?=$row['id']?>" <?=$this->eif('checked', $pais[15] == "1")?> />
                        <label for="pa<?=$rowC['id']?>-15">
                            Modificar presencial
                        </label>
                        <div>
                            <input type="checkbox" class="ml-3 checaAtalho<?=$rowC['id']?>" id="atalho<?=$rowC['id']?>-15" value="<?=$row['id']?>" <?=$this->eif('checked', $paisAtalhos[15] == "1")?> />
                            <label for="atalho<?=$rowC['id']?>-15">Atalho</label>
                        </div>
                    </div>

                    <div class="col-xs-12 text-right">
                        <input type="button" style="margin-top: 25px;" class="btn green-color" value="Atualizar" onClick="atualizar(<?=$rowC['id']?>); atualizarAtalhos(<?=$rowC['id']?>);" />
                    </div>

                    <div class="clearfix"></div>

                    <div class="hr-line-dashed"></div>
                <?php endforeach ?>
            <?php endforeach ?>
        </div>
    </div>
</div>

<script type="text/javascript">
function atualizar(idcurso) {
    stringfinal="";
    $(".checa"+idcurso).each(function() {
        var chk = '';
        if ($(this).is(":checked")) {
            chk = '1';
        } else {
            chk = '0';
        }
        stringfinal = stringfinal + chk;
    });

    $.ajax({
        url: "dao/perfilfuncionarios.php",
        type: 'POST',
        context: jQuery('#resultados'),
        data: {
            action: "paisalunos",
            campo: "perfilpais",
            paisalunos: stringfinal,
            idcurso: idcurso,
        },
        beforeSend: bloqueiaUI,
        complete: $.unblockUI,
        success: function(data){
            var resposta = data.split("|");
            if (resposta[0] == 'blue') {
                criaAlerta('success', 'Perfil atualizado com sucesso');
            } else {
                criaAlerta('error', 'Ocorreu um erro ao tentar atualizar o perfil. Tente novamente');
            }
        }
    });
}

function atualizarAtalhos (idcurso) {
    var stringAtalhos = "";

    const atalhos = document.querySelectorAll('.checaAtalho'+idcurso);
    atalhos.forEach((atalho) => {
        const checkbox = document.getElementById(`pa${idcurso}-${atalho.id.split('-')[1]}`);

        if (checkbox.checked && atalho.checked) {
            stringAtalhos += '1';
        } else {
            stringAtalhos += '0';
        }
    });

    $.ajax({
        url: "dao/perfilfuncionarios.php",
        type: 'POST',
        context: jQuery('#resultados'),
        data: {
            action: "paisalunos",
            campo: "perfilpaisAtalhos",
            paisalunos: stringAtalhos,
            idcurso: idcurso,
        },
        beforeSend: bloqueiaUI,
        complete: $.unblockUI,
        success: function(data){
            var resposta = data.split("|");
            if (resposta[0] == 'blue') {
                criaAlerta('success', 'Atalhos atualizados com sucesso');
            } else {
                criaAlerta('error', 'Ocorreu um erro ao tentar atualizar os atalhos. Tente novamente');
            }
        }
    });
}

setTimeout(function() {
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    })
}, 200)
</script>

