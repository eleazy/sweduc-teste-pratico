<?php

declare(strict_types=1);

namespace App\Configuracoes\Controller\Core;

use App\Controller\Controller;
use App\Model\Core\Grupo;
use App\Model\Core\Unidade;
use App\Model\Core\Usuario;
use App\Usuarios\PermissoesService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class PoliticaConfigController extends Controller
{
    public function __construct(ResponseFactoryInterface $responseFactory, private PermissoesService $permissoesService)
    {
        parent::__construct($responseFactory);
    }

    public function listarPoliticasFuncionarios()
    {
        $grupos = Grupo::all();

        // Caso o grupo esteja vazio, tenta importar do sistema de permissões antigo
        if ($grupos->isEmpty()) {
            $this->permissoesService->importaPermissoesLegadas();
            $grupos = Grupo::all();
        }

        return $this->liquidView('Politica/Funcionarios', compact('grupos'));
    }

    public function mostrarPoliticaFuncionariosPermissoes(ServerRequestInterface $request)
    {
        $id = (int) $request->getAttribute('id');
        $grupo = Grupo::findOrFail($id);

        $permissoes = $grupo->permissoes->pluck('permissao');
        $unidadesAtivas = $grupo->permissoes()->select('unidade_id')->distinct()->get()->pluck('unidade_id');
        $unidades = Unidade::all()->map(function ($unidade) use ($unidadesAtivas) {
            $unidade->selecionada = $unidadesAtivas->contains($unidade->id);
            return $unidade;
        });

        return $this->liquidView(
            'Politica/FuncionariosPermissoes',
            compact('id', 'permissoes', 'unidades', 'unidadesAtivas')
        );
    }

    public function salvarPoliticaFuncionariosPermissoes(ServerRequestInterface $request)
    {
        $grupoId = (int) $request->getAttribute('id');
        $params = $request->getParsedBody();
        $unidades = $params['unidade'];

        $permissoes = $params;
        unset($permissoes['grupoId']);
        unset($permissoes['unidade']);

        $this->permissoesService->aplicarPermissoes($grupoId, array_keys($permissoes), $unidades);

        return $this->jsonResponse();
    }

    public function listarPoliticasResponsaveis(ServerRequestInterface $request)
    {
        $usuario = $request->getAttribute(Usuario::class);
        $admin = $usuario->idpermissao == '1';
        $unidadesPermitidas = join(',', $usuario->autorizadoEmUnidades('empresas-perfil-de-pais-cadastrar-editar'));

        if ($admin) {
            $query  = "SELECT * FROM unidades ORDER BY unidade ASC";
        } else {
            $query  = "SELECT * FROM unidades WHERE id IN ($unidadesPermitidas) ORDER BY unidade ASC";
        }

        $result = mysql_query($query);
        $unidades = [];
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $unidades[$row['id']] = $row;
        }

        $unidadesIds = join(',', array_keys($unidades));
        $queryC  = "SELECT * FROM cursos WHERE idunidade IN ($unidadesIds) ORDER BY curso ASC";
        $resultC = mysql_query($queryC);
        while ($rowC = mysql_fetch_array($resultC, MYSQL_ASSOC)) {
            $unidades[$rowC['idunidade']]['cursos'][] = $rowC;
        }

        return $this->platesView('Politica/Responsaveis', compact('unidades'));
    }

    public function listarPoliticasAlunos()
    {
        return $this->liquidView('Politica/Aluno');
    }
}
