<?php

declare(strict_types=1);

namespace App\Model\Newsletter;

use App\Framework\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $table = 'atualizacoes_posts';
    protected $connection = 'shared';

    public $timestamps = true;
}
