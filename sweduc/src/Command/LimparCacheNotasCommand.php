<?php

declare(strict_types=1);

namespace App\Command;

use App\Academico\Model\MediaCalculada;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LimparCacheNotasCommand extends Command
{
    protected static $defaultName = 'notas:limpar-cache';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Limpar cache de notas.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        MediaCalculada::truncate();
        return Command::SUCCESS;
    }
}
