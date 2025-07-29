<?php

declare(strict_types=1);

namespace App\Configuracoes\Controller;

use App\Controller\Controller;
use Psr\Http\Message\ServerRequestInterface;
use App\Service\FilesystemService;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Controller de contas a pagar
 */
class LogoController extends Controller
{
    protected $auth;
    protected StreamFactoryInterface $streamFactory;

    public function index(ServerRequestInterface $request)
    {
        return $this->platesView('Config/Logo/Index');
    }

    public function store(ServerRequestInterface $request, array $params)
    {
        $arquivos = end($request->getUploadedFiles())->getStream()->getMetadata('uri');
        $fs = FilesystemService::cloud();
        $logo = "logo";

        $fs->writeStream($logo, fopen($arquivos, 'r'));
        echo shell_exec('cd .. && rm -rf storage/logo_cache');
    }
}
