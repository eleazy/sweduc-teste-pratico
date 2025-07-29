<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class Parentesco extends Model implements \Stringable
{
    public $timestamps = false;
    public $table = 'parentescos';

    public function __toString(): string
    {
        return (string) $this->parentesco;
    }
}
