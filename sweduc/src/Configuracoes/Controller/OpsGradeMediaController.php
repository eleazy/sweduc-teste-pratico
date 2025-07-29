<?php

declare(strict_types=1);

namespace App\Configuracoes\Controller;

use App\Framework\Http\BaseController;

class OpsGradeMediaController extends BaseController
{
    public function index()
    {
        return $this->platesView('Config/GradeMedia');
    }
}
