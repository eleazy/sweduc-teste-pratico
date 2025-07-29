<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class Email extends Model implements \Stringable
{
    public $timestamps = false;
    public $fillable = ['email', 'primario'];

    public function scopePrimario($query)
    {
        return $query->where('primario', 1);
    }

    public function __toString(): string
    {
        return (string) $this->email;
    }
}
