<?php

declare(strict_types=1);

namespace App\Service;

use App\Academico\Model\Matricula;
use App\Model\Core\Documento;
use App\Academico\BoletimMedioService;
use App\Academico\BoletimInfantilService;
use App\Academico\BoletimService;
use Liquid\Drop;
use Liquid\Template;
use Mpdf\Mpdf;

class DocumentosService
{
    /**
     * Renderiza documentos do contexto acadêmico
     * @todo refatorar: pode causar efeitos colaterais no código
     *
     * @param string $template
     * @param array $matriculas Ids de matrículas
     * @return array Templates renderizados
     */
    public function documentoAluno(Documento $documento, array $metadados, bool $utfDecode = true, bool $toPDF = false): string
    {
        // Popula variáveis esperadas pelo script documentos_alunos.php
        extract($metadados);
        $email = true;
        $cliente = $_ENV['CLIENTE'];
        $idDOC = $documento->id;

        // Inicia captura de saida do script
        try {
            ob_start();
            include(__DIR__ . '/../../public/documentos_alunos.php');
        } catch (\Throwable $th) {
            ob_end_clean();
            throw $th;
        }

        //$documento = $utfDecode ? utf8_decode(ob_get_clean()) : ob_get_clean();
        $documento = ob_get_clean();

        if (!$toPDF) {
            return $documento;
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'tempDir' => '/tmp/docs_to_pdfs'
        ]);

        $mpdf->WriteHTML($documento);
        return $mpdf->Output('documento', 'S') ?? '';
    }

    /**
     * Renderiza array de templates de documentos
     *
     * @param array $matriculas Ids de matrículas
     * @return array Templates renderizados
     */
    public function matriculas(string $template, array $matriculas): array
    {
        $renderer = new Template();
        $renderer->parse($template);

        return array_map(function ($id) use ($renderer) {
            $matricula = Matricula::find($id);

            return $renderer->render([
                'sistema' => [],
                'matricula' => $this->matriculaDrop($matricula),
            ]);
        }, $matriculas);
    }

    private function matriculaDrop(Matricula $matricula): Drop
    {
        return new class ($matricula) extends Drop {
            private $aluno;
            private $turma;
            private $serie;
            private $curso;
            private $unidade;
            private $responsaveis;
            private $boletim;
            private $boletimInfantil;
            private $boletimMedio;

            public function __construct(private $matricula)
            {
                $this->matricula->loadMissing([
                    'turma.serie.curso.unidade',
                    'aluno.pessoa'
                ]);
            }

            public function aluno()
            {
                $this->aluno ??= $this->matricula->aluno;
                $aluno = $this->aluno;

                return [
                    'nome' => $aluno->pessoa->nome,
                ];
            }


            public function turma()
            {
                $this->turma ??= $this->matricula->turma;
                $turma = $this->turma;

                return [
                    'id' => $turma->id,
                    'nome' => $turma->turma,
                ];
            }


            public function serie()
            {
                $this->serie ??= $this->matricula->turma->serie;
                $serie = $this->serie;

                return [
                    'id' => $serie->id,
                    'nome' => $serie->serie,
                ];
            }


            public function curso()
            {
                $this->curso ??= $this->matricula->turma->serie->curso;
                $curso = $this->curso;

                return [
                    'id' => $curso->id,
                    'nome' => $curso->curso,
                ];
            }

            public function unidade()
            {
                $this->unidade ??= $this->matricula->turma->serie->curso->unidade;
                $unidade = $this->unidade;

                return [
                    'id' => $unidade->id,
                    'nome' => $unidade->unidade,
                ];
            }

            public function responsaveis()
            {
                return $this->responsaveis ??= $this->matricula->aluno->responsaveis->map(fn ($r) => [
                    'nome' => $r->pessoa->nome
                ]);
            }

            public function boletim()
            {
                if (!empty($this->boletim)) {
                    return $this->boletim;
                }

                $bs = new BoletimService($this->matricula);
                return $this->boletim = $bs->boletimCompleto(false);
            }

            public function boletimMedio()
            {
                if (!empty($this->boletimMedio)) {
                    return $this->boletimMedio;
                }

                $bs = new BoletimMedioService($this->matricula);
                return $this->boletimMedio = $bs->boletimCompleto(false);
            }

            public function boletimInfantil()
            {
                if (!empty($this->boletimInfantil)) {
                    return $this->boletimInfantil;
                }

                $bs = new BoletimInfantilService($this->matricula);
                return $this->boletimInfantil = $bs->boletimCompleto(false);
            }
        };
    }
}
