<?php

declare(strict_types=1);

namespace App\Controller;

use App\Usuarios\PermissoesService;
use App\Usuarios\AuthManager;
use Psr\Http\Message\ResponseFactoryInterface;
use App\Model\Core\Configuracao;
use App\Model\Core\Menu;
use App\Model\Core\Usuario;
use Psr\Http\Message\ServerRequestInterface;
use App\Service\FilesystemService;
use Intervention\Image\Exception\NotSupportedException;
use League\Glide\Responses\PsrResponseFactory;
use League\Glide\ServerFactory;
use Psr\Http\Message\StreamFactoryInterface;

use function App\Framework\resolve;

class HomeController extends Controller
{
    protected $auth;

    public function __construct(ResponseFactoryInterface $responseFactory, AuthManager $auth, protected StreamFactoryInterface $streamFactory)
    {
        parent::__construct($responseFactory);
        $this->auth = $auth;
    }

    /**
     * Retorna página inicial do sistema
     *
     * @return Response
     */
    public function index(ServerRequestInterface $request)
    {
        $cliente = $_SERVER['CLIENTE'];
        $clienteNome = $_SERVER['CLIENTE_NOME'];
        $assetsVersion = resolve('ASSETS_VERSION');
        $usuario = $request->getAttribute(Usuario::class);
        $menu = (new Menu($usuario))->root();
        $configuracoes = Configuracao::completa();
        $tipo = $usuario->tipo;
        $idpermissoes = $usuario->permissao;

        $listaPermissoes = [];
        if (!empty($usuario->politica_grupo_id)) {
            $permissoesService = new PermissoesService();
            $listaPermissoes = $permissoesService
                ->listarPermissoes($usuario->politica_grupo_id)
                ->pluck('permissao')
                ->toArray();
        }

        return $this->platesView('Core/Inicio', compact(
            'usuario',
            'cliente',
            'clienteNome',
            'assetsVersion',
            'menu',
            'tipo',
            'idpermissoes',
            'listaPermissoes'
        ) + $configuracoes);
    }

    /**
     * Retorna logo do sistema
     *
     * @return Response
     */
    public function logo(ServerRequestInterface $request)
    {
        $psrResponseFactory = new PsrResponseFactory($this->createResponse(), fn($stream) => $this->streamFactory->createStreamFromResource($stream));
        $options = $request->getQueryParams();
        $fs = FilesystemService::cloud();
        $legadoFs = FilesystemService::legado();

        try {
            if ($legadoFs->fileExists('logo.png')) {
                $legadoImage = $legadoFs->readStream('logo.png');
                $fs->writeStream('logo', $legadoImage);
                $legadoFs->delete('logo.png');
            }
        } catch (\Exception) {
            // Ignora
        }

        if ($fs->fileExists('logo')) {
            $path = 'logo';
        } elseif ($fs->fileExists('logo.png')) {
            $path = 'logo.png';
        } elseif ($fs->fileExists('logo.jpeg')) {
            $path = 'logo.jpeg';
        } elseif ($fs->fileExists('logo.jpg')) {
            $path = 'logo.jpg';
        } else {
            return $this->emptyResponse(404);
        }

        $server = ServerFactory::create([
            'source' => $fs,
            'source_path_prefix' => '/',
            'cache' => FilesystemService::local(),
            'cache_path_prefix' => '/logo_cache',
            'response' => $psrResponseFactory,
        ]);

        if (in_array('image/webp', explode(',', $request->getHeaderLine('accept')))) {
            $options['fm'] = 'webp';
        }

        try {
            return $server->getImageResponse($path, $options);
        } catch (NotSupportedException) {
            return $server->getImageResponse('logo', ['fm' => 'jpeg'] + $options);
        }
    }
}
