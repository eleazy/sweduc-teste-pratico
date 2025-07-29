<?php

declare(strict_types=1);

namespace App\Model\Newsletter;

use App\Framework\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VistoPost extends Model
{
    use SoftDeletes;

    protected $table = 'visto_posts_atualizacao';
    protected $guarded = [];

    public $timestamps = true;
}
