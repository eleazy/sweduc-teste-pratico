<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

class ItemTitulo extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function titulo()
    {
        return $this->belongsTo(Titulo::class, 'idalunos_fichafinanceira');
    }

    public function evento()
    {
        return $this->belongsTo(EventoFinanceiro::class, 'codigo', 'codigo');
    }

    protected $table = 'alunos_fichaitens';
}
