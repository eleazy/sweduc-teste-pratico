<?php

declare(strict_types=1);

namespace App\Configuracoes\Controller;

use App\Controller\Controller;
use App\Model\Core\Usuario;
use App\Usuarios\AuditLogService;
use App\Usuarios\PermissoesService;
use Psr\Http\Message\ServerRequestInterface;

class PoliticaFuncionarioController extends Controller
{
    public function index(ServerRequestInterface $request, $params)
    {
        //
    }

    public function edit(ServerRequestInterface $request, $params)
    {
        //
    }

    public function save(ServerRequestInterface $request, $params)
    {
        $usuario = $request->getAttribute(Usuario::class);
        $input = $request->getParsedBody();

        $id = filter_var($params['id'], FILTER_SANITIZE_NUMBER_INT);
        $perfil = $input['perfil'];
        $alunosfinal = $input['alunosfinal'];
        $unidades = $input['idunidade'];
        $strUnidades = implode(',', $input['idunidade']);
        $academicofinal = $input['academicofinal'];
        $financeirofinal = $input['financeirofinal'];
        $estoquefinal = $input['estoquefinal'];
        $configuracoesfinal = $input['configuracoesfinal'];
        $sistemafinal = $input['sistemafinal'];
        $marketingfinal = $input['marketingfinal'];
        $comunicadofinal = $input['comunicadofinal'];
        $protocolofinal = '0';

        if ($input['action'] == 'cadastra') {
            $query = "INSERT INTO permissoes
            (
                perfil,
                alunos,
                unidades,
                academico,
                financeiro,
                estoque,
                configuracoes,
                sistema,
                marketing,
                comunicado,
                protocolo
            ) VALUES (
                '$perfil',
                '$alunosfinal',
                '$strUnidades',
                '$academicofinal',
                '$financeirofinal',
                '$estoquefinal',
                '$configuracoesfinal',
                '$sistemafinal',
                '$marketingfinal',
                '$comunicadofinal',
                '$protocolofinal'
            );";
        } else {
            $query = "UPDATE
                permissoes
            SET
                perfil='$perfil',
                alunos='$alunosfinal',
                unidades='$strUnidades',
                academico='$academicofinal',
                financeiro='$financeirofinal',
                estoque='$estoquefinal',
                configuracoes='$configuracoesfinal',
                sistema='$sistemafinal',
                marketing='$marketingfinal',
                comunicado = '$comunicadofinal',
                protocolo = '$protocolofinal'
                WHERE id=" . $id;
        }

        if ($result = mysql_query($query)) {
            $msg = "Política de funcionário $perfil atualizada com sucesso.";
            $permissoesService = new PermissoesService();

            $permissoes = $input['permissoes'];
            $unidades = $input['idunidade'];

            // Manter acima da importação de permissões legadas
            $permissoesService->aplicarPermissoes((int) $id, $permissoes, $unidades);
            $permissoesService->importaPermissoesLegadas();
        } else {
            $msg = "Erro ao atualizar o perfil $perfil!";
        }

        AuditLogService::log(
            $msg,
            compact(
                'id',
                'perfil',
                'alunosfinal',
                'academicofinal',
                'financeirofinal',
                'configuracoesfinal',
                'sistemafinal',
                'permissoes'
            ),
            __METHOD__
        );
        return $result ? $this->plainTextResponse($msg) : $this->plainTextResponse($msg, 500);
    }
}
