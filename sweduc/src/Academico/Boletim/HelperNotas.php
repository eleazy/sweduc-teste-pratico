<?php

declare(strict_types=1);

namespace App\Academico\Boletim;

function contarNaoNulos()
{
    $args = func_get_args();
    $args = is_array($args[0]) ? $args[0] : $args;
    $count = 0;
    foreach ($args as $i) {
        if ($i > 0) {
            $count++;
        }
    }

    return $count;
}

function mediaNaoNulos(...$args)
{
    return (array_sum($args) / contarNaoNulos($args));
}

function arredonda05($num)
{
    $inteiro = floor($num);
    $dec = number_format($num - $inteiro, 1);

    if ($dec < (0.3)) {
        return $inteiro;
    }
    if ($dec >= (0.3) && $dec < (0.8)) {
        return $inteiro += (0.5);
    }
    if ($dec >= (0.8)) {
        return $inteiro += 1;
    }

    return $inteiro;
}
