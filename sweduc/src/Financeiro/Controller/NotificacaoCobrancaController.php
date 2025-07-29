<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Framework\Http\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Carbon\CarbonImmutable;
use Illuminate\Database\Capsule\Manager as DB;
use App\Model\Sistema\EnvioDeEmail;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Carbon\Carbon;

class NotificacaoCobrancaController extends BaseController
{
    private const ITEMS_PER_PAGE = 25;

    public function index()
    {
        $situacoes = DB::table('financeiro_situacaotitulos')->where('exibir_contagem', 1)->get();
        $unidades = DB::table('unidades')->get();
        return $this->platesView('/Config/Financeiro/NotificacaoCobranca/Index', compact('situacoes', 'unidades'));
    }

    public function getNotificacao(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $notificacaoId = (int) $args['id'];
        $notificacao = DB::table('sistema_notificacao_de_cobranca')->find($notificacaoId);

        if (!$notificacao) {
            return $this->jsonResponse(['message' => 'Notificação não encontrada'], 404);
        }

        return $this->jsonResponse([
            'notificacaoAtiva' => $notificacao->notificacaoAtiva,
            'vencimentoApos' => $notificacao->vencimentoApos,
            'atrasoDe' => $notificacao->atrasoDe,
            'atrasoAte' => $notificacao->atrasoAte,
            'diasIntervaloEnvio' => $notificacao->diasIntervaloEnvio,
            'mudaStatusTitulo' => $notificacao->mudaStatusTitulo,
            'novoStatusTitulo' => $notificacao->novoStatusTitulo,
            'usaDataEnvio' => $notificacao->usaDataEnvio,
            'dataEnvio' => $notificacao->dataEnvio,
            'mensagemNotificacao' => $notificacao->mensagemNotificacao,
            'turmasJson' => json_decode($notificacao->turmasJson),
            'dataUltimoEnvio' => $notificacao->dataUltimoEnvio,
        ], 200);
    }

    public function setNotificacao(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $turmasJson = json_encode($data->turmas ?? [], JSON_UNESCAPED_UNICODE);

        try {
            DB::table('sistema_notificacao_de_cobranca')
                ->where('id', (int)$data->id)
                ->update([
                    'usuarioId' => $_SESSION['id_usuario'],
                    'turmasJson' => $turmasJson,
                    'notificacaoAtiva' => (bool)$data->notificacaoAtiva,
                    'vencimentoApos' => $data->vencimentoApos,
                    'atrasoDe' => (int)$data->atrasoDe,
                    'atrasoAte' => (int)$data->atrasoAte,
                    'diasIntervaloEnvio' => (int)$data->diasIntervaloEnvio,
                    'mudaStatusTitulo' => (bool)$data->mudaStatusTitulo,
                    'novoStatusTitulo' => $data->novoStatusTitulo,
                    'usaDataEnvio' => (bool)$data->usaDataEnvio,
                    'dataEnvio' => $data->dataEnvio,
                    'mensagemNotificacao' => $data->mensagemNotificacao,
                ]);

            return $this->jsonResponse(['message' => 'Notificação atualizada!'], 200);
        } catch (Throwable $e) {
            return $this->jsonResponse(['message' => "Erro ao configurar notificação $e->getMessage"], 422);
        }
    }

    public function setNotificacaoAtiva(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();

        try {
            DB::table('sistema_notificacao_de_cobranca')
                ->where('id', (int)$data->id)
                ->update([
                    'notificacaoAtiva' => (bool)$data->notificacaoAtiva,
                ]);

            $message = (bool)$data->notificacaoAtiva ? 'Notificação ativada!' : 'Notificação desativada!';

            return $this->jsonResponse(['message' => $message], 200);
        } catch (Throwable $e) {
            return $this->jsonResponse(['message' => "Erro ao alterar notificação: {$e->getMessage()}"], 422);
        }
    }

    public function getTitulos(ServerRequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true);

        $vencimentoApos = CarbonImmutable::parse($body['vencimentoApos'])->format('Y-m-d');
        $atrasoDe = (int) $body['atrasoDe'];
        $atrasoAte = (int) $body['atrasoAte'];
        $turmasJson = $body['turmasJson'];
        $idsTurma = array_map(fn($turma) => $turma['idTurma'], $turmasJson);

        $titulosQ = "SELECT af.titulo, fi.eventofinanceiro, pa.nome as aluno, pr.nome as resp, af.datavencimento, DATEDIFF(NOW(), af.datavencimento) as diasVencido, s.situacaotitulo
            FROM alunos_fichafinanceira af
            INNER JOIN alunos_fichaitens fi on fi.idalunos_fichafinanceira = af.id
            INNER JOIN alunos a on af.idaluno = a.id
            INNER JOIN alunos_matriculas am on a.id = am.idaluno
            INNER JOIN pessoas pa on a.idpessoa = pa.id
            LEFT JOIN responsaveis r on r.idaluno = a.id
            INNER JOIN pessoas pr on r.idpessoa = pr.id
            INNER JOIN financeiro_situacaotitulos s on af.situacao = s.situacaonumero
            WHERE ( af.situacao = 0 OR s.exibir_contagem = 1 )
            AND af.datavencimento >= :vencimentoApos
            AND af.datavencimento <= DATE_ADD(CURDATE(), INTERVAL - :atrasoDe DAY)
            AND af.datavencimento >= DATE_ADD(CURDATE(), INTERVAL - :atrasoAte DAY)
            AND r.respfin = 1";

        $params = [$vencimentoApos, $atrasoDe, $atrasoAte];

        if (!empty($idsTurma)) {
            $placeholders = [];
            foreach ($idsTurma as $index => $id) {
                $key = ":turma$index"; // Create unique named placeholders
                $placeholders[] = $key;
                $params[$key] = $id; // Bind values using named keys
            }
            $titulosQ .= " AND am.turmamatricula IN (" . implode(',', $placeholders) . ")";
        }

        $titulos = DB::select($titulosQ, $params);

        if (empty($titulos)) {
            return $this->jsonResponse(['message' => 'Nenhum título encontrado'], 404);
        }

        return $this->jsonResponse($titulos, 200);
    }

    // métodos relacionados a pagina de listagem de emails

    public function pendentes(ServerRequestInterface $request)
    {
        $pendentesModel = EnvioDeEmail::where('assunto', 'Notificação de cobrança')->where('status', 'pendente')->orderBy('id', 'desc');
        $pageNumber = filter_var($request->getQueryParams()['page'], FILTER_VALIDATE_INT) ?? 1;
        $search = $request->getQueryParams()['search'] ?? '';

        if ($search) {
            $pendentesModel->where('destinatario', 'like', "%{$search}%")
                ->orWhereRaw("metadados_para_processamento LIKE ?", ["%{$search}%"]);
        }

        $totalPages = $pendentesModel->count() / self::ITEMS_PER_PAGE;
        $pendentes = $pendentesModel
            ->skip(($pageNumber - 1) * self::ITEMS_PER_PAGE)
            ->take(25)
            ->get();

        return $this->platesView('Sistema/EmailCobranca/Pendentes', compact('pendentes', 'pageNumber', 'totalPages', 'search'));
    }

    public function enviados(ServerRequestInterface $request)
    {
        $enviadosModel = EnvioDeEmail::where('assunto', 'Notificação de cobrança')->where('status', 'sucesso')->orderBy('id', 'desc');
        $pageNumber = filter_var($request->getQueryParams()['page'], FILTER_VALIDATE_INT) ?? 1;
        $search = $request->getQueryParams()['search'] ?? '';

        if ($search) {
            $enviadosModel->where('destinatario', 'like', "%{$search}%")
                ->orWhereRaw("metadados_para_processamento LIKE ?", ["%{$search}%"]);
        }

        $totalPages = $enviadosModel->count() / self::ITEMS_PER_PAGE;
        $enviados = $enviadosModel
            ->skip(($pageNumber - 1) * self::ITEMS_PER_PAGE)
            ->take(25)
            ->get();

        return $this->platesView('Sistema/EmailCobranca/Enviados', compact('enviados', 'pageNumber', 'totalPages', 'search'));
    }

    public function falhas(ServerRequestInterface $request)
    {
        $falhadosModel = EnvioDeEmail::where('assunto', 'Notificação de cobrança')->where('status', 'falha')->orderBy('id', 'desc');
        $pageNumber = filter_var($request->getQueryParams()['page'], FILTER_VALIDATE_INT) ?? 1;
        $search = $request->getQueryParams()['search'] ?? '';

        if ($search) {
            $falhadosModel->where('destinatario', 'like', "%{$search}%")
                ->orWhereRaw("metadados_para_processamento LIKE ?", ["%{$search}%"]);
        }

        $totalPages = $falhadosModel->count() / self::ITEMS_PER_PAGE;
        $falhados = $falhadosModel
            ->skip(($pageNumber - 1) * self::ITEMS_PER_PAGE)
            ->take(25)
            ->get();

        return $this->platesView('Sistema/EmailCobranca/Falhas', compact('falhados', 'pageNumber', 'totalPages', 'search'));
    }

    public function logs(ServerRequestInterface $request)
    {
        $cliente = $_ENV['CLIENTE'];
        $pageNumber = filter_var($request->getQueryParams()['page'], FILTER_VALIDATE_INT) ?? 1;
        $search = $request->getQueryParams()['search'] ?? '';
        $perPage = self::ITEMS_PER_PAGE;

        // Read and reverse the file lines
        $logFile = file("clientes/$cliente/log_documentos_emails.txt") ?: [];

        // Always filter by "Notificação de cobrança"
        $logFile = array_filter($logFile, function ($line) {
            return stripos($line, 'Notificação de cobrança') !== false;
        });

        // Additional user-provided search filter
        if ($search) {
            $logFile = array_filter($logFile, function ($line) use ($search) {
                return stripos($line, $search) !== false;
            });
        }

        $logFile = array_values($logFile); // Reindex after filtering

        // Pagination
        $totalItems = count($logFile);
        $totalPages = ceil($totalItems / $perPage);
        $offset = ($pageNumber - 1) * $perPage;
        $pagedLines = array_slice($logFile, $offset, $perPage);

        // Parse the paginated lines
        $logs = $this->parseLogs($pagedLines);

        return $this->platesView('Sistema/EmailCobranca/Logs', compact('logs', 'pageNumber', 'totalPages', 'search'));
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

    public function cancelarEnvio(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = (int) $args['id'];

        try {
            $envio = EnvioDeEmail::find($id);
            $envio->delete();

            return $this->jsonResponse(['message' => 'Envio cancelado com sucesso'], 200);
        } catch (Throwable $e) {
            return $this->jsonResponse(['message' => 'Erro ao cancelar envio'], 422);
        }
    }
}
