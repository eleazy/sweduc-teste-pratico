<?php

use App\Model\Core\Usuario;
use Illuminate\Database\Capsule\Manager as DB;
use App\Model\Financeiro\Titulo;
use App\Asaas\Models\Asaas;

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}
$action = $_REQUEST['action'];

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
    $desc1Valor = str_replace(',', '.', str_replace('.', '', $_REQUEST['remessa_desc1_valor']));
    $desc2Valor = str_replace(',', '.', str_replace('.', '', $_REQUEST['remessa_desc2_valor']));
    $desc3Valor = str_replace(',', '.', str_replace('.', '', $_REQUEST['remessa_desc3_valor']));

    if ($id == "") {
        $query  = "SELECT COUNT(*) as cnt FROM financeiro_descontocomercial WHERE titulo='$titulo'";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);

        $cnt = $row['cnt'];
        $id = 0;
        if ($cnt == 0) {
            $query  = "INSERT INTO financeiro_descontocomercial (titulo, descricao, msg_boleto, msg_boletopadrao, remessa_desc1_dia, remessa_desc1_desc, remessa_desc1_valor, remessa_desc2_dia, remessa_desc2_desc, remessa_desc2_valor, remessa_desc3_dia, remessa_desc3_desc, remessa_desc3_valor, remessa_desc1_mesatual, remessa_desc2_mesatual, remessa_desc3_mesatual)
                      VALUES
                      ('$titulo', '$descricao', '$msg_boleto', '$msg_boletopadrao','$remessa_desc1_dia', '$remessa_desc1_desc', '$desc1Valor', '$remessa_desc2_dia', '$remessa_desc2_desc', '$desc2Valor', '$remessa_desc3_dia', '$remessa_desc3_desc', '$desc3Valor', '$remessa_desc1_mesatual', '$remessa_desc2_mesatual', '$remessa_desc3_mesatual');";
            $result = mysql_query($query);
            $id = mysql_insert_id();
            echo $id . "|Desconto cadastrado com sucesso.|" . $titulo . "|" . $descricao;
            $msg = 'Desconto: ' . $titulo . ' cadastrado com sucesso';
            $status = 0;
        } else {
            echo "0| |Desconto já existe!|" . $titulo . "|" . $descricao;
            $msg = 'Desconto já existe!';
            $status = 1;
        }
    } else {
        $query  = "UPDATE financeiro_descontocomercial SET
                        titulo='$titulo',
                        descricao='$descricao',
                        msg_boleto='$msg_boleto',
                        msg_boletopadrao='$msg_boletopadrao',
                        remessa_desc1_dia  = '$remessa_desc1_dia',
                        remessa_desc1_mesatual = '$remessa_desc1_mesatual',
                        remessa_desc1_desc = '$remessa_desc1_desc',
                        remessa_desc1_valor = '$desc1Valor',
                        remessa_desc2_dia = '$remessa_desc2_dia',
                        remessa_desc2_mesatual = '$remessa_desc2_mesatual',
                        remessa_desc2_desc = '$remessa_desc2_desc',
                        remessa_desc2_valor = '$desc2Valor',
                        remessa_desc3_dia = '$remessa_desc3_dia',
                        remessa_desc3_desc = '$remessa_desc3_desc',
                        remessa_desc3_valor = '$desc3Valor',
                        remessa_desc3_mesatual = '$remessa_desc3_mesatual'
                        WHERE id=$id";

        if ($result = mysql_query($query)) {
            echo "blue|Desconto atualizado com sucesso.|atualizado";
            $msg = "Desconto " . $titulo . " atualizado com sucesso.";
            $status = 0;

            // ATUALIZA O DESCONTO NO ASAAS
            // $titulosAsaasUsandoDesconto = DB::select("
            //     SELECT af.id
            //     FROM alunos_fichafinanceira af
            //     JOIN asaas_cobrancas ac ON ac.id_alunos_fichafinanceira = af.id
            //     JOIN alunos a ON a.id = af.idaluno
            //     WHERE af.situacao = 0
            //     AND (
            //         ( a.desconto_comercial = 1 AND a.desconto_comercial_msg = $id )
            //         OR
            //         ( af.id_desconto_comercial = $id )
            //     )
            // ");

            // foreach ($titulosAsaasUsandoDesconto as $titulo) {
            //     try {
            //         $asaas = new Asaas();
            //         $asaas->alterarCobranca($titulo->id);
            //     } catch (\Exception $e) {
            //         continue;
            //     }
            // }
        } else {
            echo "red|Erro na atualização do Desconto.";
            $msg = "Erro na atualização do Desconto.";
            $status = 1;
        }
    }
    $parametroscsv = $id . ',' . $nomeEvento . ',' . $planoparcelamento . ',' . $parcelas;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query  = "DELETE FROM financeiro_descontocomercial WHERE id=$id";
    $queryPegaDescComercial = "SELECT titulo FROM financeiro_descontocomercial WHERE id=$id";
    $resultadoDescCom = mysql_query($queryPegaDescComercial);
    while ($linhaDesc = mysql_fetch_array($resultadoDescCom)) {
        $descDeletado = $linhaDesc['titulo'];
    }
    if ($result = mysql_query($query)) {
        echo "blue|Desconto removido com sucesso.";
        $msg = 'Desconto : ' . $descDeletado . ' removido com sucesso';
        $status = 0;
    } else {
        echo "red|Erro ao remover o Desconto.";
        $msg = 'Erro ao remover o Desconto.';
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "inserirDescontoNoTitulo") {
    $usuario = Usuario::fromSession();

    if (!$usuario->autorizado('alunos-alterar-desconto-comercial')) {
        echo json_encode(['msg' => 'Erro de permissão']);
        http_response_code(403);
        die();
    }

    $descontoComercial = $_REQUEST['descontoComercial'] ?: 'null';
    $requestTitulos = explode(',', mysql_real_escape_string($_REQUEST['titulos']));
    $titulos = "'" . implode("','", $requestTitulos) . "'";

    $backup = DB::select("
        SELECT id, id_desconto_comercial
        FROM alunos_fichafinanceira
        WHERE id IN ($titulos)
    ");
    $backupMap = [];
    foreach ($backup as $row) {
        $backupMap[$row->id] = $row->id_desconto_comercial;
    }

    $query = mysql_query("UPDATE alunos_fichafinanceira SET id_desconto_comercial = $descontoComercial WHERE id IN ($titulos);");
    $result = mysql_affected_rows();
    $errNo = mysql_errno();

    if ($result) {
        $output['msg'] = "Descontos atualizados com sucesso!";
        $output['status'] = "success";

        //ATUALIZA O DESCONTO NO ASAAS
        $titulosAfetados = DB::select("
            SELECT af.id
            FROM alunos_fichafinanceira af
            JOIN asaas_cobrancas ac ON ac.id_alunos_fichafinanceira = af.id
            WHERE af.situacao = 0
            AND af.id IN ($titulos)
        ");

        foreach ($titulosAfetados as $titulo) {
            try {
                $asaas = new Asaas();
                $asaas->alterarCobranca($titulo->id);
            } catch (\Exception $e) {
                foreach ($backupMap as $id => $oldValue) {
                    DB::update("
                        UPDATE alunos_fichafinanceira
                        SET id_desconto_comercial = ?
                        WHERE id = ?
                    ", [$oldValue, $id]);
                }

                $output['msg'] = "Ocorreu um erro ao atualizar desconto no Asaas! Alterações desfeitas.";
                $output['status'] = "error";
                break;
            }
        }
    } elseif (!$result && !$errNo) {
        $output['msg'] = "Nenhum desconto foi alterado!";
        $output['status'] = "warning";
    } else {
        $output['msg'] = "Erro ao processar requisição.";
    }

    echo json_encode($output, JSON_THROW_ON_ERROR);
    http_response_code(!$errNo ? '200' : '400');
}
