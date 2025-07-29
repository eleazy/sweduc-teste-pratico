<?php

declare(strict_types=1);

namespace App\Opencart\Listener;

use App\Event\MovimentacaoDeTitulo;
use App\Model\Financeiro\Titulo;
use App\Opencart\Model\OpencartConfig;
use App\Opencart\Model\PedidoOpencart;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;

class OpencartMovimentacaoTituloListener
{
    public function __construct(
        private LoggerInterface $logger,
        private RequestFactoryInterface $requestFactory,
        private ClientInterface $http,
    ) {
        //
    }

    /**
     * Handler de evento de movimentação de título
     */
    public function comunicarLoja(MovimentacaoDeTitulo $movimentacao): void
    {
        $titulo = $movimentacao->titulo;
        $metadados = $this->metadadosDoOpencart($titulo);

        if (empty($metadados)) {
            return;
        }

        $pedido = PedidoOpencart::where('titulo_id', $titulo->id)->first();
        $config = OpencartConfig::find($pedido->configuracao_id);

        $url = rtrim($config->url, '/') . '/index.php?route=extension/payment/sweduc_checkout/callback';

        $this->logger->debug("Callback de movimentação de título enviando requisição para url: $url");

        $request = $this->requestFactory->createRequest('POST', $url);

        $request->getBody()->write(json_encode([
            'order_id' => $metadados->order_id,
            'status_updated' => $titulo->situacao,
        ], JSON_THROW_ON_ERROR));

        $response = $this->http->sendRequest($request);

        $this->logger->debug('Resposta da requisição do callback: ' . $response->getBody());
    }

    private function metadadosDoOpencart(Titulo $titulo): ?object
    {
        if (empty($titulo->metadados)) {
            return null;
        }

        $metadados = json_decode($titulo->metadados, null, 512, JSON_THROW_ON_ERROR);

        if (empty($metadados->origin) || $metadados->origin !== 'opencart_sweduc_checkout') {
            return null;
        }

        return $metadados;
    }
}
