<?php

declare(strict_types=1);

namespace App\Controller;

use App\Framework\Http\BaseController;
use App\Usuarios\AuditLogService;
use App\Usuarios\StaffAuthManager as StaffAuth;
use Auth0\SDK\Exception\StateException;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StaffLoginController extends BaseController
{
    protected $auditLog;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        AuditLogService $auditLog,
        protected StaffAuth $staffAuth,
    ) {
        parent::__construct($responseFactory);
        $this->auditLog = $auditLog;
    }

    public function login(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($request->getQueryParams())) {
            return $this->staffAuth->redirectToLogin();
        } else {
            try {
                $this->staffAuth->callback();
                return new RedirectResponse('/');
            } catch (StateException) {
                return new RedirectResponse('/staff-login');
            }
        }
    }

    public function logout(ServerRequestInterface $request)
    {
        return $this->staffAuth->redirectToLogout();
    }
}
