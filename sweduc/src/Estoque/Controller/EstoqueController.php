<?php

declare(strict_types=1);

namespace App\Estoque\Controller;

use App\Estoque\Estoque;
use App\Framework\Http\BaseController;
use Psr\Http\Message\ServerRequestInterface;

class EstoqueController extends BaseController
{
    public function autocomplete(ServerRequestInterface $request)
    {
        $input = $request->getQueryParams();
        $search = $input['term'];

        return $this->jsonResponse(
            Estoque::where('descricao', 'like', "%$search%")
                ->take(50)
                ->get(['descricao'])
                ->pluck('descricao')
        );
    }
}
