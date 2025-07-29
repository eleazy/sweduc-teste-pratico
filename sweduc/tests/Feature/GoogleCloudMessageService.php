<?php

use App\Academico\APIController\Rematricula\GerarRematriculaController;
use App\Model\Core\Usuario;
use App\Notificacoes\Service\GoogleCloudMessageService;

use function App\Framework\resolve;

test('testa envio de mensagens', function () {
    /** @var GoogleCloudMessageService */
    $gcmService = resolve(GoogleCloudMessageService::class);
    $gcmService->send();
});
