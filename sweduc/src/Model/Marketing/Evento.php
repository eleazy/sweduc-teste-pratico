<?php

declare(strict_types=1);

namespace App\Model\Marketing;

use App\Model\Core\Funcionario;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    public function prospeccao()
    {
        return $this->belongsTo(Prospeccao::class, 'id_prospeccao_ficha');
    }

    public function agendadoPor()
    {
        return $this->belongsTo(Funcionario::class, 'atendido_por_id_funcionario');
    }

    public function atendidoPor()
    {
        return $this->belongsTo(Funcionario::class, 'agendado_por_id_funcionario');
    }

    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'id_situacao');
    }

    public function tipoDeRetorno()
    {
        return $this->belongsTo(TipoDeRetorno::class, 'id_mkt_tiporetorno');
    }

    public $timestamps = false;
    protected $table = 'prospeccao_crm';
}
