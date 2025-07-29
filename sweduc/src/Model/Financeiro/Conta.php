<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Model\Core\Configuracao;
use App\Model\Core\Empresa;
use App\Model\Core\Funcionario;
use App\Model\Core\Unidade;
use App\Scopes\AtivoScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Conta extends Model
{
    public const TIPO_BANCO = 0;
    public const TIPO_FUNCIONARIO = 1;
    public const TIPO_ESCOLA = 2;

    protected $with = ['funcionario.pessoa'];

    protected static function booted()
    {
        static::addGlobalScope(
            new AtivoScope()
        );
    }

    /**
     * Verifica se o intervalo para baixa(cancelamento) do boleto já foi atingido.
     * Significando que o boleto não deve ser mais apresentado para pagamento pois
     * provavelmente não está mais ativo.
     *
     * @param Carbon $data data de emissao do boleto
     * @return bool TRUE se a data para baixa expirou, caso contrario FALSE
     */
    public function passadoPeriodoParaBaixa(Carbon $data)
    {
        return $data->addDays($this->baixa_dias)->isPast();
    }

    public function scopeEscola($query)
    {
        $query->where('tipo', self::TIPO_ESCOLA);
    }

    public function scopeBanco($query)
    {
        $query->where('tipo', self::TIPO_BANCO);
    }

    public function scopeFuncionarios($query)
    {
        $query->where('tipo', self::TIPO_FUNCIONARIO);
    }

    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class, 'idcontasorigem');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'idfuncionario');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }

    public function unidades()
    {
        return $this->belongsToMany(Unidade::class, 'unidades_empresas', 'idempresa', 'idunidade', 'idempresa');
    }

    public function getFormaPagamentoCartaoIdAttribute()
    {
        return $this->pagamento_online_forma_pagamento_id;
    }

    /**
     * Adiciona atributo virtual usado na camada de apresentação
     * para adicionar informações contextuais a cada tipo de conta
     * nome de funcionário em contas de funcionário, agencia em
     * contas de banco...
     *
     * @return void
     */
    public function getTituloAttribute()
    {
        if ($this->tipo == self::TIPO_BANCO) {
            return "$this->nomeb - $this->banconome - Ag.: $this->agencia - Cc.: $this->conta";
        }

        if ($this->tipo == self::TIPO_FUNCIONARIO) {
            $nome = $this->nomeb;

            // Remove palavra prefixada de caixa que pode aparecer de maneira irregular (caixa, CAIXA, Caixa)
            $prefixoPalavraCaixa = stripos($nome, 'caixa');
            if ($prefixoPalavraCaixa !== false) {
                $tamanhoPalavraCaixa = $prefixoPalavraCaixa + strlen('caixa');
                $nomeCorrigido = substr($nome, $tamanhoPalavraCaixa);
            }

            return "Caixa de " . ($nomeCorrigido ?? $nome);
        }

        if ($this->tipo == self::TIPO_ESCOLA) {
            return "$this->nomeb - $this->banconome";
        }

        return 'Tipo de conta desconhecido';
    }

    /**
     * Ativa ou desativa a visibilidade das contas bancárias
     * em menus e ações
     *
     * @param bool $visivel Se deve estar ativo/visível aos operadores
     * @return bool Resultado da operação
     */
    public function mudarVisibilidade(bool $visivel)
    {
        $this->desativado_em = $visivel ? null : Carbon::now();
        return $this->save();
    }

    /**
     * @todo Consertar resto da função
     *
     * @return array|int Array com situação e mensagem ou 0
     */
    public function verificaFechamento(): array|int
    {
        $caixa_fechamentomanual = Configuracao::chave('caixa_fechamentomanual') == 1;

        $teveFechamento = $this->movimentacoes->fechamento()->whereDate('datareferencia', Carbon::today())->count();
        if ($teveFechamento) {
            return [
                'situacao' => 'fechado',
                'mensagem' => 'Seu caixa já foi fechado por hoje. Você não poderá realizar recebimentos.'
            ];
        }

        // // Verifica fechamento manual habilitado
        // if ($caixa_fechamentomanual) {
        //     // Verifica fechamentos anteriores
        //     $q_anterior = "SELECT * from movimentacoes WHERE idfuncionariostatus = $idfuncionario AND datareferencia < '$hojef' ORDER BY id desc LIMIT 1";
        //     $e_anterior = mysql_query($q_anterior);
        //     $quant_resultados = mysql_num_rows($e_anterior);
        //     $buscaanterior = mysql_fetch_array($e_anterior, MYSQL_ASSOC);
        //     $motivo = $buscaanterior['motivo'];

        //     $fechamento = strpos($motivo, "Fechamento");

        //     if ($quant_resultados > 0 && ($fechamento === false)) {
        //         return [
        //             'situacao' => "aberto",
        //             'mensagem' => "Você possui movimentações em datas anteriores que estão pendentes de fechamento."
        //         ];
        //     }

        //     $dataref = '';
        //     if ($quant_resultados > 0 && ($fechamento !== false)) {
        //         $dataref .= " AND datarecebido > '{$buscaanterior['datareferencia']}' ";
        //     }

        //     // verifica se o funcionario fez algum recebimento
        //     $q_receb = "SELECT *
        //                 FROM alunos_fichasrecebidas af
        //                 JOIN contasbanco cb ON cb.id = af.idcontasbanco
        //                 WHERE af.idfuncionario = $idfuncionario
        //                 AND af.datarecebido < '$hojef' $dataref
        //                 AND cb.tipo = 1
        //                 ORDER BY id DESC";
        //     $e_receb = mysql_query($q_receb);
        //     $quant_receb = mysql_num_rows($e_receb);

        //     if ($quant_receb > 0) {
        //         return [
        //             'situacao' => "aberto",
        //             'mensagem' => "Você possui recebimentos em datas anteriores que estão pendentes de fechamento."
        //         ];
        //     }
        // }

        return 0;
    }

    public function contasDaUnidade($unidadeId)
    {

        $empresasUnidade = DB::table('unidades_empresas')
            ->where('idunidade', $unidadeId)
            ->get();

        $empresasId = [];
        foreach ($empresasUnidade as $empresa) {
            $empresasId[] = $empresa->idempresa;
        }

        $contasUnidade = Conta::banco()
            ->whereIn('idempresa', $empresasId)
            ->get();

        return $contasUnidade;
    }

    public $timestamps = false;
    protected $table = 'contasbanco';
}
