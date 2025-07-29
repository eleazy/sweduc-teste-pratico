<?php

declare(strict_types=1);

namespace App\Command;

use App\Academico\Model\Matricula;
use App\Academico\Model\MediaCalculada;
use App\Academico\Model\Nota;
use App\Academico\Model\Responsavel;
use App\Model\Financeiro\Titulo;
use App\Academico\BoletimMedioService;
use App\Service\FilesystemService;
use Carbon\Carbon;
use League\Csv\Writer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCSVCommand extends Command
{
    protected static $defaultName = 'export-csv:all';
    protected const MAX_ROWS = 3;

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Exporta lista de alunos baseada no template de importação.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Exportando dados...");

        $fs = FilesystemService::local();

        // Matriculas
        $fs->write('exports/matriculas.csv', $this->exportMatriculas());

        // // Responsaveis
        $fs->write('exports/responsaveis.csv', $this->exportResponsaveis());

        // // Financeiro
        $fs->write('exports/financeiro.csv', $this->exportFinanceiro());

        // Notas
        $this->exportNotas($fs, $output);

        // Medias
        $this->exportMedias($fs, $output);

        return Command::SUCCESS;
    }

    protected function exportMatriculas(): string
    {
        $header = [
            'Numero da matrícula',
            'Numero do aluno',
            'Status do aluno',
            'Nome completo do aluno',
            'Sexo',
            'Data Nascimento',
            'País Nascimento',
            'Estado Nascimento',
            'Cidade Nascimento',
            'Estado civil',
            'Profissão',
            'Cpf',
            'RG',
            'Orgão Expedidor',
            'Reg. MEC',
            'Email',
            'Telefone',
            'Cep',
            'Logradouro',
            'Numero',
            'Complemento',
            'Bairro',
            'Estado',
            'Cidade',
            'Ano letivo Matrícula',
            'Unidade',
            'Empresa',
            'Curso',
            'Série',
            'Turma',
            'Turno',
            'Plano de horario',
        ];

        $records = Matricula::with('aluno')->get()->map(function ($mat) {
            $aluno = $mat->aluno;
            $pessoa = $mat->aluno->pessoa;

            return [
                $mat->nummatricula,
                $aluno->numeroaluno,
                $mat->statusTexto,
                $pessoa->nome,
                $pessoa->sexo,
                $pessoa->datanascimento,
                $pessoa->paisNascimento,
                $pessoa->estadoNascimento,
                $pessoa->cidadeNascimento,
                $pessoa->estadoCivil,
                $pessoa->profissao,
                $pessoa->cpf,
                $pessoa->rg,
                $pessoa->orgaoexp,
                $aluno->registromec,
                $pessoa->emails ? $pessoa->emails->join(',') : '',
                $pessoa->telefones ? $pessoa->telefones->join(',') : '',
                $pessoa->cep,
                $pessoa->logradouro,
                $pessoa->numero,
                $pessoa->complemento,
                $pessoa->bairro,
                $pessoa->estado,
                $pessoa->cidade,
                $mat->periodoLetivo,
                $mat->unidade->unidade,
                $mat->empresa->empresa,
                $mat->turma->serie->curso->curso,
                $mat->turma->serie->serie,
                $mat->turma->turma,
                $mat->turno->turno,
                $mat->planoHorario,
            ];
        });

        $csv = Writer::createFromString();
        //insert the header
        $csv->insertOne($header);

        //insert all the records
        $csv->insertAll($records);

        return (string) $csv;
    }

    protected function exportResponsaveis(): string
    {
        $header = [
            'Numero do aluno',
            'Nome responsável',
            'Parentesto do responsável',
            'Sexo responsável',
            'Estado Civil responsável',
            'CPF Responsável',
            'RG responsável',
            'Orgão expedidor resp',
            'Data de Expedição',
            'Nacionalidade',
            'Data de Nascimento',
            'Naturalidade (Estado)',
            'Naturalidade (Cidade)',
            'Email Resp',
            'Telefone Resp',
            'Profissão Resp',
            'Empresa Resp',
            'Cep empresa',
            'Bairro empresa',
            'Endereço empresa',
            'Numero empresa',
            'Complemento Empresa',
            'Telefone empresa',
            'Cep Residencial',
            'UF Residencial',
            'Municipio Residencial',
            'Bairro residencial',
            'Endereço Residencial',
            'Numero Residencial',
            'Complemento Residencial',
            'Responsável Financeiro(sim ou não)',
            'Segundo Responsável Financeiro(sim ou não)',
            'Resposável Pedagógico(sim ou não)',
            'Autorizado a retirar aluno da escola (sim ou não)',
            'Acesso ao financeiro(sim ou não)',
            'Acesso ao padagógico(sim ou não)',
        ];

        $records = Responsavel::with(['aluno', 'pessoa'])->get()->map(function ($resp) {
            $pessoa = $resp->pessoa;
            $aluno = $resp->aluno;

            return [
                $aluno->numeroaluno,
                $pessoa->nome,
                $resp->parentesco,
                $pessoa->sexo,
                $pessoa->estadoCivil,
                $pessoa->cpf,
                $pessoa->rg,
                $pessoa->orgaoexp,
                $pessoa->rg_expedido_em,
                $pessoa->paisNascimento,
                $pessoa->datanascimento,
                $pessoa->estadoNascimento,
                $pessoa->cidadeNascimento,
                $pessoa->emails ? $pessoa->emails->join(',') : '',
                $pessoa->telefones ? $pessoa->telefones->join(',') : '',
                $pessoa->profissao,
                $pessoa->empresa,
                $pessoa->trabalho_cep,
                $pessoa->trabalho_bairro,
                $pessoa->trabalho_logradouro,
                $pessoa->trabalho_numero,
                $pessoa->trabalho_complemento,
                $pessoa->trabalho_tel,
                $pessoa->cep,
                $pessoa->estado,
                $pessoa->cidade,
                $pessoa->bairro,
                $pessoa->logradouro,
                $pessoa->numero,
                $pessoa->complemento,
                $resp->respfin,
                $resp->respfin2,
                $resp->resppedag,
                $resp->autorizado,
                $resp->visualiza_financeiro,
                $resp->visualiza_pedagogico,
            ];
        });

        $csv = Writer::createFromString();
        //insert the header
        $csv->insertOne($header);

        //insert all the records
        $csv->insertAll($records);

        return (string) $csv;
    }

    protected function exportFinanceiro(): string
    {
        $header = [
            'Numero do título',
            'Numero do aluno',
            'Nome do aluno',
            'Nome do responsável',
            'Evento(s)',
            'Status do título',
            'Valor do título',
            'Vencimento',
            'Data de recebimento',
            'Bolsa',
            'Valor esperado',
            'Valor recebido',
        ];

        $csv = Writer::createFromString();
        //insert the header
        $csv->insertOne($header);

        Titulo::with(['aluno.pessoa', 'aluno.responsaveis', 'itens', 'recebimentos'])->chunkById(250, function ($titulos) use ($csv) {
            $records = $titulos->map(fn($titulo) => [
                $titulo->titulo,
                $titulo->aluno->numeroaluno,
                $titulo->aluno->pessoa->nome,
                $titulo->aluno->responsaveis ? $titulo->aluno->responsaveis->pluck('pessoa.nome')->join(',') : '',
                $titulo->itens->pluck('eventofinanceiro')->join("\n"),
                $titulo->situacaoTexto,
                $titulo->valor,
                $titulo->vencimento->toDateString(),
                $titulo->recebido_em != '0000-00-00' ? Carbon::parse($titulo->recebido_em)->toDateString() : '',
                $titulo->bolsa,
                $titulo->esperado,
                $titulo->recebimentos->sum('valorrecebido'),
            ]);

            //insert all the records
            $csv->insertAll($records);
        });

        return (string) $csv;
    }

    protected function exportNotas($fs, $out)
    {
        $header = [
            'NID (Dado interno)',
            'MID (Dado interno)',
            'Nome do aluno',
            'Número do aluno',
            'Disciplina',
            'Período',
            'Avaliação',
            'Nota',
        ];

        $csv = Writer::createFromString();
        //insert the header
        $csv->insertOne($header);

        Nota::with([
            'aluno.pessoa',
            'media.grade.disciplina',
            'media.periodo',
            'avaliacao'
        ])->chunkById(250, fn ($notas) => $csv->insertAll($notas->map(fn ($x) => [
            $x->id,
            '',
            $x->aluno->pessoa->nome,
            $x->aluno->numeroaluno,
            $x->media->grade->disciplina->disciplina,
            $x->periodo->periodo,
            $x->avaliacao->avaliacao,
            $x->nota,
        ])));

        $fs->write('exports/notas.csv', (string) $csv);
    }

    protected function exportMedias($fs, $out)
    {
        $header = [
            'MID (Dado interno)',
            'Nome do aluno',
            'Número do aluno',
            'Disciplina',
            'Período',
            'Avaliação',
            'Nota',
        ];

        $csv = Writer::createFromString();
        //insert the header
        $csv->insertOne($header);

        foreach (Matricula::cursor() as $matricula) {
            try {
                $bs = new BoletimMedioService($matricula);
                $bs->regeneraCacheMedias();
            } catch (\Throwable $th) {
                $out->writeln($th->getMessage());
            }
        }

        MediaCalculada::chunkById(250, fn ($medias) => $csv->insertAll($medias->map(fn ($x) => [
            $x->media_id,
            $x->matricula->aluno->pessoa->nome,
            $x->matricula->aluno->numeroaluno,
            $x->disciplina,
            $x->periodo,
            $x->avaliacao->avaliacao,
            $x->valor,
        ])));

        $fs->write('exports/medias.csv', (string) $csv);
    }
}
