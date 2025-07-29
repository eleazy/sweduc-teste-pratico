<?php

declare(strict_types=1);

namespace App\Model\Sistema;

use App\Model\Core\Usuario;
use Illuminate\Database\Eloquent\Model;

class EnvioDeEmail extends Model
{
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    protected $table = 'sistema_envio_de_emails';
}
