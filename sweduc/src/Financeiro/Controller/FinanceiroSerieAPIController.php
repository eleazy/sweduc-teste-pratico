<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use App\Framework\Http\BaseController;

class FinanceiroSerieAPIController extends BaseController
{
    public function getData(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $input = $this->jsonInput($request);
            $rematriculasArray = $input->alunos ?? [];

            $rematriculaIdAluno = DB::table('alunos_rematriculas')->find($rematriculasArray[0])->aluno_id;
            $idAnoLetivo = DB::table('anoletivo')->where('anoletivo', date("Y"))->first()->id;
            $matriculaInicial = DB::table('alunos_matriculas')
                ->where('idaluno', $rematriculaIdAluno)
                ->where('anoletivomatricula', $idAnoLetivo)
                ->where('status', 1)
                ->get();

            // pega a turma regular caso hajam multiplas matriculas
            foreach ($matriculaInicial as $matricula) {
                $turmaMat = DB::table('turmas')->find($matricula->turmamatricula);
                $serieMat = DB::table('series')->find($turmaMat->idserie);
                $cursoAuxiliar = DB::table('cursos')->find($serieMat->idcurso)->auxiliar;
                if ($cursoAuxiliar == 0) {
                    $matriculaInicial = $matricula;
                    break;
                }
            }

            $idSerie = DB::table('turmas')->find($matriculaInicial->turmamatricula)->idserie;

            // Devem todos ser da mesma serie
            for ($i = 1; $i < count($rematriculasArray); $i++) {
                $rematriculaIdAlunoLoop = DB::table('alunos_rematriculas')->find($rematriculasArray[$i])->aluno_id;
                $matriculaInicialLoop = DB::table('alunos_matriculas')
                    ->where('idaluno', $rematriculaIdAlunoLoop)
                    ->where('anoletivomatricula', $idAnoLetivo)
                    ->where('status', 1)
                    ->first();

                $turmaLoop = DB::table('turmas')->find($matriculaInicialLoop->turmamatricula);

                if ($turmaLoop->idserie != $idSerie) {
                    $response = $this->createResponse(400);
                    $response->getBody()->write(json_encode(['error' => 'Alunos não são da mesma série']));
                    return $response;
                }
            }

            // Pega serie sucessora
            $serieFin = DB::table('series')->where('serie_antecessora_id', $idSerie)->first();
            if (!$serieFin) {
                $response = $this->createResponse(400);
                $response->getBody()->write(json_encode(['error' => 'Série sucessora não encontrada']));
                return $response;
            }

            $eventoF = DB::table('eventosfinanceiros')->find($serieFin->idEventoFinanceiro)->eventofinanceiro;
            $conta = DB::table('contasbanco')->find($serieFin->idContaCaixaReceb)->nomeb;

            $serieFinanceiro = [
                'eventoFinanceiro' => $eventoF,
                'conta' => $conta,
                'quantidadeParcelas' => $serieFin->quantidadeParcelas,
                'valorParcelas' => $serieFin->valorParcelas,
                'dataPrimeiroVenc' => $serieFin->dataPrimeiroVenc,
            ];

            $nullValues = array_filter($serieFinanceiro, function ($value) {
                return is_null($value);
            });

            if (count($nullValues) > 0) {
                $response = $this->createResponse(400);
                $response->getBody()->write(json_encode(['error' => 'Dados financeiros da série incompletos']));
                return $response;
            }

            $response = $this->createResponse(200);
            $response->getBody()->write(json_encode($serieFinanceiro));
            return $response;
        } catch (Exception $e) {
            $response = $this->createResponse(400);
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response;
        }
    }
}
