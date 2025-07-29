<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use App\Academico\Model\Aluno;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Event\MovimentacaoDeTitulo;
use App\Academico\Model\Matricula;
use App\Model\Core\Configuracao;
use App\Model\Core\Funcionario;
use App\Model\Financeiro\AsaasCobranca;
use App\Service\Financeiro\DescontoComercialService;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Psr\EventDispatcher\EventDispatcherInterface;

class Titulo extends Model
{
    public const SITUACAO_ABERTO = 0;
    public const SITUACAO_RECEBIDO = 1;
    public const SITUACAO_CANCELADO = 2;
    public const SITUACAO_EXCLUIDO = 4;
    public const SITUACAO_RENEGOCIADO = 5;
    public const SITUACAO_RECEBIDO_RETORNO = 6;

    protected $with = ['conta', 'itens'];

    /**
     * Atribui valores da coluna matricula_id conforme o idaluno e nummatricula
     *
     * @param int $alunoId
     * @return void
     */
    public static function patchMatriculaId($alunoId)
    {
        DB::table('alunos_fichafinanceira')
            ->join('alunos_matriculas', 'alunos_matriculas.nummatricula', '=', 'alunos_fichafinanceira.nummatricula')
            ->where('alunos_matriculas.idaluno', '=', DB::raw('alunos_fichafinanceira.idaluno'))
            ->where('alunos_matriculas.idaluno', $alunoId)
            ->update([
                'alunos_fichafinanceira.matricula_id' => DB::raw('alunos_matriculas.id')
            ]);
    }

    public static function gerar(
        Conta $conta,
        Funcionario $funcionario,
        Matricula $matricula,
        CarbonInterface $vencimento,
        $valor = 0,
        array $metadados = [],
        array $outrasPropriedades = [],
        $recebido = false,
        $dataRecebimento = null,
    ) {
        $numeroTitulo = $conta->empresa->gerarNumeroDeTitulo();
        $titulo = new Titulo();

        foreach ($outrasPropriedades as $key => $value) {
            $titulo->{$key} = $value;
        }

        $titulo->titulo = $numeroTitulo;
        $titulo->idfuncionario = $funcionario->id;
        $titulo->idcontasbanco = $conta->id;
        $titulo->idaluno = $matricula->idaluno;
        $titulo->matricula_id = $matricula->id;
        $titulo->nummatricula = $matricula->nummatricula;
        $titulo->valor = $valor;
        $titulo->situacao = ($recebido) ? Titulo::SITUACAO_RECEBIDO : Titulo::SITUACAO_ABERTO;
        $titulo->data1parcela = Carbon::now();
        $titulo->dataemissao = Carbon::now();
        $titulo->datavencimento = $vencimento->subDay()->addWeekday();
        $titulo->metadados = json_encode($metadados, JSON_THROW_ON_ERROR);

        if ($recebido) {
            $titulo->datarecebimento = $dataRecebimento ?? $vencimento->subDay()->addWeekday();
            $titulo->data1parcela = $vencimento->subDay()->addWeekday();
            $titulo->dataemissao = $vencimento->subDay()->addWeekday();
            $titulo->valorrecebido = $valor;
        }

        $titulo->save();
        return $titulo;
    }

    public function adicionarItem(
        EventoFinanceiro $evento,
        BigDecimal $valor,
        int $parcela = 1,
        int $parcelas = 1,
        bool $descontoNoBoleto = false,
        bool $atualizaTotal = true
    ): ItemTitulo {
        $item = $this->itens()->create([
            'codigo' => $evento->codigo,
            'eventofinanceiro' => $evento->eventofinanceiro,
            'valor' => $valor,
            'parcela' => $parcela,
            'totalparcelas' => $parcelas,
            'descontoboleto' => $descontoNoBoleto,
        ]);

        if ($atualizaTotal) {
            $this->valor = (string) $valor->sum($this->valor)->toScale(2);
            $this->save();
        }

        return $item;
    }

    /**
     * Adiciona recebimentos ao título e troca situação para recebido
     *
     * @return void
     */
    public function receber(EventDispatcherInterface $eventDispatcher, int $funcionarioId, Recebimento ...$recebimentos)
    {
        $this->recebimentos()->saveMany($recebimentos);
        $this->recebidoEm = Carbon::now();
        $this->situacao = self::SITUACAO_RECEBIDO;
        $this->valorrecebido = $this->recebimentos->sum('valorrecebido');
        $this->save();
        $eventDispatcher->dispatch(MovimentacaoDeTitulo::recebimento($this->id, $funcionarioId));
    }

    /**
     * Reabre o título
     *
     * @return void
     */
    public function reabrir()
    {
        $this->recebidoEm = '0000-00-00';
        $this->situacao = self::SITUACAO_ABERTO;
        $this->recebimentos()->delete();
        $this->save();
    }

    /**
     * Exclui o título
     *
     * @return void
     */
    public function excluir(EventDispatcherInterface $eventDispatcher, int $funcionarioId)
    {
        $this->situacao = self::SITUACAO_EXCLUIDO;
        $this->dataexcluido = Carbon::now();
        $this->excluido_por_id_funcionario = $funcionarioId;

        $this->save();
        $eventDispatcher->dispatch(MovimentacaoDeTitulo::exclusao($this->id, $funcionarioId));
    }

    public function getParcelamentoMaximoAttribute()
    {
        $contaId = $this->conta->id;
        $eventos = $this
            ->itens
            ->map(fn($x) => $x->evento->id)
            ->unique();

        if ($eventos->count() !== 1) {
            return 1;
        }

        $eventoId = $eventos->first();
        $valor = $this->total;

        $parcelamentosPossiveis = CondicaoDeParcelamento::where('conta_id', $contaId)
            ->where(function ($query) use ($eventoId) {
                $query->whereNull('evento_id')
                    ->orWhere('evento_id', $eventoId);
            })
            ->where(function ($query) use ($valor) {
                $query->whereNull('limite_valor')
                    ->orWhere('limite_valor', '<=', $valor);
            })
            ->get();

        $maiorParcelamento = $parcelamentosPossiveis->max('limite_parcelamento');
        return $maiorParcelamento ?: 1;
    }

    public function scopeSituacao($query, $situacao)
    {
        // Vencidos
        if ($situacao == 'vencidos') {
            $query->whereDate('datavencimento', '<', Carbon::now()->startOfDay());
        }

        // Titulos não vencidos
        if ($situacao == 'avencer') {
            $query->whereDate('datavencimento', '>', Carbon::now()->startOfDay());
        }

        // Titulos abertos
        if ($situacao == 'abertos' || $situacao == 'vencidos' || $situacao == 'avencer') {
            $query->whereNotIn('situacao', [
                self::SITUACAO_RECEBIDO,
                self::SITUACAO_RECEBIDO_RETORNO,
                self::SITUACAO_EXCLUIDO,
                self::SITUACAO_CANCELADO,
                self::SITUACAO_RENEGOCIADO,
            ]);
        }

        // Recebidos
        if ($situacao == 'recebidos') {
            $query->whereIn('situacao', [
                self::SITUACAO_RECEBIDO,
                self::SITUACAO_RECEBIDO_RETORNO,
            ]);
        }
    }

    public function scopeNaoExcluidos($query)
    {
        $query->whereNotIn('situacao', [
            self::SITUACAO_EXCLUIDO,
            self::SITUACAO_CANCELADO,
            self::SITUACAO_RENEGOCIADO
        ]);
    }

    public function getAtrasadoAttribute()
    {
        return $this->vencido && !$this->recebido;
    }

    public function getRecebidoEmAttribute()
    {
        if (!$this->datarecebimento || $this->datarecebimento == '0000-00-00') {
            return null;
        }

        return Carbon::parse($this->datarecebimento, 'utc');
    }

    public function setRecebidoEmAttribute($value)
    {
        $this->datarecebimento = $value;
    }

    public function getBolsaAposVencimento()
    {
        return Configuracao::chave('perdebolsa') == 0;
    }

    public function getRecebidoAttribute()
    {
        return $this->situacao == self::SITUACAO_RECEBIDO || $this->situacao == self::SITUACAO_RECEBIDO_RETORNO;
    }

    public function getBolsaAttribute($bolsa)
    {
        $bolsaAposVencimento = $this->getBolsaAposVencimento();
        $bolsaAtiva = !$this->vencido || $bolsaAposVencimento;
        return  $bolsaAtiva ? $bolsa : 0;
    }

    public function getEsperadoAttribute()
    {
        return $this->valor - $this->bolsa;
    }

    public function getSaldoAttribute()
    {
        return $this->valorrecebido - $this->esperado - $this->desconto + $this->multa + $this->juros;
    }

    public function getDescontoComercialAtivoAttribute()
    {
        return $this->itens->where('descontoboleto', 1)->isNotEmpty();
    }

    public function getDescontoComercialMsgAttribute()
    {
        if ($this->descontoComercialAtivo) {
            return DescontoComercialService::fromTitulo($this)->getMensagem();
        }

        return '';
    }

    public function getDescontoComercialValorAttribute()
    {
        if ($this->descontoComercialAtivo) {
            $calculadora = DescontoComercialService::fromTitulo($this);
            return $calculadora->getDescontoComercialAtivo();
        }

        return 0;
    }

    public function getVencidoAttribute()
    {
        return $this->datavencimento && $this->vencimento->isPast();
    }

    public function setVencimentoAttribute($vencimento)
    {
        $this->datavencimento = $vencimento;
    }

    public function getVencimentoAttribute()
    {
        return Carbon::parse($this->datavencimento)->endOfDay();
    }

    /**
     * Juros recebidos com o pagamento
     *
     * @return number
     */
    public function getJurosEfetivadoAttribute()
    {
        return $this->juros;
    }

    /**
     * Multa recebida com pagamento
     *
     * @return number
     */
    public function getMultaEfetivadaAttribute()
    {
        return $this->multa;
    }

    /**
     * Juros calculado
     *
     * @return void
     */
    public function getJurosCorrenteAttribute()
    {
        if (!$this->vencido) {
            return 0;
        }

        $diasVencido = $this->vencimento->diffInDays() + 1;
        $percentualAtual = $diasVencido * $this->conta->empresa->mora / 100;
        return $this->esperado * $percentualAtual;
    }

    /**
     * Multa calculada
     *
     * @return void
     */
    public function getMultaCorrenteAttribute()
    {
        if (!$this->vencido) {
            return 0;
        }

        return $this->esperado * $this->conta->empresa->multa / 100;
    }

    public function getAcrecimosAttribute()
    {
        return $this->jurosCorrente + $this->multaCorrente;
    }

    public function getDescontosAttribute()
    {
        return $this->desconto + $this->bolsa + $this->descontoComercialValor;
    }

    public function getTotalAttribute()
    {
        if ($this->recebido && $this->recebidoEm) {
            return floatval($this->valorrecebido);
        }

        return $this->valor + $this->acrecimos - $this->descontos;
    }

    public function getRecebeComCartaoAttribute()
    {
        $naoRecebido = !$this->recebido;
        $pagamentoOnlineHabilitado = !!$this->pagamento_cartao_online;
        $naoBaixadoOnline = !$this->vencimento->addDays($this->conta->pagamento_online_baixa_dias)->isPast();

        return $naoRecebido && $pagamentoOnlineHabilitado && $naoBaixadoOnline;
    }

    public function getRecebeComBoletoAttribute()
    {
        // Verifica se os dias para baixa já passaram
        $naoBaixado = !$this->conta->passadoPeriodoParaBaixa($this->vencimento);

        $naoRecebido = !$this->recebido;
        $remessaEnviado = $this->remessaenviado;
        $remessaLote = $this->remessalote;
        $usaApi = $this->conta->usabancoAPI;

        return $naoRecebido &&
            (($remessaEnviado && $remessaLote > 0) || $usaApi) &&
            $naoBaixado;
    }

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'idaluno');
    }

    /**
     * Não dá pra definir o nome da relação como matrícula porque já existe um campo
     * chamado matrícula
     *
     * @return BelongsTo
     */
    public function mMatricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function itens()
    {
        return $this->hasMany(ItemTitulo::class, 'idalunos_fichafinanceira');
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class, 'idcontasbanco')->withoutGlobalScopes();
    }

    public function chequesDevolvidos()
    {
        return $this->hasMany(ChequeDevolvido::class, 'id_fichafinanceira');
    }

    public function recebimentos()
    {
        return $this->hasMany(Recebimento::class, 'idalunos_fichafinanceira');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'idfuncionario');
    }

    public function situacaoTexto()
    {
        return $this->belongsTo(Situacao::class, 'situacao', 'situacaonumero');
    }

    public function descontoComercial()
    {
        return $this->belongsTo(DescontoComercial::class, 'id_desconto_comercial');
    }

    public function asaasCobrancas()
    {
        return $this->hasMany(AsaasCobranca::class, 'id_alunos_fichafinanceira');
    }

    public $timestamps = false;
    protected $table = 'alunos_fichafinanceira';
}
