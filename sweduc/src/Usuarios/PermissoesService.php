<?php

declare(strict_types=1);

namespace App\Usuarios;

use App\Model\Core\PermissaoGrupo;

class PermissoesService
{
    protected IntegradorPermissoesLegadasService $integradorPermissoesLegadas;

    public function __construct()
    {
        $this->integradorPermissoesLegadas = new IntegradorPermissoesLegadasService();
    }

    /**
     * Salva permissões do grupo mediante passagem de array
     * com as permissões nomeadas-como-slugs. Essa função remove
     * qualquer permissão de grupo préviamente estabelecida.
     *
     * @param integer $grupoId Identificador do grupo afetado
     * @param array $permissoes Lista de permissões
     * @param array $unidades Lista de unidades
     * @return void
     */
    public function aplicarPermissoes(int $grupoId, array $permissoes, ?array $unidades)
    {
        PermissaoGrupo::where('grupo_id', $grupoId)->whereNotIn('unidade_id', $unidades)->delete();

        foreach ($unidades ?: [ 0 ] as $unidade) {
            $permGrp = PermissaoGrupo::where('grupo_id', $grupoId)
                ->whereNotIn('permissao', $permissoes)
                ->where('unidade_id', $unidade ?? 0)
                ->delete();

            $permissoesAtivas = array_map(fn($permissao) => [
                'grupo_id' => $grupoId,
                'permissao' => $permissao,
                'unidade_id' => $unidade ?: null,
            ], $permissoes);

            PermissaoGrupo::insertOrIgnore($permissoesAtivas);
        }
    }

    public function listarPermissoes(int $grupoId)
    {
        return PermissaoGrupo::where('grupo_id', $grupoId)
            ->get();
    }

    public function verificarPermissao(int $grupoId, string $permissao, int $unidadeId)
    {
        return PermissaoGrupo::where('grupo_id', $grupoId)
            ->where('unidade_id', $unidadeId)
            ->where('permissao', $permissao)
            ->exists();
    }

    public function verificarUnidades(int $grupoId, string $permissao): array
    {
        $permissoes = PermissaoGrupo::where('grupo_id', $grupoId)
            ->where('permissao', $permissao)
            ->get();

        $unidades = $permissoes->pluck('unidade_id')->toArray();
        return $unidades;
    }

    /**
     * Importa permissões do sistema antigo
     *
     * @return void
     */
    public function importaPermissoesLegadas()
    {
        $this
            ->integradorPermissoesLegadas
            ->exportarPermissoesLegadas();
    }
}
