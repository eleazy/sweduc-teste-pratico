<?php

function dataday($day)
{
    $day = match ($day) {
        "Monday" => "Segunda-Feira",
        "Tuesday" => "Terça-Feira",
        "Wednesday" => "Quarta-Feira",
        "Thursday" => "Quinta-Feira",
        "Friday" => "Sexta-Feira",
        "Saturday" => "Sábado",
        "Sunday" => "Domingo",
        default => $day,
    };
    return $day;
}

function datamonth($month)
{
    $month = match ($month) {
        "January" => "Janeiro",
        "February" => "Fevereiro",
        "March" => "Março",
        "April" => "Abril",
        "May" => "Maio",
        "June" => "Junho",
        "July" => "Julho",
        "August" => "Agosto",
        "September" => "Setembro",
        "October" => "Outubro",
        "November" => "Novembro",
        "December" => "Dezembro",
        default => $month,
    };
    return $month;
}

function datamonth2($month)
{
    $month = match ($month) {
        "1" => "Janeiro",
        "2" => "Fevereiro",
        "3" => "Março",
        "4" => "Abril",
        "5" => "Maio",
        "6" => "Junho",
        "7" => "Julho",
        "8" => "Agosto",
        "9" => "Setembro",
        "10" => "Outubro",
        "11" => "Novembro",
        "12" => "Dezembro",
        default => $month,
    };
    return $month;
}

function dateBrtoEn($data)
{
    if ($data != '') {
        $d = explode('/', $data);
        return $d[2] . '-' . $d[1] . '-' . $d[0];
    } else {
        return '0000-00-00';
    }
}

function dateEntoBr($data)
{
    if ($data != '') {
        $d = explode('-', $data);
        return $d[2] . '/' . $d[1] . '/' . $d[0];
    } else {
        return '00/00/0000';
    }
}

function dateEntoBrHora($data)
{
    if ($data != '') {
        $p = explode(' ', $data);
        $d = explode('-', $p[0]);
        return $d[2] . '/' . $d[1] . '/' . $d[0] . ' ' . $p[1];
    } else {
        return '00/00/0000';
    }
}

function dateEntoBrHora2($data)
{
    if ($data != '') {
        $p = explode(' ', $data);
        $d = explode('-', $p[0]);
        return $d[2] . '/' . $d[1] . '/' . $d[0];
    } else {
        return '00/00/0000';
    }
}

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

function brl($num)
{
    return number_format($num, 2, ',', '.');
}

function IntervalDays($CheckIn, $CheckOut)
{
    $CheckInX = explode("-", $CheckIn);
    $CheckOutX =  explode("-", $CheckOut);
    $date1 =  mktime(0, 0, 0, intval($CheckInX[1]), intval($CheckInX[2]), intval($CheckInX[0]));
    $date2 =  mktime(0, 0, 0, intval($CheckOutX[1]), intval($CheckOutX[2]), intval($CheckOutX[0]));
    $interval = ($date2 - $date1) / (3600 * 24);

    // returns numberofdays
    return  $interval ;
}

function textoBanco($texto)
{
    $texto = strtolower($texto);
    $texto = preg_replace('/[áàãâä]/', '%', $texto);
    $texto = preg_replace('/[éèêë]/', '%', $texto);
    $texto = preg_replace('/[íìîï]/', '%', $texto);
    $texto = preg_replace('/[óòõôö]/', '%', $texto);
    $texto = preg_replace('/[úùûü]/', '%', $texto);
    $texto = preg_replace('/[ç]/', '%', $texto);
    $texto = preg_replace('/[ñ]/', '%', $texto);
    $texto = preg_replace('/[[:space:]]/', '%', $texto);
    return $texto;
}

function textoLimpo($texto)
{
    $texto = strtolower($texto);
    $texto = preg_replace('/[áàãâä]/', 'a', $texto);
    $texto = preg_replace('/[éèêë]/', 'e', $texto);
    $texto = preg_replace('/[íìîï]/', 'i', $texto);
    $texto = preg_replace('/[óòõôö]/', 'o', $texto);
    $texto = preg_replace('/[úùûü]/', 'u', $texto);
    $texto = preg_replace('/[ç]/', 'c', $texto);
    $texto = preg_replace('/[ñ]/', 'n', $texto);
    return utf8_encode($texto);
}

function naturalizaDiasDaSemana($dias)
{
    if ($dias == null) {
        return null;
    }

    $dias_da_semana = [
        'domingo' => 'Domingo',
        'segunda' => 'Segunda',
        'terca' => 'Terça',
        'quarta' => 'Quarta',
        'quinta' => 'Quinta',
        'sexta' => 'Sexta',
        'sabado' => 'Sábado'
    ];

    $dias_array = explode(',', $dias);
    if (count($dias_array) == 7) {
        return 'Todos os dias';
    }

    if (count($dias_array) > 2 && str_contains(implode(',', array_keys($dias_da_semana)), (string) $dias)) {
        return $dias_da_semana[$dias_array[0]] . ' a ' . $dias_da_semana[$dias_array[count($dias_array) - 1]];
    }

    $texto_final = '';
    foreach ($dias_array as $key => $value) {
        $separator = ', ';
        if ($key == 0) {
            $separator = '';
        }
        if ($key == count($dias_array) - 1) {
            $separator = ' e ';
        }

        $texto_final .= $separator . $dias_da_semana[$value];
    }

    return $texto_final;
}


function valorPorExtenso($valor = 0)
{
    $rt = null;
    $singular = ["centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão"];
    $plural = ["centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões"];
    $c = ["", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos"];
    $d = ["", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa"];
    $d10 = ["dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezesete", "dezoito", "dezenove"];
    $u = ["", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove"];
    $z = 0;
    $valor = number_format($valor, 2, ".", ".");
    $inteiro = explode(".", $valor);
    for ($i = 0; $i < count($inteiro); $i++) {
        for ($ii = strlen($inteiro[$i]); $ii < 3; $ii++) {
            $inteiro[$i] = "0" . $inteiro[$i];
        }
    }
    // $fim identifica onde que deve se dar junção de centenas por "e" ou por ","
    $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
    for ($i = 0; $i < count($inteiro); $i++) {
        $valor = $inteiro[$i];
        $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
        $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
        $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

        $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
        $t = count($inteiro) - 1 - $i;
        $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
        if ($valor == "000") {
            $z++;
        } elseif ($z > 0) {
            $z--;
        }
        if (($t == 1) && ($z > 0) && ($inteiro[0] > 0)) {
            $r .= (($z > 1) ? " de " : "") . $plural[$t];
        }
        if ($r) {
            $rt = '';
            $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
        }
    }
    return($rt ?: "zero");
}

function slugify($text)
{
  // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // remove unwanted characters
    $text = preg_replace('~[^\-\w]+~', '', $text);

  // trim
    $text = trim($text, '-');

  // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);

  // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}
