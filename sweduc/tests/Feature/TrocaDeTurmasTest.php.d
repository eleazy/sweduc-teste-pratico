<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Academico\Model\Aluno;
use App\Academico\Model\Matricula;
use App\Academico\Model\Turma;
use App\Model\Core\Usuario;
use GuzzleHttp\Client;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotSame;

final class TrocaDeTurmasTest extends TestCase
{
    private Client $http;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->http = new Client([
            'base_uri' => 'http://localhost',
            'cookies' => true,
        ]);
    }

    public function testMudaAlunoDeTurmaComSucesso(): void
    {
        $this->login();

        // Criando turma antiga e nova
        $turmaAntiga = Turma::create([
            'idserie' => 1,
        ]);
        $turmaNova = Turma::create([
            'idserie' => 1,
        ]);

        // Criando objeto de teste
        $aluno = Aluno::create([
            'numeroaluno' => rand(),
        ]);
        $matricula = Matricula::create([
            'nummatricula' => rand(),
        ]);

        // Adicionando matrícula a turma antiga
        $matricula->turma()->associate($turmaAntiga);
        $matricula->save();

        // Associando matrícula ao aluno
        $aluno->matriculas()->save($matricula);

        $trocarTurmasRequest = $this->http->post('/dao/alunos.php', [
            'form_params' => [
                'action' => 'trocaTurma',
                'idaluno' => $aluno->id,
                'idunidade' => $matricula->idunidade,
                'turmamatriculadoantiga' => $turmaAntiga->id,
                'nummatricula' => $matricula->nummatricula,
                'turmamatriculado' => $turmaNova->id,
                'idunidadematriculado' => 1,
                'idempresamatriculado' => 1,
                'planohorariosmatriculado' => 1,
                'anoletivomatriculado' => 10,
                'motivoSituacao' => 0,
                'obsSituacao' => '',
                'escoladestino' => '',
            ]
        ]);

        $response = (string) $trocarTurmasRequest->getBody();
        var_dump($response);
        die;

        $matricula = $matricula->refresh();
        assertEquals($turmaNova->id, $matricula->turmamatriculado);
    }

    public function login()
    {
        $initialCookies = $this->http->getConfig('cookies')->toArray();

        $user = new Usuario();
        $user->idpessoa = rand();
        $user->idpermissao = 1;
        $user->login = 'teste' . rand();
        $user->senha = '123' . rand();
        $user->tipo = 2;
        $user->saveOrFail();

        $loginRequest = $this->http->post('/login', [
            'form_params' => [
                'login' => $user->login,
                'senha' => $user->senha,
            ]
        ]);

        $headers = $loginRequest->getHeaders();
        $currentCookies = $this->http->getConfig('cookies')->toArray();

        assertNotSame($initialCookies, $currentCookies);
        assertNotEmpty(array_filter($currentCookies, fn ($cookie) => $cookie['Name'] === 'PHPSESSID'));

        $homeRequest = $this->http->get('/');
        assertEquals(200, $homeRequest->getStatusCode());
    }
}
