<?php

declare(strict_types=1);

namespace App\Command;

use App\Academico\Model\Matricula;
use App\Model\Financeiro\Titulo;
use Carbon\Carbon;
use Illuminate\Database\Query\Expression;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReconstruirTitulosCommand extends Command
{
    protected static $defaultName = 'reconstruir-titulos';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Verifica inconsistencias em títulos e dados de matrícula.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $meses = range(7, 12);

        foreach ($meses as $mes) {
            $titulos = $this->titulosValidos()
                ->whereMonth('datavencimento', $mes)
                ->whereYear('datavencimento', Carbon::now()->year)
                ->get();

            $matriculasSemTitulo = Matricula::ativo()
                ->whereNotIn('nummatricula', $titulos->pluck('nummatricula'))
                ->get();

            foreach ($matriculasSemTitulo->filter(fn ($q) => $q->qtdparcelas > 0) as $matricula) {
                $output->writeln(
                    "Matricula Nº $matricula->nummatricula sem título no mes $mes"
                );

                $titulo = $this->titulosValidos()
                    ->where('nummatricula', $matricula->nummatricula)
                    ->first();

                $novoTitulo = Titulo::gerar(
                    $titulo->conta,
                    $titulo->funcionario,
                    $titulo->mMatricula,
                    Carbon::parse($titulo->datavencimento)->setMonth($mes),
                    $titulo->valor
                );

                foreach ($titulo->itens as $item) {
                    $novoItem = $item->replicate();
                    $novoItem->parcela = $mes;
                    $novoTitulo->itens()->save($novoItem);
                }

                $output->writeln(
                    "Gerado título Nº $novoTitulo->titulo com vencimento $novoTitulo->datavencimento"
                );
            }
        }

        $this->verificaInformacoesInconsistentes($output);

        return Command::SUCCESS;
    }

    private function verificaInformacoesInconsistentes(OutputInterface $output)
    {
        $matriculas = Matricula::select('id', 'nummatricula', 'qtdparcelas')->get();
        $titulos = $this->titulosValidos()
            ->select('nummatricula', new Expression('count(*) as qtd'))
            ->groupBy('nummatricula')
            ->get();

        foreach ($matriculas as $matricula) {
            $qtd = $titulos->where('nummatricula', $matricula->nummatricula)->first()->qtd ?? 0;
            if ($qtd != $matricula->qtdparcelas) {
                $output->writeln(
                    "Corrigindo Matricula Nº $matricula->nummatricula com $matricula->qtdparcelas títulos informados e $qtd registrados"
                );

                $matricula->qtdparcelas = $qtd;
                $matricula->save();
            }
        }
    }

    private function titulosValidos()
    {
        return Titulo::where(function ($q) {
            $q
                ->where('matricula', 1)
                ->orWhereHas('itens', fn ($x) => $x->whereIn('codigo', [
                    11_020_000,
                    11_030_000,
                    11_040_000,
                    11_050_000,
                    11_080_000,
                ]));
        })->where(function ($q) {
            $q->whereNull('dataexcluido')
                ->orWhere('dataexcluido', '0000-00-00');
        });
    }
}
