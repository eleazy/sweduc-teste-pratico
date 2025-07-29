<?php

declare(strict_types=1);

namespace App\Model\Config;

use App\Framework\Model;

class DocumentoRematricula extends Model
{
    protected $casts = [ 'obrigatoriedade' => 'boolean' ];
    protected $table = 'documentos_rematricula';
    protected $fillable = ['documento', 'obrigatoriedade'];
}
