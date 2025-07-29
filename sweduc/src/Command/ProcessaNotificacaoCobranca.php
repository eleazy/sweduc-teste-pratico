<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CobrancaEmailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessaNotificacaoCobranca extends Command
{
    protected static $defaultName = 'app:process-emails-cobrancas';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Busca titulos em aberto e adiciona os emails para envio.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Executing billing email');
        $cs = new CobrancaEmailService();
        $cs->gerarEmailsCobrancas();

        return Command::SUCCESS;
    }
}
