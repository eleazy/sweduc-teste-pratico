<?php

declare(strict_types=1);

namespace App\Controller\Sistema;

use App\Controller\Controller;
use App\Model\Core\Usuario;
use App\Model\Sistema\EnvioDeEmail;
use App\Service\AlunoEmailService;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class EmailController extends Controller
{
    private const ITEMS_PER_PAGE = 25;

    public function __construct(ResponseFactoryInterface $responseFactoryInterface)
    {
        parent::__construct($responseFactoryInterface);
    }

    public function enviarEmail(ServerRequestInterface $request)
    {
        try {
            $data = $request->getParsedBody();
            $emails = explode(',', $data['emailto']);
            $assunto = strip_tags($data['subject']);
            $corpoHTML = strip_tags(nl2br($data['bodyemail']));
            $anexo = substr($data['anexo'], strpos($data['anexo'], ",") + 1);

            $ms = new AlunoEmailService();
            foreach ($emails as $email) {
                $ms->enviarEmail(
                    $email,
                    $assunto,
                    $corpoHTML,
                    $corpoHTML,
                    ['relatorio.png' => $anexo]
                );
            }
        } catch (Throwable) {
            return $this->plainTextResponse('<script>if (confirm("Erro no envio do email. Tente novamente.\n\nFechar esta janela ?")) window.close();</script>');
        }

        return $this->plainTextResponse('<script>if (confirm("E-mail agendado com sucesso.\n\nFechar esta janela ?")) window.close();</script>');
    }

    /**
     * Retorna página pendentes do sistema de emails
     *
     * @return Response
     */
    public function pendentes(ServerRequestInterface $request)
    {
        $pendentesModel = EnvioDeEmail::where('status', 'pendente')
            ->where('assunto', '!=', 'Notificação de cobrança')
            ->orderBy('id', 'desc');
        $pageNumber = filter_var($request->getQueryParams()['page'], FILTER_VALIDATE_INT) ?? 1;
        $search = $request->getQueryParams()['search'] ?? '';

        if ($search) {
            $pendentesModel->where('destinatario', 'like', "%{$search}%");
        }

        $totalPages = $pendentesModel->count() / self::ITEMS_PER_PAGE;
        $pendentes = $pendentesModel
            ->skip(($pageNumber - 1) * self::ITEMS_PER_PAGE)
            ->take(25)
            ->get();

        return $this->platesView('Sistema/Email/Pendentes', compact('pendentes', 'pageNumber', 'totalPages', 'search'));
    }

    /**
     * Retorna página enviados do sistema de emails
     *
     * @return Response
     */
    public function enviados(ServerRequestInterface $request)
    {
        $enviadosModel = EnvioDeEmail::where('status', 'sucesso')
            ->where('assunto', '!=', 'Notificação de cobrança')
            ->orderBy('id', 'desc');
        $pageNumber = filter_var($request->getQueryParams()['page'], FILTER_VALIDATE_INT) ?? 1;
        $search = $request->getQueryParams()['search'] ?? '';

        if ($search) {
            $enviadosModel->where('destinatario', 'like', "%{$search}%");
        }

        $totalPages = $enviadosModel->count() / self::ITEMS_PER_PAGE;
        $enviados = $enviadosModel
            ->skip(($pageNumber - 1) * self::ITEMS_PER_PAGE)
            ->take(25)
            ->get();

        return $this->platesView('Sistema/Email/Enviados', compact('enviados', 'pageNumber', 'totalPages', 'search'));
    }

    /**
     * Retorna página falhas do sistema de emails
     *
     * @return Response
     */
    public function falhas(ServerRequestInterface $request)
    {
        $falhadosModel = EnvioDeEmail::where('status', 'falha')
            ->where('assunto', '!=', 'Notificação de cobrança')
            ->orderBy('id', 'desc');
        $pageNumber = filter_var($request->getQueryParams()['page'], FILTER_VALIDATE_INT) ?? 1;
        $search = $request->getQueryParams()['search'] ?? '';

        if ($search) {
            $falhadosModel->where('destinatario', 'like', "%{$search}%");
        }

        $totalPages = $falhadosModel->count() / self::ITEMS_PER_PAGE;
        $falhados = $falhadosModel
            ->skip(($pageNumber - 1) * self::ITEMS_PER_PAGE)
            ->take(25)
            ->get();

        return $this->platesView('Sistema/Email/Falhas', compact('falhados', 'pageNumber', 'totalPages', 'search'));
    }

    /**
     * Retorna página logs do sistema de emails com paginação
     *
     * @return Response
     */
    public function logs(ServerRequestInterface $request)
    {
        $cliente = $_ENV['CLIENTE'];
        $pageNumber = filter_var($request->getQueryParams()['page'], FILTER_VALIDATE_INT) ?? 1;
        $search = $request->getQueryParams()['search'] ?? '';
        $perPage = self::ITEMS_PER_PAGE;

        // Read and reverse the file lines
        $logFile = file("clientes/$cliente/log_documentos_emails.txt") ?: [];

        // Optional search filtering
        if ($search) {
            $logFile = array_filter($logFile, function ($line) use ($search) {
                return stripos($line, $search) !== false;
            });
            $logFile = array_values($logFile); // Reindex after filtering
        }

        // Pagination
        $totalItems = count($logFile);
        $totalPages = ceil($totalItems / $perPage);
        $offset = ($pageNumber - 1) * $perPage;
        $pagedLines = array_slice($logFile, $offset, $perPage);

        // Parse the paginated lines
        $logs = $this->parseLogs($pagedLines);

        return $this->platesView('Sistema/Email/Logs', compact('logs', 'pageNumber', 'totalPages', 'search'));
    }

    protected function parseLogs(array $lines)
    {
        return array_map(function ($line) {
            $data = Carbon::parse(substr($line, 0, 24));
            $rex = preg_match('/E-mail disparado para (\S*) sobre (.*) com (sucesso|falha)./i', $line, $captura);
            $email = $captura[1] ?? '';
            $assunto = $captura[2] ?? '';
            $resultado = $captura[3] ?? '';

            return [
                'data' => $data,
                'email' => $email,
                'assunto' => $assunto,
                'resultado' => ucfirst($resultado) ?: 'Falha',
            ];
        }, $lines);
    }

    public function setEmailConfigs(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();

        $keys = [
            'smtp_host',
            'smtp_port',
            'smtp_encrypt',
            'smtp_address',
            'smtp_displayname',
            'smtp_username',
            'smtp_password',
            'smtp_limiteEnvio',
        ];

        try {
            foreach ($keys as $key) {
                if (isset($data[$key])) {
                    DB::table('configuracoes_kv')->updateOrInsert(
                        ['chave' => $key],
                        ['valor' => $data[$key]]
                    );
                }
            }

            return $this->jsonResponse(['message' => 'Configurações de email atualizadas com sucesso']);
        } catch (Throwable $e) {
            return $this->jsonResponse(['message' => 'Erro ao atualizar configurações de email: ', $e->getMessage()], 500);
        }
    }

    public function index(ServerRequestInterface $request)
    {
        $smtp_conf = DB::table('configuracoes_kv')
            ->select('chave', 'valor')
            ->where('chave', 'like', 'smtp_%')
            ->get()
            ->mapWithKeys(fn($item) => [str_replace('smtp_', '', $item->chave) => $item->valor])
            ->toArray();

        $quantEnvios = DB::table('sistema_envio_de_emails')
            ->whereRaw('YEAR(created_at) = ? AND MONTH(created_at) = ?', [date('Y'), date('m')])
            ->where('status', 'sucesso')
            ->count();

        $is_internal_user = Usuario::fromSession()->is_internal_user ?? false;

        return $this->platesView('Sistema/EmailConfigs/Index', compact('is_internal_user', 'smtp_conf', 'quantEnvios'));
    }
}
