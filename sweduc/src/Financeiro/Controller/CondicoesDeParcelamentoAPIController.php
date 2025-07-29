<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Controller\Controller;
use App\Model\Financeiro\CondicaoDeParcelamento;
use App\Model\Financeiro\EventoFinanceiro;
use NumberFormatter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller de condições de parcelamento do pagamento online
 */
class CondicoesDeParcelamentoAPIController extends Controller
{
    public function __construct(ResponseFactoryInterface $responseFactory, protected NumberFormatter $numfmt)
    {
        parent::__construct($responseFactory);
    }

    /**
     * Retorna eventos com as condições de parcelamento disponíveis
     *
     * @return void
     */
    public function listEventos(ServerRequestInterface $request)
    {
        $input = $this->queryInput($request);
        $contaId = $input->contaId;

        if (!$contaId) {
            return $this->errorJsonResponse("Parametro contaId não identificado na requisição", 422);
        }

        $valor = $input->valor ?? 0;

        $condicoes = CondicaoDeParcelamento::where('conta_id', $contaId)->get();
        $eventos = EventoFinanceiro::receita()->get();

        $eventosEParcelamento = $eventos->map(function ($evento) use ($condicoes, $contaId, $valor) {
            $parcelamentosPossiveis = $condicoes->filter(function ($condicao) use ($contaId, $evento, $valor) {
                // Conta difere
                if ($condicao->conta_id != $contaId) {
                    return false;
                }

                // Evento difere
                if ($condicao->evento_id && $condicao->evento_id != $evento->id) {
                    return false;
                }

                // Limite maior que o valor
                if ($condicao->limite_valor && $condicao->limite_valor <= $valor) {
                    return false;
                }

                return true;
            });

            $maiorParcelamento = $parcelamentosPossiveis->max('limite_parcelamento');
            $evento->parcelamentoMaximo = $maiorParcelamento ?? 1;
            return $evento;
        });

        return $this->jsonResponse($eventosEParcelamento);
    }

    /**
     * Criação de condição de parcelamento
     *
     * @return void
     */
    public function store(ServerRequestInterface $request)
    {
        $input = $this->jsonInput($request);

        $condicaoExiste = CondicaoDeParcelamento::where(function ($query) use ($input) {
            $query->where('conta_id', $input->contaId);
            $query->where('limite_parcelamento', '>=', $input->parcelamento);

            $query->where(function ($query) use ($input) {
                $query->whereNull('evento_id');

                if ($input->eventoId) {
                    $query->orWhere('evento_id', $input->eventoId);
                }
            });

            $query->where(function ($query) use ($input) {
                $query->whereNull('limite_valor');

                if ($this->numfmt->parse($input->valor)) {
                    $query->orWhere('limite_valor', '<=', $this->numfmt->parse($input->valor));
                }
            });
        })->first();

        if ($condicaoExiste || $input->parcelamento == 1) {
            return $this->errorJsonResponse("Já existente uma condição que abrange esses limites", 422);
        }

        $condicao = new CondicaoDeParcelamento();
        $condicao->conta_id = $input->contaId;
        $condicao->evento_id = $input->eventoId;
        $condicao->limite_valor = $this->numfmt->parse($input->valor) ?: null;
        $condicao->limite_parcelamento = $input->parcelamento;
        $condicao->saveOrFail();

        return $this->jsonResponse($condicao->load('evento:id,eventofinanceiro'));
    }

     /**
     * Remove condição de parcelamento
     *
     * @param [type] $args
     * @return void
     */
    public function delete(ServerRequestInterface $request, $args)
    {
        $id = $args['id'];
        $condicao = CondicaoDeParcelamento::findOrFail($id);
        $condicao->delete();

        return $this->jsonResponse();
    }
}
