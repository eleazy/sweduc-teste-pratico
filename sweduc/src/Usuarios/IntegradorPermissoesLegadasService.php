<?php

declare(strict_types=1);

namespace App\Usuarios;

use App\Model\Core\Grupo;
use App\Model\Core\PermissaoGrupo;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Log\LoggerInterface;

use function App\Framework\resolve;

/**
 * IntegradorPermissoesLegadasService
 *
 * Classe de integração entre sistema de permissões legado
 * arquitetado para trabalhar com octais e novo sistema
 * de permissão que utiliza slugs
 */
class IntegradorPermissoesLegadasService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        if (!$logger) {
            $logger = resolve(LoggerInterface::class);
        }

        $this->logger = $logger;
    }

    /**
     * Exporta grupos e permissões do modelo original de
     * permissões do sistema para o novo sistema.
     *
     * @return void
     */
    public function exportarPermissoesLegadas()
    {
        $this->logger->info("Realizando importação das permissões para o novo sistema");

        // Sincroniza ids de grupo dos usuários
        // Puxando de idpermissao para politica_grupo_id
        DB::table('usuarios')->update([
            'politica_grupo_id' => DB::raw('idpermissao')
        ]);

        $permissoesLegadas = DB::table('permissoes')->get();

        foreach ($permissoesLegadas as $permissaoLegada) {
            $grupo = Grupo::firstOrCreate(
                [ 'id' => $permissaoLegada->id ],
                [ 'nome' => $permissaoLegada->perfil ]
            );

            $unidades = explode(',', $permissaoLegada->unidades);

            // Remove unidades revogadas
            PermissaoGrupo::where('grupo_id', $grupo->id)
                ->whereNotIn('unidade_id', $unidades)
                ->delete();

            foreach ($unidades as $unidade) {
                $this->removeAutorizacoesRevogadas($grupo, $permissaoLegada, $unidade);
                $this->adicionaNovasAutorizacoes($grupo, $permissaoLegada, $unidade);
            }
        }
    }

    public function listarPermissoes($grupoId)
    {
        $permissoes = DB::table('permissoes')->find($grupoId);
        return $this->permissoesLegadasAtivas($permissoes);
    }

    private function removeAutorizacoesRevogadas($grupo, $permissaoLegada, $unidade)
    {
        $permissoesInativas = $this->permissoesLegadasInativas($permissaoLegada);
        PermissaoGrupo::where('grupo_id', $grupo->id)
            ->whereIn('permissao', $permissoesInativas)
            ->where('unidade_id', $unidade ?? 0)
            ->delete();
    }

    private function adicionaNovasAutorizacoes($grupo, $permissaoLegada, $unidade)
    {
        $permissoesAtivas = array_map(fn($permissao) => [
            'grupo_id' => $grupo->id,
            'permissao' => $permissao,
            'unidade_id' => $unidade ?: null,
        ], $this->permissoesLegadasAtivas($permissaoLegada));

        PermissaoGrupo::insertOrIgnore($permissoesAtivas);
    }

    /**
     * Recebe objeto com as colunas do sistema de permissão antigo como
     * propriedades e retorna um array com as permissões habilitadas no novo formato
     *
     * @param Object $permissoes
     * @return Array Lista de permissões nomeadas
     */
    private function permissoesLegadasAtivas($permissoes)
    {
        return array_keys(
            array_filter($this->mapperPermissoesLegadas($permissoes))
        );
    }

    /**
     * Recebe objeto com as colunas do sistema de permissão antigo como
     * propriedades e retorna um array com as permissões desabilitadas no novo formato
     *
     * @param Object $permissoes
     * @return Array Lista de permissões nomeadas
     */
    private function permissoesLegadasInativas($permissoes)
    {
        $perm = array_keys($this->mapperPermissoesLegadas($permissoes));
        $permAtivas = $this->permissoesLegadasAtivas($permissoes);
        return array_diff($perm, $permAtivas);
    }

    /**
     * Recebe objeto com as colunas do sistema de permissão antigo como
     * propriedades e retorna um array com as permissões no novo formato
     *
     * @param Object $permissoes
     * @return Array Lista de permissões nomeadas
     */
    private function mapperPermissoesLegadas($permissoes)
    {
        return [
            // Academico na coluna de alunos da tabela permissoes
            'academico-alunos-consultar' => (int) $permissoes->alunos[0] & 0x1,
            'academico-alunos-cadastrar' => (int) $permissoes->alunos[0] & 0x2,
            'academico-alunos-editar' => (int) $permissoes->alunos[0] & 0x2,
            'academico-alunos-excluir' => (int) $permissoes->alunos[0] & 0x4,
            'academico-alunos-alterar-anuidade' => (int) $permissoes->alunos[7] & 0x1,
            'academico-responsaveis-consultar' => (int) $permissoes->alunos[1] & 0x1,
            'academico-responsaveis-cadastrar' => (int) $permissoes->alunos[1] & 0x2,
            'academico-responsaveis-editar' => (int) $permissoes->alunos[1] & 0x2,
            'academico-responsaveis-excluir' => (int) $permissoes->alunos[1] & 0x4,
            'academico-ocorrencias-consultar' => (int) $permissoes->alunos[2] & 0x1,
            'academico-entrevistas-consultar' => (int) $permissoes->alunos[3] & 0x1,
            'academico-solicitacoes-consultar' => (int) $permissoes->alunos[4] & 0x1,
            'academico-controle-portaria-consultar' => (int) $permissoes->alunos[5] & 0x1,
            'academico-matriculados-novo-ano-consultar' => (int) $permissoes->alunos[6] & 0x1,

            // Academico na coluna academico da tabela permissoes
            'academico-cursos-consultar' => (int) $permissoes->academico[0] & 0x1,
            'academico-cursos-cadastrar' => (int) $permissoes->academico[0] & 0x2,
            'academico-cursos-editar' => (int) $permissoes->academico[0] & 0x2,
            'academico-cursos-excluir' => (int) $permissoes->academico[0] & 0x4,
            'academico-series-consultar' => (int) $permissoes->academico[1] & 0x1,
            'academico-series-cadastrar' => (int) $permissoes->academico[1] & 0x2,
            'academico-series-editar' => (int) $permissoes->academico[1] & 0x2,
            'academico-series-excluir' => (int) $permissoes->academico[1] & 0x4,
            'academico-turmas-consultar' => (int) $permissoes->academico[2] & 0x1,
            'academico-turmas-cadastrar' => (int) $permissoes->academico[2] & 0x2,
            'academico-turmas-editar' => (int) $permissoes->academico[2] & 0x2,
            'academico-turmas-excluir' => (int) $permissoes->academico[2] & 0x4,
            'academico-disciplinas-consultar' => (int) $permissoes->academico[3] & 0x1,
            'academico-disciplinas-cadastrar' => (int) $permissoes->academico[3] & 0x2,
            'academico-disciplinas-editar' => (int) $permissoes->academico[3] & 0x2,
            'academico-disciplinas-excluir' => (int) $permissoes->academico[3] & 0x4,
            'academico-grade-consultar' => (int) $permissoes->academico[4] & 0x1,
            'academico-grade-cadastrar' => (int) $permissoes->academico[4] & 0x2,
            'academico-grade-editar' => (int) $permissoes->academico[4] & 0x2,
            'academico-grade-excluir' => (int) $permissoes->academico[4] & 0x4,
            'academico-faltas-consultar' => (int) $permissoes->academico[5] & 0x1,
            'academico-faltas-alterar' => (int) $permissoes->academico[5] & 0x2,
            'academico-notas-consultar' => (int) $permissoes->academico[6] & 0x1,
            'academico-notas-cadastrar' => (int) $permissoes->academico[6] & 0x2,
            'academico-notas-editar' => (int) $permissoes->academico[6] & 0x2,
            'academico-notas-excluir' => (int) $permissoes->academico[6] & 0x4,
            'academico-notas-2-liberar-unidade' => (int) $permissoes->academico[11] & 0x1,
            'academico-notas-2-liberar-periodo' => (int) $permissoes->academico[11] & 0x2,
            'academico-calculo-de-medias-consultar' => (int) $permissoes->academico[9] & 0x1,
            'academico-calculo-de-medias-cadastrar' => (int) $permissoes->academico[9] & 0x2,
            'academico-calculo-de-medias-editar' => (int) $permissoes->academico[9] & 0x2,
            'academico-calculo-de-medias-excluir' => (int) $permissoes->academico[9] & 0x4,
            'academico-relatorios-emitir' => (int) $permissoes->academico[7] & 0x1,
            'academico-graficos-emitir' => (int) $permissoes->academico[8] & 0x1,
            'academico-fechamento-bimestral-emitir' => (int) $permissoes->academico[10] & 0x1,
            'academico-diario-online-acessar' => (int) $permissoes->academico[12] & 0x1,
            'academico-diario-online-liberar' => (int) $permissoes->academico[15] & 0x1,
            'academico-diario-online-acesso-total' => (int) (int) $permissoes->academico[15] & 0x2,
            'academico-planejamento-pedagogico-consultar' => (int) $permissoes->academico[14] & 0x1,
            'academico-planejamento-pedagogico-cadastrar' => (int) $permissoes->academico[14] & 0x2,
            'academico-planejamento-pedagogico-excluir' => (int) $permissoes->academico[14] & 0x4,
            'academico-planejamento-pedagogico-editar' => (int) $permissoes->academico[14] & 0x4,

            // Marketing
            'marketing-prospeccao-acessar' => (int) $permissoes->marketing[0] & 0x1,
            'marketing-prospeccao-visualizar-todas' => (int) $permissoes->marketing[4] & 0x4,
            'marketing-prospeccao-deletar' => (int) $permissoes->marketing[0] & 0x4,
            'marketing-midia-acessar' => (int) $permissoes->marketing[1] & 0x1,
            'marketing-relatorio-acessar' => (int) $permissoes->marketing[3] & 0x1,
            'marketing-agenda-visualizacao-somente-a-do-funcionario' => (int) $permissoes->marketing[4] & 0x1,
            'marketing-agenda-visualizacao-de-todos-os-funcionarios' => (int) $permissoes->marketing[4] & 0x4,

            // Calendário (???)
            'calendario-eventos-acessar' => (int) $permissoes->calendario[0] & 0x1,
            'calendario-contas-a-pagar-acessar' => (int) $permissoes->calendario[1] & 0x1,

            'financeiro-contas-a-pagar-consultar' => (int) $permissoes->financeiro[0] & 0x1,
            // 'financeiro-contas-a-pagar-cadastrar' => (int) $permissoes->financeiro[0] & 0x2,
            'financeiro-contas-a-pagar-editar' => (int) $permissoes->financeiro[0] & 0x2,
            'financeiro-contas-a-pagar-excluir' => (int) $permissoes->financeiro[0] & 0x4,
            'financeiro-contas-a-pagar-pagar' => (int) $permissoes->financeiro[1] & 0x1,
            'financeiro-contas-a-pagar-reabrir' => (int) $permissoes->financeiro[1] & 0x2,
            'financeiro-contas-a-pagar-baixar' => (int) $permissoes->financeiro[1] & 0x4,
            'financeiro-contas-a-pagar-cancelar' => (int) $permissoes->financeiro[1] & 0x4,

            'financeiro-contas-a-receber-consultar' => (int) $permissoes->financeiro[2] & 0x1,
            'financeiro-contas-a-receber-boleto' => (int) $permissoes->financeiro[2] & 0x1,
            'financeiro-contas-a-receber-recibo' => (int) $permissoes->financeiro[2] & 0x1,
            'financeiro-contas-a-receber-abonar' => (int) $permissoes->financeiro[13] & 0x1,
            'financeiro-contas-a-receber-cadastrar' => (int) $permissoes->financeiro[2] & 0x2,
            'financeiro-contas-a-receber-editar' => (int) $permissoes->financeiro[2] & 0x2,
            'financeiro-contas-a-receber-excluir' => (int) $permissoes->financeiro[2] & 0x4,
            'financeiro-contas-a-receber-receber' => (int) $permissoes->financeiro[3] & 0x1,
            'financeiro-contas-a-receber-alterar-data-recebimento' => (int) $permissoes->financeiro[3] & 0x2,
            'financeiro-contas-a-receber-reabrir' => (int) $permissoes->financeiro[3] & 0x4,
            'financeiro-contas-a-receber-baixar' => (int) $permissoes->financeiro[3] & 0x4,
            'financeiro-contas-a-receber-cancelar' => (int) $permissoes->financeiro[3] & 0x4,
            'financeiro-contas-a-receber-reabrir-no-dia' => (int) $permissoes->financeiro[14] & 2,

            'financeiro-fornecedor-consultar' => (int) $permissoes->financeiro[4] & 0x1,
            'financeiro-fornecedor-cadastrar' => (int) $permissoes->financeiro[4] & 0x2,
            'financeiro-fornecedor-editar' => (int) $permissoes->financeiro[4] & 0x2,
            'financeiro-fornecedor-excluir' => (int) $permissoes->financeiro[4] & 0x4,

            'financeiro-relatorios-emitir' => (int) $permissoes->financeiro[5] & 0x1,
            'financeiro-relatorios-sintetico-analitico' => (int) $permissoes->financeiro[5] & 0x4,

            'financeiro-fluxo-de-caixa-fechamento-de-caixa-geral' => (int) $permissoes->financeiro[14] & 0x1,

            'financeiro-fluxo-de-caixa-contas-funcionario-extrato-propria-conta' => (int) $permissoes->financeiro[7] & 0x1,
            'financeiro-fluxo-de-caixa-contas-funcionario-movimentacao-propria-conta' => (int) $permissoes->financeiro[7] & 0x2,
            'financeiro-fluxo-de-caixa-contas-funcionario-extrato-todas-contas' => (int) $permissoes->financeiro[8] & 0x1,
            'financeiro-fluxo-de-caixa-contas-funcionario-movimentacao-todas-contas' => (int) $permissoes->financeiro[8] & 0x2,

            'financeiro-fluxo-de-caixa-contas-bancarias-extrato' => (int) $permissoes->financeiro[9] & 0x1,
            'financeiro-fluxo-de-caixa-contas-bancarias-movimentacao' => (int) $permissoes->financeiro[9] & 0x2,

            'financeiro-fluxo-de-caixa-contas-escola-extrato' => (int) $permissoes->financeiro[10] & 0x1,
            'financeiro-fluxo-de-caixa-contas-escola-movimentacao' => (int) $permissoes->financeiro[10] & 0x2,

            'financeiro-controle-de-cheques-buscar' => (int) $permissoes->financeiro[11] & 0x1,
            'financeiro-controle-de-cheques-trocar' => (int) $permissoes->financeiro[11] & 0x2,
            'financeiro-controle-de-cheques-devolver' => (int) $permissoes->financeiro[11] & 0x4,

            'financeiro-comissao-consultar' => (int) $permissoes->financeiro[12] & 0x1,
            'financeiro-comissao-cadastrar' => (int) $permissoes->financeiro[12] & 0x2,
            'financeiro-comissao-devolver' => (int) $permissoes->financeiro[11] & 0x4,

            'financeiro-formas-de-pagamento' => (int) $permissoes->configuracoes[17] > 0,
            'financeiro-tabela-descontos' => (int) $permissoes->configuracoes[17] > 0,
            'financeiro-desconto-comercial' => (int) $permissoes->configuracoes[17] > 0,

            'estoque-materiais-acessar' => (int) $permissoes->estoque[0] & 0x1,
            'estoque-materiais-baixar' => (int) $permissoes->estoque[0] & 0x2,
            'estoque-venda-de-material-acessar' => (int) $permissoes->estoque[1] & 0x1,
            'estoque-grupos-acessar' => (int) $permissoes->estoque[2] & 0x1,
            'estoque-kits-acessar' => (int) $permissoes->estoque[3] & 0x1,
            'estoque-unidade-de-contagem-acessar' => (int) $permissoes->estoque[4] & 0x1,
            'estoque-extrato-acessar' => (int) $permissoes->estoque[5] & 0x1,
            'estoque-movimentar-acessar' => (int) $permissoes->estoque[6] & 0x1,
            'estoque-balanco-acessar' => (int) $permissoes->estoque[7] & 0x1,

            'empresas-balanco-consultar' => (int) $permissoes->configuracoes[0] & 0x1,
            'empresas-balanco-cadastrar' => (int) $permissoes->configuracoes[0] & 0x2,
            'empresas-balanco-editar' => (int) $permissoes->configuracoes[0] & 0x2,
            'empresas-balanco-excluir' => (int) $permissoes->configuracoes[0] & 0x4,

            'empresas-unidades-consultar' => (int) $permissoes->configuracoes[1] & 0x1,
            'empresas-unidades-cadastrar' => (int) $permissoes->configuracoes[1] & 0x2,
            'empresas-unidades-editar' => (int) $permissoes->configuracoes[1] & 0x2,
            'empresas-unidades-excluir' => (int) $permissoes->configuracoes[1] & 0x4,

            'empresas-funcionarios-consultar' => (int) $permissoes->configuracoes[2] & 0x1,
            'empresas-funcionarios-cadastrar' => (int) $permissoes->configuracoes[2] & 0x2,
            'empresas-funcionarios-editar' => (int) $permissoes->configuracoes[2] & 0x2,
            'empresas-funcionarios-excluir' => (int) $permissoes->configuracoes[2] & 0x4,

            'empresas-perfil-de-funcionarios-consultar' => (int) $permissoes->configuracoes[3] & 0x1,
            'empresas-perfil-de-funcionarios-cadastrar' => (int) $permissoes->configuracoes[3] & 0x2,
            'empresas-perfil-de-funcionarios-editar' => (int) $permissoes->configuracoes[3] & 0x2,
            'empresas-perfil-de-funcionarios-excluir' => (int) $permissoes->configuracoes[3] & 0x4,

            'empresas-departamentos-consultar' => (int) $permissoes->configuracoes[4] & 0x1,
            'empresas-departamentos-cadastrar' => (int) $permissoes->configuracoes[4] & 0x2,
            'empresas-departamentos-editar' => (int) $permissoes->configuracoes[4] & 0x2,
            'empresas-departamentos-excluir' => (int) $permissoes->configuracoes[4] & 0x4,

            'empresas-solicitacoes-consultar' => (int) $permissoes->configuracoes[0] & 0x1,
            'empresas-solicitacoes-cadastrar' => (int) $permissoes->configuracoes[0] & 0x2,
            'empresas-solicitacoes-editar' => (int) $permissoes->configuracoes[0] & 0x2,
            'empresas-solicitacoes-excluir' => (int) $permissoes->configuracoes[0] & 0x4,

            'empresas-contas-caixas-consultar' => (int) $permissoes->configuracoes[5] & 0x1,
            'empresas-contas-caixas-cadastrar' => (int) $permissoes->configuracoes[5] & 0x2,
            'empresas-contas-caixas-editar' => (int) $permissoes->configuracoes[5] & 0x2,
            'empresas-contas-caixas-excluir' => (int) $permissoes->configuracoes[5] & 0x4,

            'empresas-turnos-consultar' => (int) $permissoes->configuracoes[6] & 0x1,
            'empresas-turnos-cadastrar' => (int) $permissoes->configuracoes[6] & 0x2,
            'empresas-turnos-editar' => (int) $permissoes->configuracoes[6] & 0x2,
            'empresas-turnos-excluir' => (int) $permissoes->configuracoes[6] & 0x4,

            'empresas-periodos-consultar' => (int) $permissoes->configuracoes[7] & 0x1,
            'empresas-periodos-cadastrar' => (int) $permissoes->configuracoes[7] & 0x2,
            'empresas-periodos-editar' => (int) $permissoes->configuracoes[7] & 0x2,
            'empresas-periodos-excluir' => (int) $permissoes->configuracoes[7] & 0x4,

            'empresas-salas-consultar' => (int) $permissoes->configuracoes[8] & 0x1,
            'empresas-salas-cadastrar' => (int) $permissoes->configuracoes[8] & 0x2,
            'empresas-salas-editar' => (int) $permissoes->configuracoes[8] & 0x2,
            'empresas-salas-excluir' => (int) $permissoes->configuracoes[8] & 0x4,

            'empresas-ocorrencias-consultar' => (int) $permissoes->configuracoes[9] & 0x1,
            'empresas-ocorrencias-cadastrar' => (int) $permissoes->configuracoes[9] & 0x2,
            'empresas-ocorrencias-editar' => (int) $permissoes->configuracoes[9] & 0x2,
            'empresas-ocorrencias-excluir' => (int) $permissoes->configuracoes[9] & 0x4,

            'empresas-controle-de-faltas-consultar' => (int) $permissoes->configuracoes[10] & 0x1,
            'empresas-controle-de-faltas-alterar' => (int) $permissoes->configuracoes[10] & 0x2,

            'empresas-avaliacoes-consultar' => (int) $permissoes->configuracoes[11] & 0x1,
            'empresas-avaliacoes-cadastrar' => (int) $permissoes->configuracoes[11] & 0x2,
            'empresas-avaliacoes-editar' => (int) $permissoes->configuracoes[11] & 0x2,
            'empresas-avaliacoes-excluir' => (int) $permissoes->configuracoes[11] & 0x4,

            'empresas-documentos-consultar' => (int) $permissoes->configuracoes[12] & 0x1,
            'empresas-documentos-cadastrar' => (int) $permissoes->configuracoes[12] & 0x2,
            'empresas-documentos-editar' => (int) $permissoes->configuracoes[12] & 0x2,
            'empresas-documentos-excluir' => (int) $permissoes->configuracoes[12] & 0x4,

            'empresas-eventos-financeiros-consultar' => (int) $permissoes->configuracoes[13] & 0x1,
            'empresas-eventos-financeiros-cadastrar' => (int) $permissoes->configuracoes[13] & 0x2,
            'empresas-eventos-financeiros-editar' => (int) $permissoes->configuracoes[13] & 0x2,
            'empresas-eventos-financeiros-excluir' => (int) $permissoes->configuracoes[13] & 0x4,

            'empresas-parcelamentos-consultar' => (int) $permissoes->configuracoes[14] & 0x1,
            'empresas-parcelamentos-cadastrar' => (int) $permissoes->configuracoes[14] & 0x2,
            'empresas-parcelamentos-editar' => (int) $permissoes->configuracoes[14] & 0x2,
            'empresas-parcelamentos-excluir' => (int) $permissoes->configuracoes[14] & 0x4,

            'empresas-formas-de-pagamento-acessar' => (int) $permissoes->configuracoes[16] & 0x1,
            'empresas-mensagem-recibo-alterar' => (int) $permissoes->configuracoes[17] & 0x1,
            'empresas-lista-de-ocorrencias-acessar' => (int) $permissoes->configuracoes[18] & 0x1,
            'empresas-notificacoes-acessar' => (int) $permissoes->configuracoes[19] & 0x1,
            'empresas-motivos-acessar' => (int) $permissoes->configuracoes[20] & 0x1,

            'sistema-editor-documentos' => (int) $permissoes->configuracoes[0] & 0x1,
            'sistema-upload-acessar' => (int) $permissoes->sistema[2] & 0x1,
            'sistema-backups-executar-backups' => (int) $permissoes->sistema[0] & 0x1,
            'sistema-log-de-atividades-visualizar-logs' => (int) $permissoes->sistema[1] & 0x1,
        ];
    }
}
