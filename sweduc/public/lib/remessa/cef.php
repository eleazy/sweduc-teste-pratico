<?php

include('conectar.php');
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$titulos = "'206777','206778','206779','206780','206781','206782','206783','206784','206785','206786','206787', '206788','206789','206790','206791','206792','206793','206794','206795','206796'";

$hoje = date("dmY");
$agora = date("His");

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

function limpaAcentos($id)
{
    $LetraProibi = [",", ".", "'", "\"", "&", "|", "!", "#", "$", "¨", "*", "(", ")", "`", "´", "<", ">", ";", "=", "+", "§", "{", "}", "[", "]", "^", "~", "?", "%"];
    $special = ['Á', 'È', 'ô', 'Ç', 'á', 'è', 'Ò', 'ç', 'Â', 'Ë', 'ò', 'â', 'ë', 'Ø', 'Ñ', 'À', 'Ð', 'ø', 'ñ', 'à', 'ð', 'Õ', 'Å', 'õ', 'Ý', 'å', 'Í', 'Ö', 'ý', 'Ã', 'í', 'ö', 'ã', 'Î', 'Ä', 'î', 'Ú', 'ä', 'Ì', 'ú', 'Æ', 'ì', 'Û', 'æ', 'Ï', 'û', 'ï', 'Ù', '®', 'É', 'ù', '©', 'é', 'Ó', 'Ü', 'Þ', 'Ê', 'ó', 'ü', 'þ', 'ê', 'Ô', 'ß', '‘', '’', '‚', '“', '”', '„'];
    $clearspc = ['a', 'e', 'o', 'c', 'a', 'e', 'o', 'c', 'a', 'e', 'o', 'a', 'e', 'o', 'n', 'a', 'd', 'o', 'n', 'a', 'o', 'o', 'a', 'o', 'y', 'a', 'i', 'o', 'y', 'a', 'i', 'o', 'a', 'i', 'a', 'i', 'u', 'a', 'i', 'u', 'a', 'i', 'u', 'a', 'i', 'u', 'i', 'u', '', 'e', 'u', 'c', 'e', 'o', 'u', 'p', 'e', 'o', 'u', 'b', 'e', 'o', 'b', '', '', '', '', '', ''];
    $newId = str_replace($special, $clearspc, $id);
    $newId = str_replace($LetraProibi, "", trim($newId));
   //return strtolower($newId);
    return $newId;
}

$nsa = 1; // Ligar 0800-726-0104, e confirmar próximo NSA esperado quando for enviar a primeira remessa em produção.
        // nsa na tabela EMPRESAS !!
$nsl = 1;
$valorlote = 0;
$qtdtitulos = 0;
$query = "SELECT idaluno, agencia, dvagencia, convenio, razaosocial, cnpj, DATE_FORMAT(dataemissao,'%d%m%Y') AS 'dtemissao', DATE_FORMAT(datavencimento,'%d%m%Y') AS 'dtvenc', titulo, valor FROM alunos_fichafinanceira, empresas, contasbanco WHERE alunos_fichafinanceira.idcontasbanco=contasbanco.id AND contasbanco.idempresa=empresas.id AND alunos_fichafinanceira.titulo IN ($titulos)";
$result = mysql_query($query);
//echo $query;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $cnpj = str_pad(substr(preg_replace('#[^0-9]#', '', $row['cnpj']), 0, 14), 14, "0", STR_PAD_LEFT);
    $agencia = str_pad(substr($row['agencia'] . $row['dvagencia'], 0, 6), 6, "0", STR_PAD_LEFT);
    $convenio = str_pad(substr($row['convenio'], 0, 6), 6, "0", STR_PAD_LEFT);
    $empresa = str_pad(substr(limpaAcentos($row['razaosocial']), 0, 30), 30, "0", STR_PAD_LEFT);

    $dtvenc = str_pad(substr($row['dtvenc'], 0, 8), 8, "0", STR_PAD_LEFT);
    $dtemissao = str_pad(substr($row['dtemissao'], 0, 8), 8, "0", STR_PAD_LEFT);
    $titulo = str_pad(substr($row['titulo'], 0, 15), 15, "0", STR_PAD_LEFT);
    $documento = str_pad(substr($row['titulo'], 0, 11), 11, "0", STR_PAD_LEFT);
    $documento25 = str_pad(substr($row['titulo'], 0, 25), 25, "0", STR_PAD_LEFT);
    $valor = str_pad(substr(preg_replace('#[^0-9]#', '', $row['valor']), 0, 15), 15, "0", STR_PAD_LEFT);

    $idaluno = $row['idaluno'];
    $queryR = "SELECT idpessoa FROM responsaveis WHERE respfin=1 AND idaluno=" . $idaluno;
    $resultR = mysql_query($queryR);
    $rowR = mysql_fetch_array($resultR, MYSQL_ASSOC);
    if ($rowR['idpessoa'] == "") {
        $queryN = "SELECT nome, cpf, logradouro, bairro, cep FROM pessoas, alunos WHERE alunos.id=$idaluno AND alunos.idpessoa=pessoas.id";
    } else {
        $queryN = "SELECT nome, cpf, logradouro, bairro, cep FROM pessoas, responsaveis WHERE responsaveis.idaluno=$idaluno AND responsaveis.idpessoa=pessoas.id";
    }

    $resultN = mysql_query($queryN);
    $rowN = mysql_fetch_array($resultN, MYSQL_ASSOC);
    $nome = str_pad(substr(limpaAcentos($rowN['nome']), 0, 40), 40, " ", STR_PAD_RIGHT);
  //$cpf=str_pad( substr ($row['cpf'],0,15), 15, " ", STR_PAD_LEFT);
    $cpf = str_pad(substr(preg_replace('#[^0-9]#', '', $rowN['cpf']), 0, 15), 15, "0", STR_PAD_LEFT);

    $enderecoN = str_pad(substr(limpaAcentos($rowN['logradouro']), 0, 40), 40, " ", STR_PAD_RIGHT);
    $bairroN = str_pad(substr(limpaAcentos($rowN['bairro']), 0, 15), 15, " ", STR_PAD_RIGHT);
    $cepN = str_pad(substr(preg_replace('#[^0-9]#', '', $rowN['cep']), 0, 8), 8, " ", STR_PAD_LEFT);
//  $cidade=str_pad( substr ($row['cidade'],0,10), 10, " ", STR_PAD_LEFT);
//  $uf=str_pad( substr ($row['uf'],0,10), 10, " ", STR_PAD_LEFT);

    $nsa = str_pad(substr($nsa, 0, 6), 6, "0", STR_PAD_LEFT);
    $nsa8 = str_pad(substr($nsa, 0, 8), 8, "0", STR_PAD_LEFT);


    $headerArquivo = "10400000         2" . $cnpj . "00000000000000000000" . $agencia . $convenio . "00000000" . $empresa . "CAIXA ECONOMICA FEDERAL                 1" . $hoje . $agora . $nsa . "05000000                    REMESSA-PRODUCAO                               ";
    $headerLote = "10400011R0100030 20" . $cnpj . $convenio . "00000000000000" . $agencia . $convenio . "00000000" . $empresa . "                                                                                " . $nsa8 . $hoje . $hoje . "                                  ";

    $nsl = str_pad(substr($nsl, 0, 5), 5, "0", STR_PAD_LEFT);
    $loteP[] = "10400013" . $nsl . "P 01" . $agencia . $convenio . "0000000000014" . $titulo . "11220" . $documento . "    " . $dtvenc . $valor . "00000002N" . $dtemissao . "300000000000000000000000000000000000000000000000000000000000000000000000000000" . $documento25 . "3001005090000000000 ";
    $nsl++;

    $nsl = str_pad(substr($nsl, 0, 5), 5, "0", STR_PAD_LEFT);
    $loteQ[] = "10400013" . $nsl . "Q 011" . $cpf . $nome . $enderecoN . $bairroN . $cepN . "Rio de Janeiro RJ0000000000000000                                                                       ";
    $nsl++;
    $qtdtitulos++;

    $valorlote = $valorlote + $valor;
}
$nsl = $nsl - 1;

$qtdRegistros = $nsl + 2;
$qtdRegistros = str_pad(substr($qtdRegistros, 0, 6), 6, "0", STR_PAD_LEFT);

$qtdtitulos = str_pad(substr($qtdtitulos, 0, 6), 6, "0", STR_PAD_LEFT);

$nsl = str_pad(substr($nsl, 0, 6), 6, "0", STR_PAD_LEFT);
$valorlote = str_pad(substr($valorlote, 0, 17), 17, "0", STR_PAD_LEFT);
$trailerLote = "10400015         " . $qtdRegistros . $qtdtitulos . $valorlote . "0000000000000000000000000000000000000000000000                                                                                                                                                    ";

$qtdRegistros = $nsl + 4;
$qtdRegistros = str_pad(substr($qtdRegistros, 0, 6), 6, "0", STR_PAD_LEFT);

$trailerArquivo = "10499999         000001" . $qtdRegistros . "                                                                                                                                                                                                                   ";
$nsa++;

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Description: File Transfer');
header("Content-type: text/html");
header("Content-Disposition: attachment; filename=remessa_" . $cliente . "_" . $hoje . $agora . ".rem");
header("Expires: 0");
header("Pragma: public");

echo str_pad(substr($headerArquivo, 0, 240), 240, " ", STR_PAD_RIGHT) . "\r\n";//$headerArquivo;
echo str_pad(substr($headerLote, 0, 240), 240, " ", STR_PAD_RIGHT) . "\r\n";//$headerLote;
foreach ($loteP as $k => $hdLote) {
    echo str_pad(substr($loteP[$k], 0, 240), 240, " ", STR_PAD_RIGHT) . "\r\n";//$loteP[$k];
    echo str_pad(substr($loteQ[$k], 0, 240), 240, " ", STR_PAD_RIGHT) . "\r\n";//$loteQ[$k];
}
echo str_pad(substr($trailerLote, 0, 240), 240, " ", STR_PAD_RIGHT) . "\r\n";//$trailerLote;
echo str_pad(substr($trailerArquivo, 0, 240), 240, " ", STR_PAD_RIGHT) . "\r\n";//$trailerArquivo;
