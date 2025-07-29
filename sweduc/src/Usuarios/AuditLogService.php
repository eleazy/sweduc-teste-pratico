<?php

declare(strict_types=1);

namespace App\Usuarios;

use App\Model\Core\AuditLog;

class AuditLogService
{
    /**
     * Adiciona entrada de log no sistema
     *
     * @param string $path identificação da função ex(__METHOD__)
     * @return void
     */
    public static function log(string $descricao, ?array $parametros = null, string $path = null)
    {
        $log = new AuditLog();
        $log->idfuncionario = $_SESSION['id_funcionario'];
        $log->datahora = date("Y-m-d H:i:s");
        $log->descricao = $descricao;
        $log->status = 1;
        $log->acao = '';

        if (!empty($parametros)) {
            $log->parametroscsv = json_encode($parametros, JSON_THROW_ON_ERROR);
        } else {
            $log->parametroscsv = '';
        }

        if ($path) {
            $log->acao = basename($path);
        }

        $log->save();
    }
}
