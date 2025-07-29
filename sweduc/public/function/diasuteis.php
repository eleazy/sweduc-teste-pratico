<?php

//CALCULANDO DIAS NORMAIS
/*Abaixo vamos calcular a diferença entre duas datas. Fazemos uma reversão da maior sobre a menor
para não termos um resultado negativo. */

use Carbon\CarbonInterface;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;

function CalculaDias($xDataInicial, $xDataFinal)
{
    $time1 = dataToTimestamp($xDataInicial);
    $time2 = dataToTimestamp($xDataFinal);

    $tMaior = $time1 > $time2 ? $time1 : $time2;
    $tMenor = $time1 < $time2 ? $time1 : $time2;

    $diff = $tMaior - $tMenor;
    $numDias = $diff / 86400; //86400 é o número de segundos que 1 dia possui
    return $numDias;
}

//LISTA DE FERIADOS NO ANO
/*Abaixo criamos um array para registrar todos os feriados existentes durante o ano.*/
function Feriados($ano): array
{
    $dia = 86400;
    $datas = [];
    $datas['pascoa'] = easter_date($ano);
    $datas['sexta_santa'] = $datas['pascoa'] - (2 * $dia);
    $datas['carnaval'] = $datas['pascoa'] - (47 * $dia);
    $datas['corpus_cristi'] = $datas['pascoa'] + (60 * $dia);

    return array_map(fn($feriado) => $feriado . "/" . $ano, [
        '01/01',
        '20/01', // São Sebastião
        date('d/m', $datas['carnaval']),
        date('d/m', $datas['sexta_santa']),
        date('d/m', $datas['pascoa']),
        '21/04', // Tiradentes
        '01/05', // trabalho
        date('d/m', $datas['corpus_cristi']),
        '07/09', // independência
        '12/10', // n. s. Aparecida
        '02/11', // finados
        '15/11', // republica
        '25/12', // Natal
    ]);
}

//FORMATA COMO TIMESTAMP
/*Esta função é bem simples, e foi criada somente para nos ajudar a formatar a data já em formato  TimeStamp facilitando nossa soma de dias para uma data qualquer.*/
function dataToTimestamp($data)
{
    $ano = substr($data, 6, 4);
    $mes = substr($data, 3, 2);
    $dia = substr($data, 0, 2);
    return mktime(0, 0, 0, $mes, $dia, $ano);
}

//SOMA 01 DIA
function Soma1dia(CarbonInterface $data)
{
    $ano = substr($data, 6, 4);
    $mes = substr($data, 3, 2);
    $dia = substr($data, 0, 2);
    return   date("d/m/Y", mktime(0, 0, 0, $mes, $dia + 1, $ano));
}

//CALCULA DIAS UTEIS
/*É nesta função que faremos o calculo. Abaixo podemos ver que faremos o cálculo normal de dias ($calculoDias), após este cálculo, faremos a comparação de dia a dia, verificando se este dia é um sábado, domingo ou feriado e em qualquer destas condições iremos incrementar 1*/

function DiasUteis($yDataInicial, $yDataFinal)
{
    $calculoDias = CalculaDias($yDataInicial, $yDataFinal); //número de dias entre a data inicial e a final
    $diasNaoUteis = 0;

    while ($yDataInicial != $yDataFinal) {
        $diaSemana = date("w", dataToTimestamp($yDataInicial));
        if ($diaSemana == 0 || $diaSemana == 6) {
            //se SABADO OU DOMINGO, SOMA 01
            $diasNaoUteis++;
        } elseif (in_array($yDataInicial, Feriados(date("Y")))) {
            //senão vemos se este dia é FERIADO
            $diasNaoUteis++;
        }
        $yDataInicial = Soma1dia($yDataInicial); //dia + 1
    }

    return $calculoDias - $diasNaoUteis;
}

/* verifica se a data não cai em um final de semana ou feriado. Se cair, adiciona dias extras */
function ProximoDiaUtil(string $date): string
{
    $date = Carbon::createFromFormat('Y-m-d', $date);

    $feriadosDoDB = DB::select("SELECT data_do_feriado FROM feriados");
    $feriados = array_map(function ($feriado) {
        return $feriado->data_do_feriado;
    }, $feriadosDoDB);

    do {
        $dayOfWeek = $date->dayOfWeek;
        $isWeekend = ($dayOfWeek == Carbon::SATURDAY || $dayOfWeek == Carbon::SUNDAY);
        $isHoliday = in_array($date->format('Y-m-d'), $feriados);

        if (!$isWeekend && !$isHoliday) {
            return $date->format('d/m/Y');
        }

        $date->addDay();
    } while ($isWeekend || $isHoliday);

    return $date->format('d/m/Y');
}
