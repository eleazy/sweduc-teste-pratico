<?php

declare(strict_types=1);

namespace App\Academico\Model;

use App\Exception\RecursoNaoAutorizadoException;
use App\Framework\Model;
use App\Model\Core\AutorizaUsuarioInterface;
use App\Model\Core\Empresa;
use App\Model\Core\Funcionario;
use App\Model\Core\Unidade;
use App\Model\Core\Usuario;
use App\Model\Config\DocumentoRematricula;

class Matricula extends Model implements AutorizaUsuarioInterface
{
    public const STATUS_ATIVO = 1;
    public const STATUS_TRANCADO = 2;
    public const STATUS_CANCELADO = 3;
    public const STATUS_TURMA_ENCERRADA = 4;
    public const STATUS_TRANSFERIDO = 6;
    public const STATUS_ABANDONADO = 7;
    public const STATUS_CONCLUIDO = 8;

    public $timestamps = false;
    protected $fillable = [
        'anoletivomatricula',
        'bolsa_motivo_id',
        'bolsa_motivo',
        'bolsa',
        'bolsapercentual',
        'datamatricula',
        'datareajuste',
        'datastatus',
        'escoladestino',
        'idaluno',
        'idempresa',
        'idfuncionario',
        'idplanohorario',
        'idunidade',
        'motivoSituacao',
        'nummatricula',
        'obsSituacao',
        'presencial',
        'qtdparcelas',
        'reajustado',
        'recebereajuste',
        'seguroescolar',
        'status',
        'turmamatricula',
        'valorAnuidade',
    ];

    public function autoriza(Usuario $usuario): void
    {
        if ($usuario->tipo === Usuario::TIPO_FUNCIONARIO) {
            return;
        }

        if (
            $usuario->tipo === Usuario::TIPO_RESPONSAVEL &&
            $usuario->matriculas->contains($this)
        ) {
            return;
        }

        if (
            $usuario->tipo === Usuario::TIPO_ALUNO &&
            Aluno::where('id', $this->idaluno)
            ->where('idpessoa', $usuario->idpessoa)
            ->first()
        ) {
            return;
        }

        throw new RecursoNaoAutorizadoException();
    }

    public static function scopeAtivo($query)
    {
        return $query->where('status', self::STATUS_ATIVO);
    }

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'idaluno');
    }

    public function statusTexto()
    {
        return $this->belongsTo(MatriculaStatus::class, 'status');
    }

    public function periodoLetivo()
    {
        return $this->belongsTo(PeriodoLetivo::class, 'anoletivomatricula');
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class, 'turmamatricula');
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'idunidade');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }

    public function planoHorario()
    {
        return $this->belongsTo(PlanoHorario::class, 'idplanohorario');
    }

    /**
     * Usar AlunoOcorrencia::patchMatriculaId($alunoId) antes de acessar o relacionamento
     */
    public function ocorrencias()
    {
        return $this->hasMany(AlunoOcorrencia::class, 'matricula_id');
    }

    public function mediasCalculadas()
    {
        return $this->hasMany(MediaCalculada::class, 'matricula_id');
    }

    public function statusExtenso()
    {
        return $this->belongsTo(AlunoStatus::class, 'status');
    }

    public function registradoPor()
    {
        return $this->belongsTo(Funcionario::class, 'idfuncionario');
    }

    public function documentos()
    {
        return $this->belongsToMany(
            DocumentoRematricula::class,
            'alunos_documentos_matriculas'
        )
            ->withPivot(['id', 'documento_file_id', 'aprovado_em', 'rejeitado_em'])
            ->whereNull('substituido_em');
    }

    protected $table = 'alunos_matriculas';
}
