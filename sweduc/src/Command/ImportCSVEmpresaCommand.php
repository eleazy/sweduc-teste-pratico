<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Core\Empresa;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ImportCSVEmpresaCommand extends Command
{
    protected static $defaultName = 'import-csv:empresas';
    protected const MAX_ROWS = 3;

    protected function configure()
    {
        if (!ini_get("auto_detect_line_endings")) {
            ini_set("auto_detect_line_endings", '1');
        }

        $this->addArgument('path', InputArgument::REQUIRED, 'Caminho do arquivo CSV');

        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Importa lista de empresas baseada no template de importação.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $csvReader = Reader::createFromPath($path)->setHeaderOffset(0);

        $table = new Table($output);
        $table->setHeaderTitle('Empresas');
        $table->setHeaders(array_slice($csvReader->getHeader(), 0, self::MAX_ROWS));

        foreach ($csvReader as $record) {
            $table->addRow(array_slice($record, 0, self::MAX_ROWS));
        }

        $table->render();

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Confirmar inserção? (s/n): ', false, '/^(y|s)/i');

        Empresa::unguard();
        if ($helper->ask($input, $output, $question)) {
            foreach ($csvReader as $namedRecord) {
                $record = array_values($namedRecord);
                Empresa::updateOrCreate(
                    [
                        'razaosocial' => $record[0],
                        'cnpj' => $record[2],
                    ],
                    [
                        'nomefantasia' => $record[1],
                        'inscricaomunicipal' => $record[3],
                        'cep' => $record[4],
                        'endereco' => $record[5],
                        'numero' => $record[6],
                        'complemento' => $record[7],
                        'bairro' => $record[8],
                        'uf' => $record[9],
                        'codigotributacaomunicipio' => $record[11],
                        'codigoCNAE' => $record[12],
                        'aliquotaISS' => $record[13],
                        'optantesimplesnacional' => $record[14],
                    ]
                );
            }

            $output->writeln('Registro inserido com sucesso!');
        }

        return Command::SUCCESS;
    }
}
