<?php

session_start();
include('../headers.php');
include('conectar.php');
require_once('trocasenha.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$agora = date("Y-m-d h:i");
$debug = 0;

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionariologin = $row1['id'];


if ($professor == "") {
    $professor = 0;
} else {
    $professor = 1;
}

if ($action == "apagar") {
    $query = "SELECT idpessoa FROM funcionarios WHERE id='$id'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $idpessoa = $row['idpessoa'];
    $queryParaPegarNome = "SELECT nome FROM pessoas WHERE id = $idpessoa";
    $resultadoDaQueryParaPegarNome = mysql_query($queryParaPegarNome);
    while ($linhaNome = mysql_fetch_array($resultadoDaQueryParaPegarNome)) {
        $nome = $linhaNome['nome'];
    }
    $erro = 1;
    $msg = "Dados pessoais do funcionário " . $nome . " removidos com sucesso.";
    $query  = "DELETE FROM pessoas WHERE id=$idpessoa";
    if ($result = mysql_query($query)) {
        echo "Dados pessoais do funcionário removidos com sucesso.";
        $status = 0;
    } else {
        $erro = $erro + 3;
        $msg = "Erro ao remover dados de pessoais do funcionário.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $idpessoa;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);


    $query  = "DELETE FROM emails WHERE idpessoa=$idpessoa";
    $queryPegaEmail = "SELECT email FROM emails WHERE idpessoa=$idpessoa";
    $resultadoEmailFunc = mysql_query($queryPegaEmail);
    while ($linhaEmail = mysql_fetch_assoc($resultadoEmailFunc)) {
        $emailDeletado = $linhaEmail['email'];
    }
      $msg = "Email:" . $emailDeletado . " do funcionário " . $nome . " removidos com sucesso.";
    if ($result = mysql_query($query)) {
        $status = 0;
    } else {
        $erro = $erro + 3;
        $msg = "Erro ao remover emails do funcionário!";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $idpessoa;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    $query  = "DELETE FROM telefones WHERE idpessoa=$idpessoa";
    if ($result = mysql_query($query)) {
        $msg = "Telefones do funcionário removidos com sucesso.";
        $status = 0;
    } else {
        $erro = $erro + 3;
        $msg = "Erro ao remover telefones do funcionário!";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $idpessoa;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    $query  = "DELETE FROM usuarios WHERE idpessoa=$idpessoa";
    if ($result = mysql_query($query)) {
        $msg = "Dados de usuário do funcionário removidos com sucesso.";
        $status = 0;
    } else {
        $erro = $erro + 3;
        $msg = "Erro ao remover dados de usuário do funcionário.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $idpessoa;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);


    $query  = "DELETE FROM funcionarios WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "green|Funcionário removido com sucesso.";
        $msg = "Funcionário removido com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro $erro ao remover o funcionário!";
        $msg = "Erro $erro ao remover o funcionário!";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $idpessoa;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cadastrar") {
    if ($datanascimento) {
        $dtnascimento = explode("/", $datanascimento);
        $datanascimento = $dtnascimento[2] . "-" . $dtnascimento[1] . "-" . $dtnascimento[0];
    }

    if ($data_expedicao) {
        $data_expedicao = explode("/", $data_expedicao);
        $rg_expedido_em = $data_expedicao[2] . "-" . $data_expedicao[1] . "-" . $data_expedicao[0];
    }

    $data = compact(
        'idpaisnascimento',
        'idestadonascimento',
        'idcidadenascimento',
        'idpais',
        'idestado',
        'idcidade',
        'bairro',
        'nome',
        'datanascimento',
        'idsexo',
        'idestadocivil',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'rg',
        'orgaoexp',
        'rg_expedido_em',
        'cpf'
    );

    $data = array_map('mysql_real_escape_string', $data);
    $data = array_filter($data);

    $idpessoa = 0;
    $query = "INSERT INTO pessoas (" . join(',', array_keys($data)) . ") VALUES ('" . join('\',\'', $data) . "')";
    if ($result = mysql_query($query)) {
        $idpessoa = mysql_insert_id();

        if (!empty($email)) {
            $qemail = "INSERT INTO emails (idpessoa,email,primario) VALUES ($idpessoa,'$email',1)";
            $remail = mysql_query($qemail);
        }

        $msg = "Dados pessoais do funcionário " . $data['nome'] . " cadastrados com sucesso.";
        $status = 0;
    } else {
        $erro = mysql_error();
        $msg = "Erro $erro ao cadastrar dados pessoais do funcionário!";
        $status = 1;
    }

    $parametroscsv = $idpessoa . ',' . $idestadonascimento . ',' . $idpais . ',' . $idestado . ',' . $idcidade . ',' . $bairro . ',' . $nome . ',' . $datanascimento . ',' . $idsexo . ',' . $idestadocivil . ',' . $cep . ',' . $logradouro . ',' . $numero . ',' . $complemento . ',' . $rg . ',' . $orgaoexp . ',' . $cpf;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    $idfuncionario = 0;

    $data = compact(
        'idpessoa',
        'idunidade',
        'iddepartamento',
        'professor',
        'numeroprof',
        'cursoprof',
        'pis',
        'grauescolar',
        'formacaoescolar',
        'comissao',
        'banco',
        'conta',
        'agencia',
        'registro_mec'
    );

    $data = array_map('mysql_real_escape_string', $data);
    $data = array_filter($data);

    if (!isset($data['professor'])) {
        $data['professor'] = 0;
    }

    if (!isset($data['numeroprof'])) {
        $data['numeroprof'] = 0;
    }

    if (!isset($data['cursoprof'])) {
        $data['cursoprof'] = 0;
    }

    if (!isset($data['pis'])) {
        $data['pis'] = '';
    }

    if (!isset($data['grauescolar'])) {
        $data['grauescolar'] = '';
    }

    if (!isset($data['formacaoescolar'])) {
        $data['formacaoescolar'] = '';
    }

    if (!isset($data['comissao'])) {
        $data['comissao'] = 0;
    }

    if ($data['idpessoa']) { // idescolaorigem, anoorigem, cursoorigem,   '$idescolaorigem', '$anoorigem', '$cursoorigem',
        $query = "INSERT INTO funcionarios(" . join(',', array_keys($data)) . ") VALUES ('" . join('\',\'', $data) . "')";
        if ($result = mysql_query($query)) {
            $idfunc = mysql_insert_id();
            $msg = "Dados do funcionário " . $data['nome'] . " cadastrados com sucesso.";
            $status = 0;
        } else {
            $erro = mysql_error();
            $msg = "Erro $erro ao cadastrar dados do funcionário!";
            $status = 1;
        }
        $parametroscsv = $idfunc . ',' . $idpessoa . ',' . $idunidade . ',' . $iddepartamento . ',' . $professor . ',' . $numeroprof . ',' . $cursoprof . ',' . $pis . ',' . $grauescolar . ',' . $formacaoescolar;
        salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    /***** USUÁRIO *******************************************/
        if ($usuario != "") {
            $idusuario = 0;

            // API
            $passhash = password_hash($senha, PASSWORD_DEFAULT);
            $apikey = generateApiKey();
            // API

            $query = "INSERT INTO usuarios(idpessoa,tipo,idpermissao,login,senha, password_hash, api_key) VALUES ($idpessoa,'2',$idpermissao,'$usuario','$senha','$passhash','$apikey');";

            if (validaSenha($senha) && $result = mysql_query($query)) {
                $idusuario = mysql_insert_id();
                $msg = "Dados de usuário do funcionário " . $data['nome'] . " cadastrados com sucesso.";
                $status = 0;
            } else {
                $msg = "Erro $erro ao cadastrar dados de usuário do funcionário!";
                $status = 1;
            }
            $parametroscsv = $idusuario . ',' . $idpessoa . ',' . $idunidade . ',' . $idpermissao . ',' . $usuario . ',' . $senha;
            salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);
        }
    }

    // Telefones
    // Faz escape das variáveis de telefone
    $telefone = array_map('mysql_real_escape_string', $telefone);
    $telefones = array_filter(array_combine($telefone, $tipotelefone), 'strlen', ARRAY_FILTER_USE_KEY);
    $telefonesExistentes = [];

    $selectTelefonesQuery = mysql_query(
        "SELECT id, idpessoa, idtipotel, telefone FROM telefones WHERE idpessoa = '$idpessoa';"
    );

    while ($telefoneRow = mysql_fetch_assoc($selectTelefonesQuery)) {
        $tel = $telefoneRow['telefone'];
        $tipo = $telefoneRow['idtipotel'];

        $telefonesExistentes[$tel] = $tipo;
    }

    $deletarTelefonesValues = join(
        ' ',
        array_map(
            fn($tel, $tipo) => "AND NOT (telefone = '$tel' AND idtipotel = '$tipo')",
            array_keys($telefones),
            $telefones
        )
    );
    $deletarTelefonesQuery = mysql_query("DELETE FROM telefones WHERE idpessoa = '$idpessoa' $deletarTelefonesValues");

    $telefonesNovos = array_diff_assoc($telefones, $telefonesExistentes);
    $inserirTelefonesValues = join(
        ",",
        array_map(fn($tel, $tipo) => "('$idpessoa', '$tipo', '$tel')", array_keys($telefonesNovos), $telefonesNovos)
    );
    $inserirTelefonesQuery = mysql_query("INSERT INTO telefones (idpessoa, idtipotel, telefone) VALUES $inserirTelefonesValues");
    // FIM Telefones

    if ($idfunc > 0) {
        echo "green|Funcionário cadastrado com sucesso.|";
        $msg = "Funcionário cadastrado " . $data['nome'] . " com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar o funcionário!|";
        $msg = "Erro ao cadastrar o funcionário!";
        $status = 1;
    }
    $parametroscsv = $idfunc;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "editar") {
    if ($datanascimento) {
        $dtnascimento = explode("/", $datanascimento);
        $datanascimento = $dtnascimento[2] . "-" . $dtnascimento[1] . "-" . $dtnascimento[0];
    }

    if ($data_expedicao) {
        $data_expedicao = explode("/", $data_expedicao);
        $dataexpecao = $data_expedicao[2] . "-" . $data_expedicao[1] . "-" . $data_expedicao[0];
    }

    $erro = 0;
    $idpessoa = mysql_real_escape_string($idpessoa);

    $sql_set = [
        'idpaisnascimento' => $idpaisnascimento,
        'idestadonascimento' => $idestadonascimento,
        'idcidadenascimento' => $idcidadenascimento,
        'idpais' => $idpais,
        'idestado' => $idestado,
        'idcidade' => $idcidade,
        'bairro' => $bairro,
        'nome' => trim($nome),
        'datanascimento' => $datanascimento,
        'idsexo' => $idsexo,
        'idestadocivil' => $idestadocivil,
        'cep' => $cep,
        'logradouro' => $logradouro,
        'numero' => $numero,
        'complemento' => $complemento,
        'rg' => $rg,
        'orgaoexp' => $orgaoexp,
        'rg_expedido_em' => $dataexpecao,
        'cpf' => $cpf
    ];

    $sql_set = array_map('mysql_real_escape_string', $sql_set);
    $sql_set = array_filter($sql_set);
    $sql_set = array_map(
        fn($field, $column) => "$column = '$field'",
        $sql_set,
        array_keys($sql_set)
    );

    $query = "UPDATE pessoas SET " . join(',', $sql_set) . " WHERE id='$idpessoa'";

    if ($result = mysql_query($query)) {
        $msg = "Dados pessoais do funcionário " . $nome . " alterados com sucesso.";
        $status = 0;
    } else {
        $err = mysql_error();
        $erro++;
        $msg = "Erro ao alterar dados pessoais do funcionário!";
        $status = 1;
    }

    $parametroscsv = $idpessoa . ',' . $idpaisnascimento . ',' . $idestadonascimento . ',' . $idcidadenascimento . ',' . $idpais . ',' . $idestado . ',' . $idcidade . ',' . $bairro . ',' . $nome . ',' . $datanascimento . ',' . $idsexo . ',' . $idestadocivil . ',' . $cep . ',' . $logradouro . ',' . $numero . ',' . $complemento . ',' . $rg . ',' . $orgaoexp . ',' . $cpf;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    $checaemail = "SELECT id, email FROM emails WHERE idpessoa=$idpessoa ORDER BY primario IS NULL, primario LIMIT 1";
    $resemail   = mysql_query($checaemail);
    $rowemail   = mysql_fetch_array($resemail, MYSQL_ASSOC);

    if (empty($rowemail['id'])) {
        $qemail = "INSERT INTO emails (idpessoa,email,primario) VALUES ($idpessoa,'$email', 1)";
        $remail = mysql_query($qemail);
    } else {
        $queryem = "UPDATE emails SET email='$email', primario=1 WHERE id=" . $rowemail['id'];
        $rem = mysql_query($queryem);
    }

    // Telefones
    // Faz escape das variáveis de telefone
    $telefone = array_map('mysql_real_escape_string', $telefone);
    $telefones = array_filter(array_combine($telefone, $tipotelefone), 'strlen', ARRAY_FILTER_USE_KEY);
    $telefonesExistentes = [];

    $selectTelefonesQuery = mysql_query(
        "SELECT id, idpessoa, idtipotel, telefone FROM telefones WHERE idpessoa = '$idpessoa';"
    );

    while ($telefoneRow = mysql_fetch_assoc($selectTelefonesQuery)) {
        $tel = $telefoneRow['telefone'];
        $tipo = $telefoneRow['idtipotel'];

        $telefonesExistentes[$tel] = $tipo;
    }

    $deletarTelefonesValues = join(
        ' ',
        array_map(
            fn($tel, $tipo) => "AND NOT (telefone = '$tel' AND idtipotel = '$tipo')",
            array_keys($telefones),
            $telefones
        )
    );
    $deletarTelefonesQuery = mysql_query("DELETE FROM telefones WHERE idpessoa = '$idpessoa' $deletarTelefonesValues");

    $telefonesNovos = array_diff_assoc($telefones, $telefonesExistentes);
    $inserirTelefonesValues = join(
        ",",
        array_map(fn($tel, $tipo) => "('$idpessoa', '$tipo', '$tel')", array_keys($telefonesNovos), $telefonesNovos)
    );
    $inserirTelefonesQuery = mysql_query("INSERT INTO telefones (idpessoa, idtipotel, telefone) VALUES $inserirTelefonesValues");
    // FIM Telefones

    $sql_set = [
        'idunidade' => $idunidade,
        'iddepartamento' => $iddepartamento,
        'professor' => $professor,
        'numeroprof' => $numeroprof,
        'cursoprof' => $cursoprof,
        'pis' => $pis,
        'grauescolar' => $grauescolar,
        'formacaoescolar' => $formacaoescolar,
        'comissao' => $comissao,
        'banco' => $banco,
        'conta' => $conta,
        'agencia' => $agencia,
        'registro_mec' => $registro_mec
    ];

    $sql_set = array_map('mysql_real_escape_string', $sql_set);
    $sql_set = array_map(
        fn($field, $column) => "$column = '$field'",
        $sql_set,
        array_keys($sql_set)
    );

    $query1 = "UPDATE funcionarios SET " . join(',', $sql_set) . " WHERE id=$id";

    if ($result1 = mysql_query($query1)) {
        $msg = "Dados do funcionário " . $nome . " alterados com sucesso.";
        $status = 0;
    } else {
        $erro++;
        $msg = "Erro ao alterar dados do funcionário!";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $idunidade . ',' . $iddepartamento . ',' . $professor . ',' . $numeroprof . ',' . $cursoprof . ',' . $pis . ',' . $grauescolar . ',' . $formacaoescolar;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    /***** USUÁRIO *******************************************/
    $query = "SELECT COUNT(*) as cnt FROM usuarios WHERE idpessoa=" . $idpessoa;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $query = "";
    // API
    $passhash = password_hash($senha, PASSWORD_DEFAULT);
    $apikey = generateApiKey();
    // API

    $result = false;
    if (validaSenha($senha)) {
        if ($row['cnt'] > 0) {
            $query = "UPDATE usuarios SET idpermissao=$idpermissao, politica_grupo_id=$idpermissao, login='$usuario', senha='$senha', password_hash='$passhash', api_key='$apikey' WHERE idpessoa=" . $idpessoa;
        } elseif ($usuario != "") {
            $query = "INSERT INTO usuarios(idpessoa,tipo,idpermissao,politica_grupo_id,login,senha, password_hash, api_key)VALUES($idpessoa,'2',$idpermissao, $idpermissao,'$usuario','$senha','$passhash','$apikey');";
        }

        $result = mysql_query($query);
    }

    if ($result) {
        $msg = "Dados de usuário do funcionário " . $nome . " alterados com sucesso.";
        $status = 0;
    } else {
        $erro++;
        $msg = "Erro ao alterar dados de usuário do funcionário!";
        $status = 1;
    }

    $parametroscsv = $idpessoa . ',' . $idunidade . ',' . $idpermissao . ',' . $usuario . ',' . $senha;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    if ($debug == 1) {
        echo "<br>USUÁRIO => " . $query . " " . $result . "<br><br>";
    }

    if ($erro == 0) {
        echo "green|Funcionário atualizado com sucesso.|" . $id;
        $msg = "Funcionário atualizado " . $nome . " com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ($erro) ao atualizar dados do funcionário!";
        $msg = "Erro ($erro) ao atualizar dados do funcionário!";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $erro;
    salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeFuncionariosPorDepartamento") {
    $query  = "SELECT pessoas.nome, funcionarios.id as fid FROM funcionarios, pessoas WHERE pessoas.id>0 AND funcionarios.iddepartamento=$iddepartamento AND funcionarios.idpessoa=pessoas.id GROUP BY nome ORDER BY nome ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['fid'] . '">' . $row['nome'] . '</option>';
    }
} elseif ($action == "recebeProfessores") {
    $idf = [];

    if ($idgrade) {
        $query  = "SELECT idfuncionario as idf FROM grade_funcionario WHERE idgrade=$idgrade";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $idf[] = $row['idf'];
        }
    }

    $query  = "(SELECT pessoas.nome, funcionarios.id as fid FROM funcionarios, pessoas WHERE funcionarios.idunidade=$idunidade AND funcionarios.idpessoa=pessoas.id AND funcionarios.professor=1 GROUP BY nome ) UNION (SELECT pessoas.nome, funcionarios.id as fid FROM funcionarios, usuarios, permissoes, pessoas WHERE funcionarios.professor=1 AND funcionarios.idpessoa=usuarios.idpessoa AND usuarios.idpessoa=pessoas.id AND usuarios.idpermissao=permissoes.id AND permissoes.unidades like '%$idunidade%' GROUP BY nome) ORDER BY nome ASC";

//  $query  = "SELECT pessoas.nome, funcionarios.id as fid FROM funcionarios, pessoas WHERE funcionarios.idunidade=$idunidade AND funcionarios.idpessoa=pessoas.id AND funcionarios.professor=1 ORDER BY nome ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['fid'] . '" ';
        if ($idf) {
            if (in_array($row['fid'], $idf)) {
                echo " selected ";
            }
        }
        echo '>' . $row['nome'] . '</option>';
    }
} elseif ($action == "recebeFuncionarios") {
    $query = "SELECT nome, funcionarios.id as fid FROM pessoas, funcionarios, permissoes, usuarios,unidades_empresas WHERE usuarios.idpessoa=funcionarios.idpessoa AND funcionarios.idunidade=unidades_empresas.idunidade AND unidades_empresas.idempresa=$idempresa AND usuarios.idpermissao=permissoes.id AND pessoas.id=funcionarios.idpessoa AND substring(permissoes.financeiro,3,1) IN (1,3,5,7) GROUP BY nome";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['fid'] . '">' . $row['nome'] . '</option>';
    }
}
