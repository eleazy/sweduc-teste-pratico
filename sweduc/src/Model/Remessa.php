<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use App\Model\Financeiro\Titulo;
use Carbon\CarbonImmutable;

class Remessa
{
    protected Titulo $titulo;
    protected $statusDescontoComercial;
    protected array $empresa;
    protected $dataVencimento;
    protected $descontoComercial;
    protected $valorBoletoSTarifaDesc;

    private const DATA_VAZIA = '000000';

    public function __construct($id, $valorBoletoSTarifaDesc, $rowempresa, $dataVencimento)
    {
        $this->titulo = Titulo::find($id);
        $this->statusDescontoComercial = $this->statusDescComercial($id);
        $this->descontoComercial = $this->descComercial($id, $this->titulo->idaluno);
        $this->valorBoletoSTarifaDesc = $valorBoletoSTarifaDesc;
        $this->empresa = $rowempresa;
        $this->dataVencimento = $dataVencimento;
    }

    public function descontoComercialValor1(): string
    {
        if ($this->statusDescontoComercial === 0) {
            return self::completarZero('', 13);
        }

        $fonte = $this->statusDescontoComercial === 2 ? $this->descontoComercial : $this->empresa;

        if ($fonte['remessa_desc1_valor'] > 0) {
            $valor = $fonte['remessa_desc1_valor'];
        } else {
            $valor = ($fonte['remessa_desc1_desc'] * $this->valorBoletoSTarifaDesc) / 100;
        }

        $valor = !empty($valor) ? str_replace('.', '', number_format((float) $valor, 2, '.', '')) : '';
        return self::completarZero($valor, 13);
    }

    public function descontoComercialData1(): string
    {
        if ($this->statusDescontoComercial === 0) {
            return self::DATA_VAZIA;
        }

        $fonte = $this->statusDescontoComercial === 2 ? $this->descontoComercial : $this->empresa;

        $dia = $fonte['remessa_desc1_dia'];
        $mesAtual = $fonte['remessa_desc1_mesatual'] == '1' || $fonte['remessa_desc1_mesatual'] == '4';
        $diaUtil = $fonte['remessa_desc1_mesatual'] == '3' || $fonte['remessa_desc1_mesatual'] == '4';

        if ($fonte['remessa_desc1_valor'] > 0) {
            $valor = $fonte['remessa_desc1_valor'];
        } else {
            $valor = ($fonte['remessa_desc1_desc'] * $this->valorBoletoSTarifaDesc) / 100;
        }

        $valor = !empty($valor) ? str_replace('.', '', number_format((float) $valor, 2, '.', '')) : '';
        return $this->dataDesconto($valor, $dia, $mesAtual, $diaUtil);
    }

    public function descontoComercialValor2(): string
    {
        if ($this->statusDescontoComercial === 0) {
            return self::completarZero('', 13);
        }

        $fonte = $this->statusDescontoComercial === 2 ? $this->descontoComercial : $this->empresa;

        if ($fonte['remessa_desc2_valor'] > 0) {
            $valor = $fonte['remessa_desc2_valor'];
        } else {
            $valor = ($fonte['remessa_desc2_desc'] * $this->valorBoletoSTarifaDesc) / 100;
        }

        $valor = !empty($valor) ? str_replace('.', '', number_format((float) $valor, 2, '.', '')) : '';
        return self::completarZero($valor, 13);
    }

    public function descontoComercialData2(): string
    {
        if ($this->statusDescontoComercial === 0) {
            return self::DATA_VAZIA;
        }

        $fonte = $this->statusDescontoComercial === 2 ? $this->descontoComercial : $this->empresa;

        $dia = $fonte['remessa_desc2_dia'];
        $mesAtual = $fonte['remessa_desc2_mesatual'] == '1' || $fonte['remessa_desc2_mesatual'] == '4';
        $diaUtil = $fonte['remessa_desc2_mesatual'] == '3' || $fonte['remessa_desc2_mesatual'] == '4';

        if ($fonte['remessa_desc2_valor'] > 0) {
            $valor = $fonte['remessa_desc2_valor'];
        } else {
            $valor = ($fonte['remessa_desc2_desc'] * $this->valorBoletoSTarifaDesc) / 100;
        }

        $valor = !empty($valor) ? str_replace('.', '', number_format((float) $valor, 2, '.', '')) : '';
        return $this->dataDesconto($valor, $dia, $mesAtual, $diaUtil);
    }

    public function descontoComercialValor3(): string
    {
        if ($this->statusDescontoComercial === 0) {
            return self::completarZero('', 13);
        }

        $fonte = $this->statusDescontoComercial === 2 ? $this->descontoComercial : $this->empresa;

        if ($fonte['remessa_desc3_valor'] > 0) {
            $valor = $fonte['remessa_desc3_valor'];
        } else {
            $valor = ($fonte['remessa_desc3_desc'] * $this->valorBoletoSTarifaDesc) / 100;
        }

        $valor = !empty($valor) ? str_replace('.', '', number_format((float) $valor, 2, '.', '')) : '';
        return self::completarZero($valor, 13);
    }

    public function descontoComercialData3(): string
    {
        if ($this->statusDescontoComercial === 0) {
            return self::DATA_VAZIA;
        }

        $fonte = $this->statusDescontoComercial === 2 ? $this->descontoComercial : $this->empresa;

        $dia = $fonte['remessa_desc3_dia'];
        $mesAtual = $fonte['remessa_desc3_mesatual'] == '1' || $fonte['remessa_desc3_mesatual'] == '4';
        $diaUtil = $fonte['remessa_desc3_mesatual'] == '3' || $fonte['remessa_desc3_mesatual'] == '4';

        if ($fonte['remessa_desc3_valor'] > 0) {
            $valor = $fonte['remessa_desc3_valor'];
        } else {
            $valor = ($fonte['remessa_desc3_desc'] * $this->valorBoletoSTarifaDesc) / 100;
        }

        $valor = empty($valor) ? str_replace('.', '', number_format((float) $valor, 2, '.', '')) : '';
        return $this->dataDesconto($valor, $dia, $mesAtual, $diaUtil);
    }

    private function dataDesconto($valor, $dias, bool $mesAtual, bool $somenteDiasUteis = false, $anoExtenso = false)
    {
        if (!$dias && !$valor) {
            return self::DATA_VAZIA;
        }

        $vencimento = CarbonImmutable::parse($this->dataVencimento);
        $vencimento->settings([
            'monthOverflow' => false,
        ]);
        $mesDesconto = $mesAtual ? $vencimento->setDay(0) : $vencimento->subMonth()->setDay(0);

        if ($somenteDiasUteis) {
            $dataDoDesconto = $this->addDiaUtil($mesDesconto, $dias);
        } else {
            $dataDoDesconto = $mesDesconto->addDays($dias);
        }

        $dataFormatada = $dataDoDesconto->format($anoExtenso ? 'dmY' : 'dmy');
        return $dataFormatada;
    }

    private function statusDescComercial($idFichaFinanceira)
    {
        $qdc = "SELECT
                    IF(aff.id_desconto_comercial IS NOT NULL OR desconto_comercial > 0, 1, 0) as dc,
                    SUM(afi.descontoboleto) as recebeDesconto
                FROM alunos
                JOIN alunos_fichafinanceira aff ON alunos.id = idaluno
                JOIN alunos_fichaitens afi ON aff.id = afi.idalunos_fichafinanceira
                WHERE aff.id = $idFichaFinanceira LIMIT 1";
        $this->descontoComercial = mysql_query($qdc);
        $r = mysql_fetch_array($this->descontoComercial, MYSQL_ASSOC);

        $temDesconto = $r['recebeDesconto'] > 0;
        $descComercialAtivado = $r['dc'] > 0;
        return $temDesconto * ($temDesconto + $descComercialAtivado);
    }

    private function descComercial($idFichaFinanceira, $idAluno)
    {
        $qdc = "SELECT  financeiro_descontocomercial.id,
                        remessa_desc1_dia,
                        remessa_desc1_mesatual,
                        remessa_desc1_desc,
                        remessa_desc1_valor,
                        remessa_desc2_dia,
                        remessa_desc2_mesatual,
                        remessa_desc2_desc,
                        remessa_desc2_valor,
                        remessa_desc3_dia,
                        remessa_desc3_mesatual,
                        remessa_desc3_desc,
                        remessa_desc3_valor
                    FROM
                        financeiro_descontocomercial
                    LEFT JOIN
                        alunos ON financeiro_descontocomercial.id=alunos.desconto_comercial_msg AND alunos.id = $idAluno
                    LEFT JOIN
                        alunos_fichafinanceira ON financeiro_descontocomercial.id=alunos_fichafinanceira.id_desconto_comercial AND alunos_fichafinanceira.id = $idFichaFinanceira
                    WHERE alunos.id IS NOT NULL OR alunos_fichafinanceira.id IS NOT NULL
                    ORDER BY alunos_fichafinanceira.id IS NULL
                    LIMIT 1";
        $this->descontoComercial = mysql_query($qdc);
        $r = mysql_fetch_array($this->descontoComercial, MYSQL_ASSOC);

        return $r;
    }

    public static function completarZero(string $txt, $qunat)
    {
        $t = strlen($txt);
        if ($t < $qunat) {
            for ($i = $t; $i < $qunat; $i++) {
                $txt = 0 . $txt;
            }
        } else {
            $txt = substr($txt, 0, $qunat);
        }
        return $txt;
    }

    private function addDiaUtil(CarbonImmutable $mesDesconto, string $dias)
    {
        $dias = (int) $dias;
        $feriadosDoDB = DB::select("SELECT data_do_feriado FROM feriados");
        $feriados = array_map(function ($feriado) {
            return $feriado->data_do_feriado;
        }, $feriadosDoDB);

        while ($dias > 0) {
            $mesDesconto = $mesDesconto->addDay();
            $dayOfWeek = $mesDesconto->dayOfWeek;
            $isWeekend = ($dayOfWeek == Carbon::SATURDAY || $dayOfWeek == Carbon::SUNDAY);
            $isHoliday = in_array($mesDesconto->format('Y-m-d'), $feriados);

            if (!$isWeekend && !$isHoliday) {
                $dias--;
            }
        }

        return $mesDesconto;
    }
}
