<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class EstadoCivil extends Model implements \Stringable
{
    public $timestamps = false;
    public $table = 'estadocivil';

    public function __toString(): string
    {
        return (string) $this->estadocivil;
    }
}
