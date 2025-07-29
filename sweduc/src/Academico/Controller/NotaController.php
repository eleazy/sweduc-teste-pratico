<?php

declare(strict_types=1);

namespace App\Academico\Controller;

use App\Controller\Controller;
use App\Academico\Model\Aluno;
use App\Model\Core\Configuracao;
use League\Csv\Reader;
use League\Csv\Info;
use Psr\Http\Message\ServerRequestInterface;

class NotaController extends Controller
{
    public function listar(ServerRequestInterface $request)
    {
        $input = $request->getParsedBody() + $request->getQueryParams();

        $notasImportadas = [];
        if (count($request->getUploadedFiles()) && !empty($request->getUploadedFiles()['csv'])) {
            $arquivo = $request->getUploadedFiles()['csv'];
            $csvReader = Reader::createFromString((string) $arquivo->getStream());

            $contaDelimitadores = array_flip(Info::getDelimiterStats($csvReader, ["\t", ',', ';']));
            $delimitador = $contaDelimitadores[max(array_keys($contaDelimitadores))] ?? ',';
            $csvReader->setDelimiter($delimitador);

            $posicaoIdentificador = ($input['posicao-id'] ?? 1) - 1;
            $posicaoNota = ($input['posicao-nota'] ?? 2) - 1;

            foreach ($csvReader->getRecords() as $record) {
                $unindexedArray = array_values($record);
                if ($unindexedArray[$posicaoIdentificador] != "") {
                    $notasImportadas[$unindexedArray[$posicaoIdentificador]] = $unindexedArray[$posicaoNota];
                }
            }
        }

        $anoletivoId = $input['anoletivoId'];
        $gradeId = $input['gradeId'];
        $periodoId = $input['periodoId'];
        $avaliacaoId = $input['avaliacaoId'];
        $educacaoInfantil = filter_var($input['educacaoInfantil'], FILTER_VALIDATE_BOOL);
        $turmaId = $input['turmaId'] ?? null;
        $multiplasAvaliacoes = strpos($avaliacaoId, ',');
        $tipodefaltas = Configuracao::chave('tipodefaltas');
        $casasDecimaisNotas = Configuracao::chave('casasdecimaisnotas');
        $faltasPorDisciplina = $tipodefaltas == '1';
        $tipoId = $input['tipo-id'];
        if ($multiplasAvaliacoes) {
            $query = "SELECT
                    avaliacoes.*
                FROM disciplinas_avaliacoes
                INNER JOIN avaliacoes ON avaliacoes.id=disciplinas_avaliacoes.idavaliacao
                WHERE idgrade='$gradeId'
                AND idperiodo='$periodoId'";
        } else {
            $query = "SELECT *
                FROM avaliacoes
                WHERE avaliacoes.id IN ($avaliacaoId)
                ORDER BY id ASC";
        }

        $result = mysql_query($query);
        $avaliacoes = [];

        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $avaliacoes[] = $row;
        }

        $finalquery = $turmaId != "todos" ? "AND alunos_matriculas.turmamatricula=(SELECT idturma FROM grade WHERE id=$gradeId)" : '';
        $query = "SELECT *, alunos.id as aid
            FROM alunos, alunos_matriculas, pessoas, turmas
            WHERE alunos_matriculas.status < 2
            AND alunos_matriculas.turmamatricula=turmas.id
            AND alunos.idpessoa=pessoas.id
            AND alunos.id=alunos_matriculas.idaluno
            $finalquery
            AND alunos_matriculas.anoletivomatricula='$anoletivoId'
            ORDER BY nome ASC";

        $result = mysql_query($query);
        $mediadaturma = [];
        $alunos = [];
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $turmaId = $row['turmamatricula'];

            if ($faltasPorDisciplina) {
                $queryFalta = "SELECT faltas
                    FROM alunos_notas, medias
                    WHERE idaluno='{$row['aid']}'
                    AND alunos_notas.idmedia=medias.id
                    AND medias.idgrade='$gradeId'
                    AND medias.idperiodo='$periodoId'
                    AND alunos_notas.idavaliacao=0";

                $resultFalta = mysql_query($queryFalta);
                $rowFaltas = $resultFalta ? mysql_fetch_array($resultFalta, MYSQL_ASSOC) : false;
                $row['faltas'] = $rowFaltas ? $rowFaltas['faltas'] : '';
            }

            $row['avaliacoes'] = [];
            foreach (array_column($avaliacoes, 'id') as $avaliacaoId) {
                $queryNota = "SELECT nota
                    FROM alunos_notas, medias
                    WHERE idaluno='{$row['aid']}'
                    AND idmedia=medias.id
                    AND medias.idgrade='$gradeId'
                    AND medias.idperiodo='$periodoId'
                    AND idavaliacao = '$avaliacaoId'";

                $resultNota = mysql_query($queryNota);
                $rowNota = mysql_fetch_array($resultNota, MYSQL_ASSOC);

                if ($educacaoInfantil) {
                    $row['avaliacoes'][$avaliacaoId] = $rowNota ? $rowNota['nota'] : '';
                } else {
                    $row['avaliacoes'][$avaliacaoId] = $rowNota ? number_format((float) $rowNota['nota'], $casasDecimaisNotas, '.', '') : '';
                    $mediadaturma[$avaliacaoId] = (float) ($mediadaturma[$avaliacaoId] ?? 0) + (float) ($rowNota['nota'] ?? 0);
                }
            }

            $alunos[] = $row;
        }

        $modelsAlunos = Aluno::with('pessoa.email')->find(array_column($alunos, 'aid'));
        $alunosComNotasImportadas = array_map(
            function ($aluno) use ($modelsAlunos, $notasImportadas, $tipoId) {
                $email = $modelsAlunos->find($aluno['aid'])->pessoa->email ?? '';

                if (empty($notasImportadas)) {
                    $nota = "";
                } elseif ($tipoId == "aluno-email" && !empty($email)) {
                    $nota = $notasImportadas[(string) $email] ?? '';
                } elseif ($tipoId == "numeroaluno") {
                    $nota = $notasImportadas[$aluno["numeroaluno"]];
                }

                return $aluno + [
                    'email' => $email,
                    'notaImportada' => $nota,
                ];
            },
            $alunos
        );

        return $this->platesView('Academico/Notas/Listar', [
            'alunos' => $alunosComNotasImportadas,
            'avaliacoes' => $avaliacoes,
            'casasdecimaisnotas' => $casasDecimaisNotas,
            'cnt' => 1,
            'educacaoInfantil' => $educacaoInfantil,
            'faltasPorDisciplina' => $faltasPorDisciplina,
            'gradeId' => $gradeId,
            'multiplasAvaliacoes' => $multiplasAvaliacoes,
            'notasImportadas' => $notasImportadas,
            'periodoId' => $periodoId,
            'btnMedias' => true
        ]);
    }

    public function salvar()
    {
        //
    }
}
