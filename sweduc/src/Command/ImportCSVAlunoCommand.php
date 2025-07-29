<?php

declare(strict_types=1);

namespace App\Command;

use App\Academico\Model\Aluno;
use App\Academico\Model\PeriodoLetivo;
use App\Academico\Model\PlanoHorario;
use App\Academico\Model\Turno;
use App\Model\Core\Empresa;
use App\Model\Core\EstadoCivil;
use App\Model\Core\Pais;
use App\Model\Core\Parentesco;
use App\Model\Core\Pessoa;
use App\Model\Core\Sexo;
use App\Model\Core\Unidade;
use App\Model\Core\Usuario;
use Carbon\Carbon;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ImportCSVAlunoCommand extends Command
{
    protected static $defaultName = 'import-csv:alunos';
    protected const MAX_ROWS = 3;

    protected function configure()
    {
        if (!ini_get("auto_detect_line_endings")) {
            ini_set("auto_detect_line_endings", '1');
        }

        $this->addArgument('path', InputArgument::REQUIRED, 'Caminho do arquivo CSV');

        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Importa lista de alunos baseada no template de importação.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $csvReader = Reader::createFromPath($path)->setHeaderOffset(0);

        $table = new Table($output);
        $table->setHeaderTitle('Alunos');
        $table->setHeaders(array_slice($csvReader->getHeader(), 0, self::MAX_ROWS));

        foreach ($csvReader as $record) {
            $trimmedRecord = array_slice($record, 0, self::MAX_ROWS);
            $table->addRow($trimmedRecord);
        }

        $table->render();

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Confirmar inserção? (s/n): ', false, '/^(y|s)/i');

        Usuario::unguard();
        if ($helper->ask($input, $output, $question)) {
            $alteracoesTable = new Table($output);
            $alteracoesTable->setHeaderTitle('Alterações');
            $alteracoesTable->setHeaders([
                'Nome',
                'Pessoa',
                'Usuario',
                'Aluno',
                'Matricula',
                'Responsavel',
                'Usuario do responsavel',
            ]);

            foreach ($csvReader as $record) {
                $paisNascimentoAluno = Pais::firstWhere('nom_pais', $record['País Nascimento']);
                $estadoNascimentoAluno = $paisNascimentoAluno->estado()->firstWhere('sgl_estado', $record['Estado Nascimento']);
                $cidadeNascimentoAluno = $estadoNascimentoAluno->cidade()->firstWhere('nom_cidade', $record['Cidade Nascimento']);

                $paisAluno = Pais::firstWhere('nom_pais', 'Brasil');
                $estadoAluno = $paisAluno->estado()->firstWhere('sgl_estado', $record['Estado']);
                $cidadeAluno = $estadoAluno->cidade()->firstWhere('nom_cidade', $record['Cidade']);

                $pessoa = Pessoa::updateOrCreate([
                    'nome' => $record['Nome Completo do Aluno'],
                    'cpf' => $record['Cpf'],
                ], [
                    'datanascimento' => $record['Data Nascimento'] ? Carbon::createFromFormat('d/m/Y', $record['Data Nascimento']) : '',
                    'idsexo' => Sexo::firstWhere('sexo', $record['Sexo'])->id,
                    'idestadocivil' => EstadoCivil::firstWhere('estadocivil', $record['Estado civil'])->id,
                    'profissao' => $record['Profissão'],
                    'rg' => $record['RG'],
                    'orgaoexp' => $record['Orgão Expedidor'],
                    'cep' => $record['Cep'],
                    'logradouro' => $record['Logradouro'],
                    'numero' => $record['Numero'],
                    'complemento' => $record['Complemento'],
                    'bairro' => $record['Bairro'],
                    'idpaisnascimento' => $paisNascimentoAluno->cod_pais,
                    'idestadonascimento' => $estadoNascimentoAluno->id,
                    'idcidadenascimento' => $cidadeNascimentoAluno->id,
                    'idpais' => $paisAluno->cod_pais,
                    'idestado' => $estadoAluno->id,
                    'idcidade' => $cidadeAluno->id,
                ]);

                $aluno = Aluno::updateOrCreate([
                    'idpessoa' => $pessoa->id,
                    'numeroaluno' => $record['Numero do Aluno'],
                ], [
                    'registromec' => $record['Reg. MEC'],
                ]);

                $usuario = Usuario::firstOrCreate([
                    'idpessoa' => $pessoa->id,
                ], [
                    'tipo' => 0,
                    'idpermissao' => 3,
                    'login' => 'a' . str_pad("$pessoa->id", 6, '0', STR_PAD_LEFT),
                    'senha' => 'padrao123',
                    'password_hash' => password_hash('padrao123', PASSWORD_DEFAULT),
                ]);

                $periodoLetivo = PeriodoLetivo::firstOrCreate(['anoletivo' => $record['Ano letivo Matrícula']]);
                $unidade = Unidade::firstOrCreate(['unidade' => $record['Unidade']]);
                $empresa = Empresa::firstOrCreate(['razaosocial' => $record['Empresa']]);
                $curso = $unidade->cursos()->firstOrCreate(['curso' => $record['Curso']]);
                $serie = $curso->series()->firstOrCreate(['serie' => $record['Série']]);
                $turno = Turno::firstOrCreate(['turno' => $record['Turno']]);
                $turma = $serie->turmas()->firstOrCreate(['turma' => $record['Turma'], 'idturno' => $turno->id]);
                $planoHorario = PlanoHorario::firstOrCreate(['codigo' => $record['Plano de horario']]);

                $this->alertaRecemGerado($planoHorario, $output);
                $this->alertaRecemGerado($periodoLetivo, $output);
                $this->alertaRecemGerado($unidade, $output);
                $this->alertaRecemGerado($empresa, $output);
                $this->alertaRecemGerado($curso, $output);
                $this->alertaRecemGerado($serie, $output);
                $this->alertaRecemGerado($turma, $output);
                $this->alertaRecemGerado($turno, $output);

                $matricula = $aluno->matriculas()->updateOrCreate([
                    'anoletivomatricula' => $periodoLetivo->id,
                    'turmamatricula' => $turma->id,
                    'idunidade' => $unidade->id,
                    'idempresa' => $empresa->id,
                ], [
                    'status' => 1,
                    'datamatricula' => Carbon::now(),
                    'datastatus' => Carbon::now(),
                    'idplanohorario' => $planoHorario->id,
                ]);

                if (!$matricula->nummatricula) {
                    $unidade->increment('numerodamatricula');
                    $matricula->nummatricula = $unidade->numerodamatricula;
                    $matricula->save();
                }

                if (!empty($record['Email'])) {
                    $pessoa->emails()
                        ->firstOrCreate(['email' => $record['Email']], ['primario' => true])
                    ;
                }

                if (!empty($record['Telefone'])) {
                    $pessoa->telefones()
                        ->firstOrCreate(['telefone' => $record['Telefone']], ['idtipotel' => 1])
                    ;
                }

                $paisResponsavel = Pais::firstWhere('nom_pais', 'Brasil');
                $estadoResponsavel = $paisResponsavel->estado()->firstWhere('sgl_estado', $record['UF Residencial']);
                $cidadeResponsavel = $estadoResponsavel->cidade()->firstWhere('nom_cidade', $record['Municipio Residencial']);

                $responsavel = Pessoa::updateOrCreate([
                    'nome' => $record['Nome responsável'],
                    'cpf' => $record['CPF Responsável'],
                ], [
                    'idsexo' => Sexo::firstWhere('sexo', $record['Sexo responsável'])->id,
                    'idestadocivil' => EstadoCivil::firstWhere('estadocivil', $record['Estado Civil responsável'])->id ?? 1,
                    'rg' => $record['RG responsável'],
                    'orgaoexp' => $record['Orgão expedidor resp'],
                    'rg_expedido_em' => $record['Data de Expedição'] ? Carbon::createFromFormat('d/m/Y', $record['Data de Expedição']) : '',
                    'datanascimento' => $record['Data de Nascimento'] ? Carbon::createFromFormat('d/m/Y', $record['Data de Nascimento']) : '',
                    'profissao' => $record['Profissão Resp'],
                    'empresa' => $record['Empresa Resp'],
                    'trabalho_cep' => $record['Cep empresa'],
                    'trabalho_bairro' => $record['Bairro empresa'],
                    'trabalho_logradouro' => $record['Endereço empresa'],
                    'trabalho_numero' => $record['Numero empresa'],
                    'trabalho_complemento' => $record['Complemento Empresa'],
                    'trabalho_tel' => $record['Telefone empresa'],
                    'cep' => $record['Cep Residencial'],
                    'bairro' => $record['Bairro residencial'],
                    'logradouro' => $record['Endereço Residencial'],
                    'numero' => $record['Numero Residencial'],
                    'complemento' => $record['Complemento Residencial'],
                    'idpais' => $paisResponsavel->cod_pais,
                    'idestado' => $estadoResponsavel->id,
                    'idcidade' => $cidadeResponsavel->id,
                ]);

                $usuarioResponsavel = Usuario::firstOrCreate([
                    'idpessoa' => $responsavel->id,
                ], [
                    'tipo' => 1,
                    'idpermissao' => 2,
                    'login' => 'r' . str_pad("$responsavel->id", 6, '0', STR_PAD_LEFT),
                    'senha' => 'padrao123',
                    'password_hash' => password_hash('padrao123', PASSWORD_DEFAULT),
                ]);

                $aluno->responsaveis()->updateOrCreate([
                    'idpessoa' => $responsavel->id,
                ], [
                    'idparentesco' => Parentesco::firstWhere('parentesco', $record['Parentesto do responsável'])->id,
                    'respfin' => $this->parseBoolOption($record['Responsável Financeiro(sim ou não)']),
                    'respfin2' => $this->parseBoolOption($record['Segundo Responsável Financeiro(sim ou não)']),
                    'resppedag' => $this->parseBoolOption($record['Resposável Pedagógico(sim ou não)']),
                    'autorizado' => $this->parseBoolOption($record['Autorizado a retirar aluno da escola (sim ou não)']),
                    'visualiza_financeiro' => $this->parseBoolOption($record['Acesso ao financeiro(sim ou não)']),
                    'visualiza_pedagogico' => $this->parseBoolOption($record['Acesso ao padagógico(sim ou não)']),
                ]);

                $responsavel->emails()
                    ->firstOrCreate(['email' => $record['Email Resp']], ['primario' => true])
                ;

                $responsavel->telefones()
                    ->firstOrCreate(['telefone' => $record['Telefone Resp']], ['idtipotel' => 1])
                ;

                $unidade->numerodoaluno = Aluno::whereHas(
                    'matriculas',
                    fn($q) => $q->where('idunidade', $unidade->id)
                )->max('numeroaluno') + 1;
                $unidade->save();

                $alteracoesTable->addRow([
                    $aluno->pessoa->nome,
                    $this->status($pessoa),
                    $this->status($usuario),
                    $this->status($aluno),
                    $this->status($matricula),
                    $this->status($responsavel),
                    $this->status($usuarioResponsavel),
                ]);

                $output->writeln("Importando <info>{$aluno->pessoa->nome}</info> / Aluno: <info>{$aluno->numeroaluno}</info> / Matricula: <info>{$matricula->nummatricula}</info>");
            }

            $alteracoesTable->render();
        }

        return Command::SUCCESS;
    }

    protected function parseBoolOption($val): bool
    {
        return strcasecmp($val, 'SIM') === 0;
    }

    private function status($model): string
    {
        if ($model->wasRecentlyCreated) {
            return 'Criado';
        }

        if ($model->wasChanged()) {
            return 'Atualizado';
        }

        return 'Sem modificações';
    }

    private function alertaRecemGerado($elemento, $output)
    {
        if ($elemento->wasRecentlyCreated) {
            $output->writeln('<error>' . $elemento::class . ' não encontrado, foi gerado um novo</error>');
        }
    }
}
