<?php

declare(strict_types=1);

namespace App\Academico\Controller;

use App\Controller\Controller;
use App\Model\AlunosMatricula;
use App\Model\Core\Configuracao;
use App\Model\Formula;
use App\Model\Grade;
use App\Model\Notas;
use App\Model\Periodos;
use App\Model\Serie;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '../../../../public/helper_notas.php';

class BoletimController extends Controller
{
    public function boletim(ServerRequestInterface $request)
    {
        $input = $request->getParsedBody();
        $configuracoes = $this->getConfiguracoes($request);

        // Requisição
        $databoletim = $input['databoletim'] ?? date("m");
        $mostrapontos = $input['mostrapontos'];
        $mostraAvaliacao = $input['mostraAvaliacao'];
        $pontosparaPF = filter_var($input['pontosparaPF'] ?? false, FILTER_VALIDATE_BOOL);
        $idaluno = $input['idaluno'];
        $nummatricula = $input['nummatricula'];
        $idanoletivo = $input['idanoletivo'];
        $idunidade = $input['idunidade'];
        $comfaltas = $input['comfaltas'];
        $tipodefaltas = Configuracao::chave('tipodefaltas');

        $hoje = date("Y-m-d");

        $querytmp = "SELECT turmamatricula FROM alunos_matriculas WHERE idaluno=" . $idaluno . " AND nummatricula=" . $nummatricula . " AND idunidade=" . $idunidade;
        $resulttmp = mysql_query($querytmp);
        $rowtmp = mysql_fetch_array($resulttmp, MYSQL_ASSOC);
        $idturma = $rowtmp['turmamatricula'];

        $query_curso = mysql_query("SELECT
                cursos.id as curso_id, series.id as serie_id
            FROM cursos
            JOIN series ON series.idcurso=cursos.id
            JOIN turmas ON turmas.idserie=series.id
            WHERE turmas.id=$idturma");

        $res_curso = mysql_fetch_assoc($query_curso);
        $curso_id = $res_curso['curso_id'];
        $serie_id = $res_curso['serie_id'];

        $query_periodos = "SELECT * FROM (
            SELECT * FROM periodos WHERE NOT EXISTS (SELECT 1 FROM cursos_periodos WHERE curso_id=$curso_id)
                UNION
            SELECT
                periodos.id,
                cp.ordenacao as colunaboletim,
                cp.situacaofinalanual,
                cp.recuperacao,
                cp.provafinal,
                cp.mediaanual,
                cp.conta_faltas,
                periodos.periodo,
                periodos.calculaMedia,
                DATE_FORMAT(inicio_em, '0000-%m-%d') as datade,
                DATE_FORMAT(termino_em, '0000-%m-%d') as dataate
            FROM cursos_periodos cp JOIN periodos ON cp.periodo_id = periodos.id
            WHERE curso_id=$curso_id
            ) as t
            WHERE DATE_FORMAT('$hoje', '%m-%d') >= DATE_FORMAT(datade, '%m-%d')
            AND {$configuracoes['indice_boletim']}
            ORDER BY colunaboletim ASC";

        $query1 = $query_periodos;
        $result1 = mysql_query($query1);
        $err = mysql_error();
        $periodos = [];
        while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
            $periodos[] = $row1;
        }

        return $this->platesView('Academico/Boletim/Padrao', compact(
            'curso_id',
            'databoletim',
            'idturma',
            'periodos',
            'pontosparaPF',
            'serie_id',
            'tipodefaltas',
        ) + $configuracoes);
    }

    public function boletimVermelho(ServerRequestInterface $request)
    {
        $input = $request->getParsedBody();
        $idaluno = filter_var($input['idaluno'] ?? null, FILTER_VALIDATE_INT);
        $nummatricula = filter_var($input['nummatricula'] ?? null, FILTER_VALIDATE_INT);
        $idunidade = filter_var($input['idunidade'] ?? null, FILTER_VALIDATE_INT);
        $idanoletivo = filter_var($input['idanoletivo'], FILTER_VALIDATE_INT);
        $usaPeriodosPorCursos = filter_var($input['usaPeriodosPorCursos'], FILTER_VALIDATE_BOOL);
        $cliente = $_ENV['CLIENTE'];

        $periodo = new Periodos();
        $alunos_matricula = new AlunosMatricula();
        $grade = new Grade();
        $serie = new Serie();
        $formulas = new Formula();
        $nota = new Notas();

        $rowtmp = $alunos_matricula->buscarAlunoMatricula($idaluno, $nummatricula, $idunidade);
        $idturma = $rowtmp['turmamatricula'];

        $res = mysql_query("SELECT idcurso, series.limiterecuperacoes, series.limiteprovasfinais, series.dependencias FROM turmas JOIN series ON series.id = idserie WHERE turmas.id = '$idturma' LIMIT 1;");
        $contexto_turma = mysql_fetch_assoc($res);
        $curso_id = $contexto_turma['idcurso'];
        $limite_de_recuperacoes = $contexto_turma['limiterecuperacoes'];
        $limite_dependencias = $contexto_turma['dependencias'];
        $limite_provasfinais = $contexto_turma['limiteprovasfinais'];

        $ano_letivo = getAnoLetivo($idanoletivo);
        $tipoperiodo = getTipoPeriodoPorTurma($idturma);
        $mudancaperiodo = getAnoMudanca($cliente);

        if (
            !empty($input['tipoPeriodo']) && (
                $input['tipoPeriodo'] === 'bimestre' ||
                $input['tipoPeriodo'] === 'trimestre'
            )
        ) {
            $mudancaperiodo = 0;
            $tipoperiodo = $input['tipoPeriodo'] === 'bimestre' ? "2" : "3";
            $usaPeriodosPorCursos = false;
        }

        $indice_boletim = ($tipoperiodo == "3" && $ano_letivo > $mudancaperiodo) ? " colunaboletim between 41 and 100 " : " colunaboletim between 1 and 40 ";

        $periodos = ($usaPeriodosPorCursos) ? $periodo->buscarPeriodosPorCurso($curso_id, $indice_boletim) : $periodo->buscarPeriodosMes(13, $indice_boletim);
        $possui_provafinal = count(
            (array) array_filter($periodos, fn ($periodo) => $periodo['provafinal'])
        ) > 0;

        return $this->platesView('Academico/Boletim/Vermelho', [
            'idaluno' => $idaluno,
            'nummatricula' => $nummatricula,
            'idunidade' => $idunidade,
            'idanoletivo' => $idanoletivo,
            'usaPeriodosPorCursos' => $usaPeriodosPorCursos,
            'cliente' => $cliente,
            'periodo' => $periodo,
            'alunos_matricula' => $alunos_matricula,
            'grade' => $grade,
            'serie' => $serie,
            'formulas' => $formulas,
            'nota' => $nota,
            'idturma' => $idturma,
            'curso_id' => $curso_id,
            'limite_de_recuperacoes' => $limite_de_recuperacoes,
            'limite_dependencias' => $limite_dependencias,
            'limite_provasfinais' => $limite_provasfinais,
            'ano_letivo' => $ano_letivo,
            'tipoperiodo' => $tipoperiodo,
            'mudancaperiodo' => $mudancaperiodo,
            'indice_boletim' => $indice_boletim,
            'periodos' => $periodos,
            'possui_provafinal' => $possui_provafinal,
        ]);
    }

    public function boletimGeral(ServerRequestInterface $request)
    {
        return $this->platesView('Academico/Boletim/Geral');
    }

    public function boletimGeralEvolucao(ServerRequestInterface $request)
    {
        return $this->platesView('Academico/Boletim/EvolucaoGeral');
    }

    public function boletimFaltas(ServerRequestInterface $request)
    {
        $configuracoes = $this->getConfiguracoes($request);

        return $this->platesView('Academico/Boletim/Faltas', compact(
            'usuario',
            'configuracoes'
        ));
    }

    public function boletimFaltasX(ServerRequestInterface $request)
    {
        $configuracoes = $this->getConfiguracoes($request);

        return $this->platesView('Academico/Boletim/FaltasX', compact(
            'usuario',
            'configuracoes'
        ));
    }

    public function boletimModelo2(ServerRequestInterface $request)
    {
        $configuracoes = $this->getConfiguracoes($request);

        return $this->platesView('Academico/Boletim/Modelo2', compact(
            'configuracoes'
        ));
    }

    public function boletimCets(ServerRequestInterface $request)
    {
        $configuracoes = $this->getConfiguracoes($request);

        return $this->platesView('Academico/Boletim/ModeloCets', compact(
            'configuracoes'
        ));
    }

    public function boletimCetsMes(ServerRequestInterface $request)
    {
        $configuracoes = $this->getConfiguracoes($request);

        return $this->platesView('Academico/Boletim/ModeloCetsMes', compact(
            'configuracoes'
        ));
    }

    public function boletimCetsFaltas(ServerRequestInterface $request)
    {
        $configuracoes = $this->getConfiguracoes($request);

        return $this->platesView('Academico/Boletim/ModeloCetsFaltas', compact(
            'configuracoes'
        ));
    }

    public function boletimCetsFundamental(ServerRequestInterface $request)
    {
        $configuracoes = $this->getConfiguracoes($request);

        return $this->platesView('Academico/Boletim/ModeloCetsFundamental', compact(
            'configuracoes'
        ));
    }

    private function getConfiguracoes(ServerRequestInterface $request)
    {
        $input = $request->getParsedBody() ?? $request->getQueryParams();
        $cliente = $_ENV['CLIENTE'];
        $idaluno = $input['idaluno'];
        $idanoletivo = $input['idanoletivo'];
        $nummatricula = $input['nummatricula'];
        $idunidade = $input['idunidade'];

        $querytmp = "SELECT turmamatricula FROM alunos_matriculas WHERE idaluno=" . $idaluno . " AND nummatricula=" . $nummatricula . " AND idunidade=" . $idunidade;
        $resulttmp = mysql_query($querytmp);
        $rowtmp = mysql_fetch_array($resulttmp, MYSQL_ASSOC);
        $idturma = $rowtmp['turmamatricula'];

        $notasDecimais = Configuracao::chave('notasdecimais') ?? 1;

        $ano_letivo = getAnoLetivo($idanoletivo);
        $tipoperiodo = getTipoPeriodoPorTurma($idturma);
        $mudancaperiodo = getAnoMudanca($cliente);
        $indice_boletim = ($tipoperiodo == "3" && $ano_letivo > $mudancaperiodo) ? " colunaboletim between 41 and 100 " : " colunaboletim between 1 and 40 ";

        $periodo = new Periodos();
        $periodos = $periodo->buscarPeriodosMes(13, $indice_boletim);
        $possui_provafinal = count((array) array_filter($periodos, fn($periodo) => $periodo['provafinal'])) > 0;

        $res = mysql_query("SELECT idcurso, series.limiterecuperacoes, series.limiteprovasfinais, series.dependencias FROM turmas JOIN series ON series.id = idserie WHERE turmas.id = '$idturma' LIMIT 1;");
        $contexto_turma = mysql_fetch_assoc($res);
        $curso_id = $contexto_turma['idcurso'];
        $limite_de_recuperacoes = $contexto_turma['limiterecuperacoes'];
        $limite_dependencias = $contexto_turma['dependencias'];
        $limite_provasfinais = $contexto_turma['limiteprovasfinais'];

        return compact(
            'cliente',
            'notasDecimais',
            'idturma',
            'idanoletivo',
            'ano_letivo',
            'tipoperiodo',
            'mudancaperiodo',
            'indice_boletim',
            'possui_provafinal',
            'curso_id',
            'limite_de_recuperacoes',
            'limite_dependencias',
            'limite_provasfinais'
        );
    }
}
