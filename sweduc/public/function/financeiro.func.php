<?php

/*
 * A mascara possui duas casas com uma unidade e 3 casas com duas unidades
 * remove os zeros e para valores maiores que 2 casas que vierem impares
 * adiciona um zero no final
 */
function removeNuloMascaraEventoFinanceiro($codigoeventofinanceiro)
{
    $evento = rtrim($codigoeventofinanceiro, '0');
    $repeticoes = (strlen($evento) > 2 && strlen($evento) % 2 != 0 ? 1 : 0);

    return $evento . str_repeat('0', $repeticoes);
}

/*
 *  Recebe array de códigos de evento financeiro retornando apenas os maiores
 *  níveis hierarquicos removendo casas nulas da mascara
 *
 *  Ex.:
 *  [20000000, 21000000] => [2]
 *  [21010000, 21020000] => [2101, 2102]
 *  [21010300, 21010305] => [210103]
 *
 */
function filtraRaizesEventosFinanceiros($eventos)
{
    $eventosCorrigidos = array_map("removeNuloMascaraEventoFinanceiro", $eventos);

    foreach ($eventosCorrigidos as $roots) {
        foreach ($eventosCorrigidos as $key => $evento) {
            if (empty($evento) || empty($roots)) {
                continue;
            }
            if (str_contains($evento, (string) $roots) && strlen($evento) > strlen($roots)) {
                unset($eventosCorrigidos[$key]);
            }
        }
    }

    return $eventosCorrigidos;
}

/**
 * Retorna cabeçalho do evento financeiro determinado pela profundidade
 *
 * 1 - x.0.00.00.00
 * 2 - x.x.00.00.00
 * 3 - x.x.xx.00.00
 * 4 - x.x.xx.xx.00
 * 5 - x.x.xx.xx.xx
 */
function headerEventoFinanceiro($codigoeventofinanceiro, $profundidade = 2)
{
    $tamanho = $profundidade < 3 ? $profundidade : 2 + ($profundidade - 2) * 2;
    $headerEventFin = substr($codigoeventofinanceiro, 0, $tamanho);
    $zerosRestantes = str_repeat("0", 8 - $tamanho);

    $query = "SELECT eventofinanceiro FROM eventosfinanceiros WHERE COALESCE(codigonovo, codigo) LIKE '$headerEventFin$zerosRestantes' LIMIT 1";
    $result = mysql_query($query);
    return mysql_fetch_array($result, MYSQL_ASSOC)['eventofinanceiro'];
}

/**
 * Retorna profundidade do evento financeiro de acordo com seu código
 *
 * return integer Nível de profundidade dá mascara
 */
function nivelEventoFinanceiro($codigoeventofinanceiro)
{
    $profundidade = strlen(removeNuloMascaraEventoFinanceiro($codigoeventofinanceiro));
    $tamanho = $profundidade < 3 ? $profundidade : 2 + ($profundidade - 2) / 2;
    return $tamanho;
}

function imprimeEventosRecursivamente($eventos, $raiz, $callbackInicio, $callbackFim, $nivel = 1)
{
    call_user_func($callbackInicio, $raiz, $nivel);
    foreach ($eventos as $evento) {
        $raiz_limpo = removeNuloMascaraEventoFinanceiro($raiz);
        $evento_limpo = removeNuloMascaraEventoFinanceiro($evento);

        if (isset($ultimo_evento) && $raiz_limpo != $ultimo_evento && strlen($ultimo_evento) != strlen($evento_limpo)) {
            continue;
        }

        if (strlen($raiz_limpo) < strlen($evento_limpo)) {
            imprimeEventosRecursivamente(array_filter($eventos, fn($evt) => str_starts_with($evt, (string) $evento_limpo)), $evento, $callbackInicio, $callbackFim, $nivel + 1);
        }

        $ultimo_evento = $evento_limpo;
    }
    call_user_func($callbackFim, $raiz, $nivel);
}
