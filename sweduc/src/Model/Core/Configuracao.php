<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    public static $config;
    public $timestamps = false;

    public static function chave($key)
    {
        self::$config ??= Configuracao::first();
        return self::$config->$key ?? null;
    }

    /**
     * Retorna configurações populadas globalmente pelo /dao/conectar.php em formato array
     */
    public static function completa(): array
    {
        $configuracao = self::$config ??= Configuracao::first();

        $datainicialocorrenciasalunos = $configuracao->datainicialocorrenciasalunos;
        $dtinicialocorrenciasalunos = explode("-", $datainicialocorrenciasalunos);
        $dtinicialocorrenciasalunos = $dtinicialocorrenciasalunos[2] . "/" . $dtinicialocorrenciasalunos[1] . "/" . $dtinicialocorrenciasalunos[0];

        return [
            'publica' => $configuracao->publica,
            'tipodefaltas' => $configuracao->tipodefaltas,
            'atualizadoem' => $configuracao->atualizadoem,
            'perdebolsa' => $configuracao->perdebolsa,
            'msgrecibo' => $configuracao->msgrecibo,
            'relfin' => $configuracao->relfin,
            'notasdecimais' => $configuracao->notasdecimais,
            'dtinicialocorrenciasalunos' => $dtinicialocorrenciasalunos,
            'bloqueio' => $configuracao->bloqueio ?? 0,
            'usa_matricula_online' => $configuracao->usa_matricula_online ?? 0,
            'vencimento_pula_feriados' => $configuracao->vencimento_pula_feriados ?? 0,
        ];
    }

    protected $table = 'configuracoes';
}
