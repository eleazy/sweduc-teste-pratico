<?php

function balancoAltera($quant = '', $valorCompra = '', $valorVenda = '', $unidade, $id)
{

    $status = null;
    $set = '';
    if ($valorCompra != '') {
        $set .= 'valorcompra = "' . $valorCompra . '", ';
        $valorCompra = str_replace(",", ".", str_replace(".", "", $valorCompra));
    }

    if ($valorVenda != '') {
        $set .= 'valorvenda = "' . $valorVenda . '", ';
        $valorVenda = str_replace(",", ".", str_replace(".", "", $valorVenda));
    }


    $set = substr($set, 0, -2);


    $query = 'select * from  estoque_movimentacao 
                    WHERE
                        movimentacao = 1 AND idestoque = ' . $id . '
                            AND estoque_unidade = ' . $unidade;


    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    if (isset($row['id'])) {
        if ($set != '') {
            $query = 'UPDATE estoque 
                    SET 
                    ' . $set . '
                    WHERE
                        id = ' . $id;

            if ($result = mysql_query($query)) {
                $status = 1;
            } else {
                $status = -1;
            }
        }

        if ($quant != '') {
            $query = 'UPDATE estoque_movimentacao 
                    SET 
                        quantidade = ' . $quant . '
                    WHERE
                        movimentacao = 1 AND idestoque = ' . $id . '
                            AND estoque_unidade = ' . $unidade;
            if ($result = mysql_query($query)) {
                $status = 1;
            } else {
                $status = -1;
            }
        }
    } else {
        $query = "INSERT INTO estoque_movimentacao
                                    (idestoque,
                                    idunidadedestino,
                                    idterceiro,
                                    quantidade,
                                    dataoperacao,
                                    idfuncionario,
                                    datamovimentacao,
                                    valormovimentacao,
                                    tempoentrega,
                                    motivo,
                                    estoque_unidade,
                                    movimentacao)
                                    VALUES
                                    (" . $id . " ,
                                    0 ,
                                    0 ,
                                    " . $quant . ",
                                    now() ,
                                    0 ,
                                    now() ,
                                    0 ,
                                    0 ,
                                    '' ,
                                    " . $unidade . " ,
                                    1)";
        if ($result = mysql_query($query)) {
            $status = 1;
        } else {
            $status = -1;
        }
    }

    return $status;
}

function estoqueQuantidadeVendaUnidade($idEstoque, $idUnidade, $dataIni, $dataFim)
{

    $rowVendas = null;
    $sql = 'SELECT 
                SUM(quantidade) quantidade
            FROM
                estoque_movimentacao em
            WHERE
                idestoque = ' . $idEstoque . ' AND idunidadedestino = -1
                   AND estoque_unidade = ' . $idUnidade . '
                    AND dataoperacao BETWEEN "' . $dataIni . '" AND "' . $dataFim . '"';


    $resul = mysql_query($sql);
    while ($row = mysql_fetch_array($resul, MYSQL_ASSOC)) {
        $rowVendas = $row['quantidade'];
    }

    return $rowVendas;
}

function estoqueQuantidadeVendaAluno($idEstoque, $idUnidade, $dataIni, $dataFim)
{

    $rowVendas = [];
    $sql = 'SELECT
                p.nome, em.quantidade
            FROM
                estoque_movimentacao em
                INNER JOIN
                alunos a ON em.idaluno = a.id
                INNER JOIN
                pessoas p ON a.idpessoa = p.id
            WHERE
                idestoque = ' . $idEstoque . ' AND idunidadedestino = -1
                    AND estoque_unidade = ' . $idUnidade . '
                    AND dataoperacao BETWEEN "' . $dataIni . '" AND "' . $dataFim . '"';

    $resul = mysql_query($sql);
    while ($row = mysql_fetch_array($resul, MYSQL_ASSOC)) {
        $rowVendas[] = $row;
    }

    return $rowVendas;
}
