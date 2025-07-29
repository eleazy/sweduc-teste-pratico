<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EventoFinanceiro extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('codigo', 'asc');
        });

        static::addGlobalScope('habilitado', function (Builder $builder) {
            $builder->where('habilitado', 1);
        });
    }

    public function scopeFechamento($query)
    {
        $query->where('motivo', 'like', 'Fechamento%');
    }

    public function scopeReceita($query)
    {
        $query->where('codigo', 'like', '1%');
    }

    public function getTituloAttribute()
    {
        return $this->eventofinanceiro;
    }


    /**
     * Retorna atributo nível do evento financeiro
     *
     * As máscaras são compostas por dois níveis de uma casa decimal
     * e três de duas casas decimais
     *
     * Tendo o seguinte código de evento
     * 1.1.01.10.00
     * O nível será quatro pois é onde está a ultimo valor maior
     * que zero
     *
     * @return void
     */
    public function getNivelAttribute()
    {
        $primeiroNivelPos = 0;
        $segundoNivelPos = 1;

        // Duas casas decimais
        $terceiroNivelPos = 2;
        $quartoNivelPos = 4;
        $quintoNivelPos = 6;

        if (substr($this->codigo, $quintoNivelPos, 2) > 0) {
            return 5;
        }

        if (substr($this->codigo, $quartoNivelPos, 2) > 0) {
            return 4;
        }

        if (substr($this->codigo, $terceiroNivelPos, 2) > 0) {
            return 3;
        }

        if (substr($this->codigo, $segundoNivelPos, 1) > 0) {
            return 2;
        }

        if (substr($this->codigo, $primeiroNivelPos, 1) > 0) {
            return 1;
        }

        return 0;
    }

    public $timestamps = false;
    protected $table = 'eventosfinanceiros';
}
