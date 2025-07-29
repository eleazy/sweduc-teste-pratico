<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Framework\Http\BaseController;
use App\Model\Financeiro\EventoFinanceiro;

class EventoFinanceiroController extends BaseController
{
    public function index()
    {
        return EventoFinanceiro::all();
    }
}
