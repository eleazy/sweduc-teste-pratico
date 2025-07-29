<?php

declare(strict_types=1);

namespace App\Command;

use App\Academico\Model\Disciplina;
use App\Academico\Model\PlanejamentoPedagogico;
use App\Model\Core\Unidade;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCSVPlanejamentoCommand extends Command
{
    protected static $defaultName = 'import-csv:planejamento';

    protected function configure()
    {
        if (!ini_get("auto_detect_line_endings")) {
            ini_set("auto_detect_line_endings", '1');
        }

        $this->addArgument('path', InputArgument::REQUIRED, 'Caminho do arquivo CSV');

        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Importa lista de planejamento academico baseada no template de importação.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $csvReader = Reader::createFromPath($path)->setHeaderOffset(0);

        PlanejamentoPedagogico::unguard();
        foreach ($csvReader as $namedRecord) {
            // $this->inserirPlanejamentos(
            //     $this->resolverIds(
            //         $namedRecord
            //     )
            // );
            // var_dump($namedRecord);
            // print_r(
            //     $this->resolverIds(
            //         $namedRecord
            //     )
            // );
            break;
        }

        $output->writeln('Registros inseridos com sucesso!');

        return Command::SUCCESS;
    }

    /**
     * Expandir dados
     *
     * Ano  Unidade Curso   Serie   Turma   Disciplina
     * 2022 Todas   Ensino Fundamental 2    6º ano  Todas   Ciências
     *
     * @param [type] $registro
     */
    private function resolverIds($registro): array
    {
        $academicoTransposto = null;
        $mapTurma = fn($turma, $serie, $curso, $unidade, $registro) => array_merge(
            $registro,
            [
                'turma_id' => $turma->id,
                'serie_id' => $serie->id,
                'curso_id' => $curso->id,
                'unidade_id' => $unidade->id,
            ]
        );

        $mapSerie = fn($serie, $curso, $unidade, $registro) => $serie->turmas->map(fn ($turma) => $mapTurma(
            $turma,
            $serie,
            $curso,
            $unidade,
            $registro,
        ));

        $mapCurso = fn($curso, $unidade, $registro) => $curso
            ->series()
            ->where('serie', $registro['Serie'])
            ->get()
            ->map(fn ($serie) => $mapSerie($serie, $curso, $unidade, $registro));

        $mapUnidade = fn($unidade, $registro) => $unidade
            ->cursos()
            ->where('curso', 'like', strtolower(trim($registro['Curso'])))
            ->get()
            ->map(fn ($curso) => $mapCurso($curso, $unidade, $registro));

        if ($registro['Unidade'] == 'Todas') {
            $academicoTransposto = Unidade::all()
                ->map(fn ($u) => $mapUnidade($u, $registro))
                ->flatten(3)
                ->filter();
        }

        if ($academicoTransposto) {
            return $academicoTransposto->map(fn($registroTransposto) => Disciplina::query()
                ->where('disciplina', 'like', trim($registro['Disciplina']))
                ->get()
                ->flatMap(fn ($d) => array_merge(
                    $registroTransposto,
                    ['disciplina_id' => $d->id]
                )))->toArray();
        }

        return [];
    }

    private function inserirPlanejamentos(array $registros)
    {
        array_map(function ($registro) {
            PlanejamentoPedagogico::create([
                'anoletivo' => null,
                'periodode' => $registro[''],
                'periodoate' => $registro[''],
                'iddisciplina' => $registro[''],
                'idserie' => $registro[''],
                'idturma' => $registro[''],
                'conteudo' => $registro[''],
            ]);
        }, $registros);
    }
}
