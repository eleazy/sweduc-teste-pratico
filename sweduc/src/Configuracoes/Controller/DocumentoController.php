<?php

declare(strict_types=1);

namespace App\Configuracoes\Controller;

use App\Controller\Controller;
use App\Model\Core\Documento;
use App\Model\Core\Usuario;
use App\Service\DocumentosService;
use App\Service\FilesystemService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DocumentoController extends Controller
{
    private const ITEMS_PER_PAGE = 30;

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $pageNumber = $params['page'] ?? 1;
        $search = $params['search'] ?? '';
        $contexto = $params['contexto'] ?? 'todos';

        $contextoMap = [
            'todos' => [0, 1, 2, 3, 4, 5, 6],
            'alunos' => [0, 6],
            'turmas' => [4],
            'financeiro' => [1],
            'empresa' => [2],
            'responsaveis' => [3],
            'rematricula' => [5],
        ][$contexto];

        $documentoModel = Documento::whereIn('contexto', $contextoMap)->orderBy('nomedoc');

        if ($search) {
            $documentoModel->where('nomedoc', 'like', "%{$search}%");
        }

        $totalItems = $documentoModel->count();
        $totalPages = (int) ceil($totalItems / self::ITEMS_PER_PAGE);

        $documentos = $documentoModel
            ->skip(($pageNumber - 1) * self::ITEMS_PER_PAGE)
            ->take(self::ITEMS_PER_PAGE)
            ->get();

        return $this->platesView('Config/EditorDeDocumentos/ListagemDeDocumentos', [
            'usuario' => $request->getAttribute(Usuario::class),
            'documentos' => $documentos,
            'contexto' => $contexto,
            'pageNumber' => $pageNumber,
            'totalPages' => $totalPages,
            'search' => $search,
        ]);
    }

    public function edit(ServerRequestInterface $request, $params): ResponseInterface
    {
        $cliente = $_ENV['CLIENTE'];
        $id = $params['id'];

        $_SESSION['KCFINDER'] = [
            'disabled' => false,
            'uploadURL' => "/clientes/" . $cliente . "/upload",
            'uploadDir' => "",
        ];

        if ($id != 0) {
            try {
                $fs = FilesystemService::legado();
                $html = $fs->read("docs/{$id}.php");
            } catch (\Throwable) {
                //
            }
        }

        return $this->platesView('Config/EditorDeDocumentos/EditorDeDocumentos', [
            'usuario' => $request->getAttribute(Usuario::class),
            'documento' => Documento::find($id),
            'data' => $html ?? '',
            'id' => $id,
        ]);
    }

    public function documentoAcademico(ServerRequestInterface $request, $params): ResponseInterface
    {
        $input = $request->getParsedBody();
        $documentoId = filter_var($input['documentoId'], FILTER_VALIDATE_INT);
        $matriculasIds = filter_var(
            explode(',', $input['matriculaId']),
            FILTER_VALIDATE_INT,
            FILTER_FORCE_ARRAY
        );

        $documento = Documento::findOrFail($documentoId);

        return $this->platesView('Documentos/Padrao', [
            'nome' => $documento->nomedoc,
            'documentos' => (new DocumentosService())->matriculas($documento->template, $matriculasIds)
        ]);
    }

    /**
     * Esquema de paginação para seleção de documentos na listagem de alunos
     *
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function buscarDocumentoAcademico(ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();
        $search = $params['search'] ?? '';
        $page = $params['page'] ?? 1;

        $query = Documento::whereIn('contexto', [0, 5, 6]);

        if (!empty($search)) {
            $query->where('nomedoc', 'like', "%{$search}%");
        }

        $documentos = $query
            ->orderBy('nomedoc')
            ->offset(($page - 1) * self::ITEMS_PER_PAGE)
            ->limit(self::ITEMS_PER_PAGE)
            ->get();

        $hardcodedOptions = [];

        if ($page == 1 && $search === '') {
            $usuario = Usuario::fromSession();

            $hardcodedOptions[] = ['tipo' => 'separador', 'id' => null, 'nomedoc' => 'Ações'];
            if ($usuario->autorizado('academico-alunos-excluir')) {
                $hardcodedOptions[] = [
                    'id' => 'excluir',
                    'nomedoc' => 'EXCLUIR',
                ];
            }

            if ($usuario->autorizado('academico-alunos-consultar')) {
                $hardcodedOptions[] = ['id' => 'rematricula', 'nomedoc' => 'REMATRICULA'];
                $hardcodedOptions[] = ['id' => 'lancamento-mult', 'nomedoc' => 'LANÇAMENTO MÚLTIPLOS ALUNOS'];
                $hardcodedOptions[] = ['id' => 'emails-mult', 'nomedoc' => 'E-MAIL MÚLTIPLOS ALUNOS'];
                $hardcodedOptions[] = ['id' => 'ocorrencias', 'nomedoc' => 'OCORRÊNCIAS DE ALUNOS DA TURMA'];
                $hardcodedOptions[] = ['id' => 'etiqueta', 'nomedoc' => 'Etiqueta'];
                $hardcodedOptions[] = ['id' => 'trocar-turma', 'nomedoc' => 'Troca de etapa'];
                $hardcodedOptions[] = ['id' => 'mensagens-multi', 'nomedoc' => 'ENVIAR MENSAGEM INSTITUCIONAL'];
                $hardcodedOptions[] = ['tipo' => 'separador', 'id' => null, 'nomedoc' => 'Documentos', 'isGroup' => true];
            }
        }

        $todosDocs = array_merge($hardcodedOptions, $documentos->toArray());

        return $this->jsonResponse([
            'docs' => $todosDocs,
        ]);
    }
}
