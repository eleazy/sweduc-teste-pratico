<?php
$keys = array_keys($_REQUEST);

foreach ($keys as $k) {
    ${$k} = $_REQUEST[$k];
}

require __DIR__ . '/../../../../public/permissoes.php';

$porPagina = 25;

if (!isset($situacao)) {
    $situacao = 1;
}

$query = "SELECT funcionarios.idunidade as idunidade, funcionarios.id as fid, unidade  FROM funcionarios, unidades WHERE funcionarios.idunidade=unidades.id AND  funcionarios.idpessoa=$idpessoalogin";
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$idfuncionario = $row['fid'];
$idfuncionariounidade = $row['idunidade'];
$nomeunidade = $row['unidade'];

echo '<input type="hidden" name="idfuncionario" id="idfuncionario" value="' . $idfuncionario . '">';
echo '<input type="hidden" name="idfuncionariounidade" id="idfuncionariounidade" value="' . $idfuncionariounidade . '">';
?>

<div id="content-outer">
    <div class="container-fluid">
        <h3 class="print:hidden">
            Alunos | Busca
        </h3>

        <form action="#" method="post" id="mainform" class="print:hidden mb-5">
            <input type="hidden" name="limiteinf" id="limiteinf" value="0">
            <input type="hidden" name="limitesup" id="limitesup" value="<?= $porPagina ?>">
            <div class="box-search">
                <div class="flex flex-wrap -m-1">
                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="idanoletivo">Ano Letivo</label>
                        <select name="idanoletivo" id="idanoletivo" class="form-element">
                            <option
                                <?=$this->selected(!empty($idanoletivo) && 'todos' == $idanoletivo)?>
                                value="todos"
                            >
                                TODOS OS ANOS
                            </option>

                            <?php
                            $query = "SELECT * FROM anoletivo ORDER BY anoletivo ASC";
                            $result = mysql_query($query);
                            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                                ?>
                                <option
                                    value="<?= $row['id'] ?>"
                                    <?=
                                        $this->selected(!empty($idanoletivo) ?
                                        ($row['id'] == $idanoletivo) :
                                        ($row['anoletivo'] == date('Y')))
                                    ?>
                                >
                                    <?= $row['anoletivo'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="situacao">Situação</label>
                        <select name="situacao" id="situacao" class="form-element">
                            <?php foreach ($alunosStatus as $status) : ?>
                                <?php if ($status->mostrarNaBusca == 0) {
                                    continue;
                                } ?>
                                <option value="<?= $status->id ?>" aria-details="<?= $status->confirmaMsg ?>" <?= $this->selected($status->id == $rowA['status']) ?>>
                                    <?= $status->nome ?>
                                </option>
                            <?php endforeach ?>

                            <option value="">Todos</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="idunidadebusca">Unidade</label>
                        <select name="idunidadebusca" id="idunidadebusca" class="form-element">
                            <option
                                <?=$this->selected(!empty($idunidadebusca) && 'todos' == $idunidadebusca)?>  value="todos"
                            >
                                TODOS
                            </option>
                            <?php
                            $query = "SELECT * FROM unidades GROUP BY unidade ORDER BY unidade ASC";
                            $result = mysql_query($query);
                            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { ?>
                                <option
                                    <?=$this->selected(!empty($idunidadebusca) && $row['id'] == $idunidadebusca)?>
                                    value="<?=$row['id']?>"
                                >
                                    <?=$row['unidade']?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="curso">Curso</label>
                        <select data-toggle="tooltip" data-placement="bottom" title="Escolha a unidade para selecionar o curso" name="curso" id="curso" class="form-element">
                            <option
                                value="todos"
                                <?=$this->selected(!empty($curso) && 'todos' == $curso)?>
                            >
                                TODOS
                            </option>

                            <?php
                            if (!empty($curso) && $curso > 0) {
                                $query = "SELECT * FROM cursos WHERE idunidade='$idunidadebusca' ORDER BY curso ASC";

                                $result = mysql_query($query);
                                // echo '<option value=" - " selected="selected"> </option>';
                                if (mysql_num_rows($result) > 0) {
                                    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                                        ?>
                                        <option <?php echo ($row['id'] == $curso) ? "selected=\"selected\"" : ''; ?>
                                            value="<?php echo $row['id'] ?>"><?php  echo $row['curso'] ?> </option>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="serie">
                            Série
                        </label>

                        <select data-toggle="tooltip" data-placement="bottom" title="Escolha o curso para selecionar a série" name="serie" id="serie" class="form-element">
                            <option
                                value="todos"
                                <?=$this->selected(!empty($serie) && 'todos' == $serie)?>
                            >
                                TODOS
                            </option>

                            <?php
                            if (!empty($serie) && $serie > 0) {
                                $query = "SELECT * FROM series WHERE idcurso=" . $curso . " ORDER BY serie ASC";
                                $result = mysql_query($query);
                                // echo '<option value=" - " selected="selected"> </option>';
                                if (mysql_num_rows($result) > 0) {
                                    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                                        ?>
                                        <option <?php echo ($row['id'] == $serie) ? "selected=\"selected\"" : ''; ?>
                                            value="<?php echo $row['id'] ?>"><?php echo $row['serie'] ?> </option>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="turma">
                            Turma
                        </label>

                        <select data-toggle="tooltip" data-placement="bottom" title="Escolha a série para selecionar a turma" name="turma" id="turma" class="form-element">
                            <option
                                value="todos"
                                <?=$this->selected(!empty($turma) && 'todos' == $turma)?>
                            >
                                TODOS
                            </option>

                            <?php
                            if (!empty($turma) && $turma > 0) {
                                $query = "SELECT * FROM turmas WHERE idserie=" . $serie . " ORDER BY turma ASC";
                                $sqlAno = '';
                                if ($anoletivomatricula > 0) {
                                    $sqlAno = " and anoletivomatricula=" . $anoletivomatricula;
                                }
                                $result = mysql_query($query);
                                while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                                    $qalunosnaturma = "SELECT count(*) as qtdemat FROM alunos_matriculas WHERE turmamatricula=" . $row['id'] . $sqlAno . " and  status = 1";
                                    $ralunosnaturma = mysql_query($qalunosnaturma);
                                    $qt = mysql_fetch_array($ralunosnaturma, MYSQL_ASSOC);
                                    ?>
                                    <option <?php echo ($row['id'] == $turma) ? "selected=\"selected\"" : ''; ?>  value="<?=  $row['id'] ?> "><?=  $row['turma'] ?> ( Matriculados: <?=  $qt['qtdemat'] ?> / Limite: <?= (($row['quantalunos'] == 0) ? "Sem limite" : $row['quantalunos']) ?>)</option>';
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="w-full sm:w-2/3 md:w-2/6 p-1">
                        <input type="hidden" name="nomeiniciacontem" value="1">

                        <label for="nome">
                            Nome
                        </label>

                        <input
                            type="search"
                            class="form-element"
                            name="nome"
                            id="nome"
                            placeholder="Digite o nome do(a) aluno(a)"
                            value="<?=$nome ?? '' ;?>"
                        />
                    </div>

                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="codigo">Nº do aluno</label>
                        <input type="text" class="form-element" name="codigo" id="codigo" value="<?=$codigo ?? ''?>" />
                    </div>

                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="mesnascimento" class="whitespace-nowrap">Mês (aniversário)</label>
                        <select name="mesnascimento" id="mesnascimento" class="form-element">
                            <option
                                value="todos"
                                <?=$this->selected(empty($mesnascimento) || $mesnascimento == 'todos')?>
                            >
                                TODOS
                            </option>

                            <?php
                            $arr_meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro']; ?>
                            <?php foreach ($arr_meses as $num => $mes) : ?>
                                <option
                                    value="<?=str_pad($num, 2, "0", STR_PAD_LEFT)?>"
                                    <?=$this->selected(!empty($mesnascimento) && $mesnascimento == $mes)?>
                                >
                                    <?=$mes?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="idsexo">Sexo</label>
                        <select name="idsexo" id="idsexo" class="form-element">
                            <option value="0" selected="selected">TODOS</option>
                            <?php
                                $query = "SELECT * FROM sexo ORDER BY id ASC";
                                $result = mysql_query($query);
                            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {?>
                                    <option value="<?=  $row['id'] ?> "><?=  $row['sexo'] ?></option>';
                            <?php } ?>
                        </select>
                    </div>

                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="presencial">Tipo de aula</label>
                        <select name="presencial" id="presencial" class="form-element">
                            <option value="" selected="selected">Todos</option>
                            <option value="1">Presencial</option>
                            <option value="0">Online</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-1/3 md:w-1/6 p-1">
                        <label for="seguroescolar">Seguro Escolar</label>
                        <select name="seguroescolar" id="seguroescolar" class="form-element">
                            <option value="" selected="selected">Todos</option>
                            <option value="1">Habilitado</option>
                            <option value="0">Desabilitado</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-2/3 md:w-2/6 p-2 flex items-end">
                        <div class="-m-4">
                            <label class="switch-toggle" for="novosalunos">
                                <input
                                    id="novosalunos"
                                    name="novosalunos"
                                    type="checkbox"
                                    value="1"
                                >
                                Apenas Novos Alunos
                            </label>
                        </div>
                    </div>
                </div>

                <hr class=""></hr>

                <div class="flex items-center">
                    <div class="p-1 -m-4">
                        <label class="switch-toggle" for="paginacao">
                            <input
                                id="paginacao"
                                name="paginacao"
                                type="checkbox"
                                value="1"
                                checked
                            >
                            Paginação
                        </label>
                    </div>

                    <div id="label-agrupa-anoletivo" class="p-1 -m-4">
                        <label class="switch-toggle" for="agrupa-anoletivo">
                            <input
                                id="agrupa-anoletivo"
                                name="agrupar_ano_letivo"
                                type="checkbox"
                                value="1"
                            >
                            Agrupar ano letivo
                        </label>
                    </div>

                    <div class="p-1 flex items-center">
                        <select name="ordenacao" id="ordenacao" class="form-element">
                            <option value="1">Ordem alfabética</option>
                            <option value="2">Ordem data de matrícula</option>
                            <option value="3">Ordem data de nascimento</option>
                            <option value="4">Ordem número do aluno</option>
                            <option value="5">Ordem aniversariantes</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="button" name="buscar" id="buscar" class="sw-btn sw-btn-primary"><i class="fa fa-search"></i> Buscar</button>
                </div>
            </div>
        </form>

        <div id="conteudoBusca"></div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $('.form-element').keypress(function (e) {
            if (e.which == 13) {
                buscaAlunos();
                return false;
            }
        });
        toggleAgrupaAnoletivo()
    });
    $("#buscar").click(function () {
        if ($("#idunidadebusca :selected").val() == " - ") {
            swal("Atenção", "Escolha uma unidade para buscar os alunos.", "warning")
        } else {
            document.getElementById('limiteinf').value = "0";
            document.getElementById('limitesup').value = "25";
            buscaAlunos();
        }
    });
    function buscaAlunos() {
        $.ajax({
            url: 'alunos_lista.php',
            type: 'POST',
            data: $("#mainform").serialize(),
            context: $('#conteudoBusca'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function (data) {
                this.html(data);
            }
        });
    }

    $("#idunidadebusca").change(function () {
        pegaCursos();
    });
    $("#idanoletivo").change(function () {
        pegaCursos();
    });

    function pegaCursos() {
        $.ajax({
            url: "dao/cursos.php",
            type: "POST",
            data: {action: "recebeCursos", idunidade: $('#idunidadebusca :selected').val(), anoLetivo: $('#idanoletivo :selected').text(),  usaAnoLimite: true},
            context: jQuery('#curso'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function (data) {
                this.html(data);
            }
        });
    }

    $("#curso").change(function () {
        $.ajax({
            url: "dao/series.php",
            type: "POST",
            data: {action: "recebeSeriesComTurmas", idcurso: $('#curso').val()},
            context: jQuery('#serie'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function (data) {
                this.html('<option value="todos" selected="selected">TODOS</option>' +data);
            }
        });
    });

    $("#serie").change(function () {
        $.ajax({
            url: "dao/turmas.php",
            type: "POST",
            data: {
                action: "recebeTurmas2",
                idserie: $('#serie').val(),
                situacao: $('#situacao :selected').val(),
                anoletivomatricula: $('#idanoletivo :selected').val()
            },
            context: jQuery('#turma'),
            beforeSend: bloqueiaUI,
            complete: $.unblockUI,
            success: function (data) {
                this.html('<option value="todos" selected="selected">TODOS</option>' +data);
            }
        });
    });

    $('#idanoletivo').change(function() {
        toggleAgrupaAnoletivo()
    })

    function toggleAgrupaAnoletivo() {
        let todos = $('#idanoletivo').find('option:selected').val() == 'todos'
        if(todos) {
            $('#label-agrupa-anoletivo').show()
        } else {
            $('#label-agrupa-anoletivo').hide()
        }
    }
</script>

<?php if (isset($_POST['idanoletivo'])) : ?>
    <script>
        $("#buscar").click();
        $("#idunidadebusca").change();
    </script>
<?php else : ?>
    <script>
        $("#idunidadebusca").change();
    </script>
<?php endif ?>
