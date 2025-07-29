<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");
$keys = array_keys($_REQUEST);
foreach ($keys as $k) {
    ${$k} = $_REQUEST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

$codigotiposervico = substr($codigotributacaomunicipio, 0, 4);
$mora = str_replace(',', '.', str_replace('.', '', $mora));
$multa = str_replace(',', '.', str_replace('.', '', $multa));

if ($action == "cadastraEmpresa") {
    $codigotiposervico = substr($codigotributacaomunicipio, 0, 4);
    if ($id == 0) { //CADASTRO
        $cidades = explode("@", $cidades);
        $idcidade = $cidades[0];
        $codigomunicipio = $cidades[1];

        $query  = "INSERT INTO empresas (razaosocial, nomefantasia, endereco,numero,complemento,bairro,cep, cnpj, inscricaomunicipal, incentivadorcultural, optantesimplesnacional, regimetributacao, naturezaoperacao, aliquotaISS, codigoCNAE, idcidade, uf, codigotiposervico, codigomunicipio, codigotributacaomunicipio, numlote, serieRPS, multa, mora, tiporecibo, recibo_vias, titulofichafinanceira ) VALUES ('$razaosocial', '$nomefantasia','$endereco','$numero','$complemento','$bairro','$cep', '$cnpj', '$inscricaomunicipal', $incentivadorcultural, $optantesimples,0,1, '$iss','$codigocnae', '$idcidade', '$uf', '$codigotiposervico', '$codigomunicipio', '$codigotributacaomunicipio',0,0, '$multa', '$mora', $tiporecibo, $recibo_vias, '$titulofichafinanceira' );";
        $result = mysql_query($query);

        if ($result) {
            $msg = "Empresa " . $nomefantasia . " CNPJ: $cnpj " . " cadastrada.";
            echo "blue|$msg";
            $status = 0;
            $id = mysql_insert_id();
            $query  = "UPDATE configuracoes SET perdebolsa='$perdebolsaAtualiza'";
            $result = mysql_query($query);
        } else {
            $msg = "Erro ao cadastrar empresa $nomefantasia($razaosocial / CNPJ: $cnpj)!";
            echo "red|$msg";
            $status = 1;
        }

        $parametroscsv = $id . ',' . $razaosocial;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "updateEmpresa") {
    if ($regimetributacao == "") {
        $regimetributacao = 0;
    }
    if ($naturezaoperacao == "") {
        $naturezaoperacao = 0;
    }
    if ($codigotiposervico == "") {
        $codigotiposervico = 0;
    }
    if ($numlote == "") {
        $numlote = 0;
    }
    if ($multa == "") {
        $multa = 0;
    }
    if ($mora == "") {
        $mora = 0;
    }
    //numlote=$numlote,  serieRPS
    $cidades = explode("@", $cidades);
    $idcidade = $cidades[0];
    $codigomunicipio = $cidades[1];
    $eventoFinanceiroRematricula = filter_var($_REQUEST['evento-rematricula'] ?? 0, FILTER_VALIDATE_INT);

    $query  = "UPDATE empresas SET
        razaosocial='$razaosocial',
        nomefantasia='$nomefantasia',
        endereco='$endereco',
        numero='$numero',
        complemento='$complemento',
        bairro='$bairro',
        cep='$cep',
        cnpj='$cnpj',
        inscricaomunicipal='$inscricaomunicipal',
        incentivadorcultural=$incentivadorcultural,
        optantesimplesnacional=$optantesimples,regimetributacao=$regimetributacao,
        naturezaoperacao=$naturezaoperacao,
        aliquotaISS='$iss',codigoCNAE='$codigocnae',
        idcidade='$idcidade',
        uf='$uf',
        codigotiposervico='$codigotiposervico',
        codigomunicipio='$codigomunicipio',
        codigotributacaomunicipio='$codigotributacaomunicipio',
        titulofichafinanceira='$titulofichafinanceira',
        multa='$multa',
        mora='$mora',
        tiporecibo='$tiporecibo',
        recibo_vias='$recibo_vias',
        rematricula_evento_financeiro_id='$eventoFinanceiroRematricula'
        WHERE id=$id";

    if ($result = mysql_query($query)) {
        echo "blue|Cadastro da empresa atualizada.";
        $msg = "Cadastro da empresa " . $nomefantasia . " com o CNPJ: " . $cnpj . " foi atualizado.";
        $status = 0;
        $query  = "UPDATE configuracoes SET perdebolsa='$perdebolsaAtualiza'";
        $result = mysql_query($query);
    } else {
        echo htmlentities("red|Erro na atualização da empresa.");
        $msg = "Erro na atualização da empresa.";
        $status = 1;
    }
    $parametroscsv = $id . "," . $razaosocial;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagaEmpresa") {
    $queryParaPegarNome =  "SELECT nomefantasia FROM empresas WHERE id=" . $id;
    $resultado = mysql_query($queryParaPegarNome);
    while ($row = mysql_fetch_assoc($resultado)) {
        $nomefantasia = $row['nomefantasia'];
    }
    $msg1 = "Cadastro da empresa " . $nomefantasia . "foi removido.";
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg1);
    $query  = "DELETE FROM empresas WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Cadastro da empresa  removido.";
        $msg = "Cadastro da empresa " . $nomefantasia . " removido.";
        $status = 0;
    } else {
        echo htmlentities("red|Erro ao remover empresa.");
        $msg = "Erro ao remover empresa.";
        $status = 1;
    }
} elseif ($action == "recebeEmpresasDaUnidade") {
    $query  = "SELECT empresas.id, empresas.razaosocial FROM empresas, unidades_empresas WHERE unidades_empresas.idempresa=empresas.id AND unidades_empresas.idunidade=" . $idunidade;
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['razaosocial'] . '</option>';
    }
} elseif ($action == "recebeEmpresasDoPerfil") {
    $id_permissao = $_SESSION['permissao'];
    $id_unidade   = $_SESSION['id_unidade'];

    $query  = "SELECT empresas.id, empresas.razaosocial FROM empresas";
    if ($id_permissao != 1) {
        $query .= " JOIN unidades_empresas ON unidades_empresas.idempresa=empresas.id WHERE unidades_empresas.idunidade IN ((SELECT unidades FROM permissoes where id='$id_permissao' LIMIT 1), $id_unidade) ";
    }

    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['razaosocial'] . '</option>';
    }
} elseif ($action == "empresasUnidades") {
    $query  = "SELECT empresas.id, empresas.razaosocial FROM empresas, unidades_empresas WHERE unidades_empresas.idempresa=empresas.id AND unidades_empresas.idunidade=" . $idunidade;

    $result = mysql_query($query);
    $options = '<option value="-1" selected="selected"> - </option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $options .= '<option value="' . $row['id'] . '">' . $row['razaosocial'] . '</option>';
    }

    echo $options;
}
