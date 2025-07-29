<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Pix extends Model
{
    public $timestamps = true;
    protected $table = 'pix';

    public function titulo()
    {
        return $this->belongsTo(Titulo::class, 'alunos_fichafinanceira_id');
    }
}
