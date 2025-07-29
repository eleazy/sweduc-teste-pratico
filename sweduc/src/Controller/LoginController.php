<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\AuthException;
use App\Exception\RecursoNaoAutorizadoException;
use App\Framework\Http\BaseController;
use App\Model\Core\Usuario;
use App\Usuarios\AuditLogService;
use App\Model\Core\Configuracao;
use App\Usuarios\AuthManager as Auth;
use AWS\CRT\HTTP\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class LoginController extends BaseController
{
    protected $auth;
    protected $auditLog;

    public function __construct(ResponseFactoryInterface $responseFactory, Auth $auth, AuditLogService $auditLog)
    {
        parent::__construct($responseFactory);
        $this->auth = $auth;
        $this->auditLog = $auditLog;
    }

    public function showLogin(ServerRequestInterface $request)
    {
        if ($this->auth->estaAutenticado()) {
            return new RedirectResponse('/');
        }

        $redirect = urlencode($request->getQueryParams()['redirect'] ?? '');
        $loginUrl = empty($redirect) ? 'login' : 'login?redirect=' . $redirect;

        $cor = Configuracao::chave('cor_identidade_visual') ?? '#fff4de';

        $cor = str_replace("#", "", $cor);
        if (strlen($cor) == 3) {
            $cor = str_repeat(substr($cor, 0, 1), 2) .
                str_repeat(substr($cor, 1, 1), 2) .
                str_repeat(substr($cor, 2, 1), 2);
        }
        $rgb = array(
            'r' => hexdec(substr($cor, 0, 2)),
            'g' => hexdec(substr($cor, 2, 2)),
            'b' => hexdec(substr($cor, 4, 2))
        );
        $corRgb = "rgb({$rgb['r']}, {$rgb['g']}, {$rgb['b']})";
        $corRgbTransparent = "rgba({$rgb['r']}, {$rgb['g']}, {$rgb['b']}, 0.2)";
        // Convert HEX to RGB

        $cliente = $_SERVER['CLIENTE'];

        return $this->platesView('Core/Login', compact('loginUrl', 'corRgb', 'corRgbTransparent', 'cliente'));
    }

    public function login(ServerRequestInterface $request)
    {
        $body = $request->getParsedBody();
        $username = $body['login'];
        $password = $body['senha'];
        $redirect = urldecode($request->getQueryParams()['redirect'] ?? '');
        $loginUrl = empty($redirect) ? 'login' : 'login?redirect=' . $redirect;

        // Valida os dados de login
        try {
            v::stringType()->noWhitespace()->notEmpty()->assert($username);
            v::stringType()->notEmpty()->assert($password);

            if (!$this->auth->estaAutenticado() && !$this->auth->autenticar($username, $password)) {
                throw AuthException::loginOuSenhaIncorreta();
            };

            // $usuario = $this->auth->usuario($request);
            // if ($usuario->senha === $password) {
            //     return new RedirectResponse('/trocar-senha');
            // };

            return new RedirectResponse($redirect ?: '/');
        } catch (AuthException | ValidationException $e) {
            $_SESSION['erro_msg'] = 'Usuário ou senha incorreta.';
            return new RedirectResponse($loginUrl);
        }
    }

    public function showTrocarSenha(ServerRequestInterface $request)
    {
        $response = [];
        $response['cliente'] = $_SERVER['CLIENTE'];
        $response['clienteNome'] = $_SERVER['CLIENTE_NOME'];
        $response['assetsVersion'] = ASSETS_VERSION;

        // Funções de 2fa desativadas
        // $otp = TOTP::create();
        // $otpNow = $otp->now();
        // $otp->setLabel('SWEduc');
        // $otpUri = $otp->getProvisioningUri();
        // $otpSecret = $otp->getSecret();

        return $this->platesView('Core/TrocarSenha', $response);
    }

    public function trocarSenha(ServerRequestInterface $request)
    {
        $response = [];
        $response['cliente'] = $_SERVER['CLIENTE'];
        $response['clienteNome'] = $_SERVER['CLIENTE_NOME'];
        $response['assetsVersion'] = ASSETS_VERSION;

        $params = $request->getParsedBody();
        $usuario = $request->getAttribute(Usuario::class);
        $senha = $params['nova-senha'] ?? null;

        if (empty($senha) || strlen($senha) < 6) {
            $response['erro'] = 'A senha deve conter 6 ou mais caracteres';
            return $this->platesView('Core/TrocarSenha', $response);
        }

        $usuario->password_hash = password_hash($params['nova-senha'], PASSWORD_DEFAULT);
        $usuario->senha = base64_encode(random_bytes(6));
        $usuario->save();

        // Funções de 2fa desativadas
        // $otpSecret = $params['otp-secret'];
        // $otpNow = $params['otp-now'];

        // $otp = TOTP::create($otpSecret);
        // $otp->setLabel('SWEduc');
        // $otpUri = $otp->getProvisioningUri();
        // $otpNow2 = $otp->now();
        // $otpVerify = $otp->verify($otpNow);
        // $otpSecret = $otp->getSecret();

        return new RedirectResponse('/');
    }

    public function logout(ServerRequestInterface $request)
    {
        $usuario = $request->getAttribute(Usuario::class);
        $this->auth->logout();

        if ($usuario->provider_iss) {
            return new RedirectResponse('/staff-logout');
        }

        return new RedirectResponse('/');
    }

    public function impersonate(ServerRequestInterface $request)
    {
        if ($this->auth->estaPersonificado()) {
            $this->auth->logout();
        }

        $usuario = Usuario::fromSession($_SESSION);
        $params = $request->getQueryParams();

        if (empty($params['usuarioId']) || filter_var($params['usuarioId'], FILTER_VALIDATE_INT) === false) {
            return $this->plainTextResponse('Parametro de usuário não reconhecido.', 422);
        }

        $usuarioAPersonificar = Usuario::find($params['usuarioId']);
        $this->autorizaPersonificacao($usuario, $usuarioAPersonificar);

        $labelUsuario = "{$usuario->pessoa->nome} ($usuario->login)";
        $labelUsuarioPers = "{$usuarioAPersonificar->pessoa->nome} ($usuarioAPersonificar->login)";
        $msg = "Usuário $labelUsuario entrou como $labelUsuarioPers.";
        $this->auditLog->log($msg, null, __METHOD__);

        $this->auth->personificar($usuarioAPersonificar);
        return $this->redirect('/', 301);
    }

    private function autorizaPersonificacao($usuario, $usuarioAPersonificar)
    {
        // O agente possui personificação bloqueada
        if ($usuarioAPersonificar->autorizado('sistema-autenticacao-impedir-personificacao')) {
            throw new RecursoNaoAutorizadoException();
        }

        $tipo = [
            Usuario::TIPO_FUNCIONARIO => $usuario->autorizado('sistema-autenticacao-personificar-funcionarios'),
            Usuario::TIPO_ALUNO => $usuario->autorizado('sistema-autenticacao-personificar-alunos-responsaveis'),
            Usuario::TIPO_RESPONSAVEL => $usuario->autorizado('sistema-autenticacao-personificar-alunos-responsaveis')
        ];

        // Tipo não registrado
        if (!array_key_exists($usuarioAPersonificar->tipo, $tipo)) {
            throw new RecursoNaoAutorizadoException();
        }

        // Tipo não autorizado
        if ($tipo[$usuarioAPersonificar->tipo] !== true) {
            throw new RecursoNaoAutorizadoException();
        }
    }

    // public function verificaPermissao(ServerRequestInterface $request): ResponseInterface
    // {
    //     $params = $request->getQueryParams();
    //     $permissao = $params['permissao'] ?? null;

    //     $usuario = Usuario::fromSession();
    //     $isAuthorized = $usuario->autorizado($permissao);

    //     return $this->jsonResponse(['authorized' => $isAuthorized], 200);
    // }
}
