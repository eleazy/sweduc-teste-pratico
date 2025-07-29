<?php
include_once '../../dao/conectar.php';

$keys = array_keys($_REQUEST);

foreach ($keys as $k) {
    ${$k} = $_REQUEST[$k];
}

$idlanca = explode(",", $qrlancamento);
$query = "SELECT
        banconum
    FROM
        alunos_fichafinanceira,
        contasbanco,
        empresas,
        unidades,
        unidades_empresas,
        alunos_matriculas,
        cidades
    WHERE
        (unidades.cidade=cidades.id OR unidades.cidade=0) AND
        alunos_fichafinanceira.nummatricula = alunos_matriculas.nummatricula AND
        unidades_empresas.idempresa=empresas.id AND
        unidades_empresas.idunidade=unidades.id AND
        alunos_fichafinanceira.idaluno=alunos_matriculas.idaluno AND
        alunos_matriculas.idunidade=unidades.id AND
        alunos_fichafinanceira.idcontasbanco=contasbanco.id AND
        contasbanco.idempresa=empresas.id AND
        alunos_fichafinanceira.id='{$idlanca[0]}'";

$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
?>

<style type="text/css">
@media all {
    .page-break {
        display: none;
    }
}

@media print {
    .page-break {
        display: block;
        page-break-before: always;
    }
}

@page {
    /* this affects the margin in the printer settings */
    margin: 5mm;
    /*size:8.27in 11.69in;     */
}
</style>

<?php
$i = 0;

foreach ($idlanca as $key => $idl) {
    $i++;
    echo "<iframe src='fazboleto.qrcode.php?idl=" . $idl . "' class='ifr' style='width:1100px;top:0px;left:0px;border-bottom:3px dashed #000;' frameborder='0' scrolling='no' height='565px' id='fazbol" . $idl . "' ></iframe><br />";
    if ($i == 3) {
        echo '<div class="page-break"></div>';
        $i = 0;
    }
}
?>
