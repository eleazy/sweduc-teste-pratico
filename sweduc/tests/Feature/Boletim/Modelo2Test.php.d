<?php

declare(strict_types=1);

namespace Tests\Feature\Remessa;

use App\Academico\Model\Matricula;
use App\Model\Core\Usuario;

use function PHPUnit\Framework\assertEquals;

test('aluno comum', function () {
    loggedAs(Usuario::first());

    $matricula = Matricula::factory()->create();

    $request = post("/alunos_boletim_modelo2.php", [
        'idaluno' => $matricula->idaluno,
        'nummatricula' => $matricula->nummatricula,
        'idanoletivo' => random_int(1,9),
        'idunidade' => $matricula->idunidade,
    ]);

    assertEquals(200, $request->getStatusCode());
});
