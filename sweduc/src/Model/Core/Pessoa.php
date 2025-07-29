<?php

declare(strict_types=1);

namespace App\Model\Core;

use App\Academico\Model\Aluno;
use App\Core\Factory\PessoaFactory;
use App\Framework\Factory;
use App\Framework\Model;
use App\Service\FilesystemService;
use League\Flysystem\UnableToDeleteFile;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use App\Model\Financeiro\AsaasCustomer;
use Illuminate\Database\Capsule\Manager as DB;

class Pessoa extends Model
{
    protected $with = ['emails'];
    protected $guarded = [];

    protected $attributes = [
        'idpaisnascimento' => 0,
        'idestadonascimento' => 0,
        'idcidadenascimento' => 0,
        'idpais' => 0,
        'idestado' => 0,
        'idcidade' => 0,
        'bairro' => 0,
        'idsexo' => 0,
        'idestadocivil' => 0,
        'cep' => '',
        'logradouro' => '',
        'numero' => '',
        'complemento' => '',
        'rg' => 0,
        'orgaoexp' => 0,
        'cpf' => 0,
        'profissao' => '',
        'nome_original' => '',
        'raca' => ''
    ];


    public const RACA = [
        1  => 'Branca',
        2 => 'Preta',
        3 => 'Amarela',
        4 => 'Parda',
    ];

    public function emails()
    {
        return $this->hasMany(Email::class, 'idpessoa');
    }

    public function telefones()
    {
        return $this->hasMany(Telefone::class, 'idpessoa');
    }

    public function celulares()
    {
        return $this->hasMany(Telefone::class, 'idpessoa')->celular();
    }

    public function paisNascimento()
    {
        return $this->belongsTo(Pais::class, 'idpaisnascimento');
    }

    public function estadoNascimento()
    {
        return $this->belongsTo(Estado::class, 'idestadonascimento');
    }

    public function cidadeNascimento()
    {
        return $this->belongsTo(Cidade::class, 'idcidadenascimento');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'idestado');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'idcidade');
    }

    public function estadoCivil()
    {
        return $this->belongsTo(EstadoCivil::class, 'idestadocivil');
    }

    public function sexo()
    {
        return $this->belongsTo(Sexo::class, 'idsexo');
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'idpessoa');
    }

    public function aluno()
    {
        return $this->hasOne(Aluno::class, 'idpessoa');
    }

    public function setPrimaryEmailAttribute($value)
    {
        $this->emails()->update(['primario' => null]);
        $this->emails()->updateOrCreate(
            ['email' => $value],
            ['primario' => 1]
        );
    }

    public function setPrimaryTelefoneAttribute($value)
    {
        $this->telefones()->updateOrCreate(
            ['telefone' => $value],
        );
    }

    public function getNascimentoAttribute()
    {
        return $this->datanascimento;
    }

    public function getEnderecoAttribute()
    {
        $endereco = [];

        !empty($this->logradouro) && $endereco[] = $this->logradouro;
        !empty($this->numero) && $endereco[] = "Nº {$this->numero}";
        !empty($this->complemento) && $endereco[] = $this->complemento;
        !empty($this->bairro) && $endereco[] = $this->bairro;

        return join(' ', $endereco);
    }

    public static function getRaca(int $numeroDaRaca)
    {
        return self::RACA[$numeroDaRaca];
    }

    public static function getAllRacas(): array
    {
        return self::RACA;
    }

    public function email()
    {
        return $this->hasOne(Email::class, 'idpessoa')
            ->where('primario', 1)
            ->where('email', '<>', '');
    }

    public function funcionario()
    {
        return $this->hasOne(Funcionario::class, 'idpessoa');
    }

    public function setNomeOriginal($value)
    {
        $this->telefones()->updateOrCreate(
            ['nome_original' => $value],
        );
    }

    public function getNomeOriginal()
    {
        return $this->nome_original;
    }

    public function getFotoUrlAttribute()
    {
        return '/api/v1/img/perfil/' . $this->foto;
    }

    public function getFotoInfo()
    {
        if (!$this->foto) {
            return null;
        }

        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        $fs = FilesystemService::cloud();
        $fotoPath = '/api/v1/img/perfil/' . $this->foto;

        try {
            $fileMetadata = $fs->mimetype($fotoPath);
            $extension = $this->$mimeMap[$fileMetadata] ?? null;

            return ['extention' => $extension ?? 'jpg', 'contentType' => $fileMetadata];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Envia imagem de perfil para o cloud object storage do FilesystemService
     * e salva modelo com uuid da localização da foto
     *
     * @return void
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function uploadImg(UploadedFileInterface $uploadedFile, ?LoggerInterface $logger = null)
    {
        $fs = FilesystemService::cloud();
        $fotoId = Uuid::uuid4();

        if ($this->foto) {
            try {
                $fs->delete('img/perfil/' . $this->foto);
                $logger->info('Exclusão de foto antiga de perfil: ' . $this->foto, [
                    'pessoaId' => $this->id
                ]);
            } catch (UnableToDeleteFile $exception) {
                if ($logger) {
                    $logger->info(
                        'Erro ao deletar foto de perfil: ' . $exception->getMessage(),
                        compact('exception')
                    );
                }
            }
        }

        $fs->writeStream('img/perfil/' . $fotoId, $uploadedFile->getStream()->detach());

        $this->foto = $fotoId;
        $this->save();
    }

    /**
     * Verifica se a pessoa possui cobranças ativas no Asaas
     *
     * @return boolean
     *
     */
    public function temCobrancasAsaas(): bool
    {
        $asaasCustomer = AsaasCustomer::where('idpessoa', $this->id)->first();

        if (!$asaasCustomer) {
            return false;
        }

        $existeCobrancaAsaasEmAberto = DB::select(
            "SELECT af.id
            FROM asaas_cobrancas ac
            JOIN alunos_fichafinanceira af ON ac.id_alunos_fichafinanceira = af.id
            WHERE ac.idpessoa = :idpessoa AND af.status = 0",
            ['idpessoa' => $asaasCustomer->idpessoa]
        );

        return !empty($existeCobrancaAsaasEmAberto);
    }

    protected static function newFactory(): Factory
    {
        return PessoaFactory::new();
    }

    public $timestamps = false;
}
