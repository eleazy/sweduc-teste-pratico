<?php

declare(strict_types=1);

namespace App\Command\Oauth;

use Defuse\Crypto\Key;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateKeysCommand extends Command
{
    protected static $defaultName = 'oauth:generate-keys';
    protected const ENV_CONFIG = __DIR__ . '/../../../.env';
    protected const OAUTH_CONFIG_DIR = __DIR__ . '/../../../storage/';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Gera arquivos GPG e chave de assinatura para .env')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = realpath(self::OAUTH_CONFIG_DIR) . '/';
        if (file_exists(self::OAUTH_CONFIG_DIR)) {
            $output->writeln('Diretório ' . $path . ' encontrado');
        } else {
            $output->writeln('Criando diretório ' . $path);
            if (!mkdir(self::OAUTH_CONFIG_DIR, 0755, true)) {
                throw new Exception('Não é possível abrir ou criar o caminho /storage');
            }
        }

        $privateKey = openssl_pkey_new();
        $output->writeln('Exportando chave privada: <info>' . $path . '.key_rsa</info>');
        openssl_pkey_export_to_file($privateKey, self::OAUTH_CONFIG_DIR . '.oauth_private.key');

        $publicKey = openssl_pkey_get_details($privateKey)['key'];
        $output->writeln('Exportando chave publica: <info>' . $path . '.key_rsa_public</info>');
        file_put_contents('file:///' . self::OAUTH_CONFIG_DIR . '.oauth_public.key', $publicKey);

        $defuseKey = Key::createNewRandomKey()->saveToAsciiSafeString();
        $output->writeln('Gerando chave de criptografia:');
        $output->writeln('<info>' . $defuseKey . '</info>');

        $output->writeln('Salvando no .env');
        if (file_exists(self::ENV_CONFIG)) {
            file_put_contents(self::ENV_CONFIG, preg_replace(
                "/OAUTH_ENCRYPTION_KEY=.*/",
                "OAUTH_ENCRYPTION_KEY=$defuseKey",
                file_get_contents(self::ENV_CONFIG)
            ));
        } else {
            throw new Exception(self::ENV_CONFIG . ' não encontrado');
        }

        return self::SUCCESS;
    }
}
