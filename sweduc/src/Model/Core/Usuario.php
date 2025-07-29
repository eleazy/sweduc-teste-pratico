<?php

declare(strict_types=1);

namespace App\Model\Core;

use App\Academico\Model\Aluno;
use App\Academico\Model\Matricula;
use App\Academico\Model\Responsavel;
use App\Core\Factory\UsuarioFactory;
use App\Framework\Factory;
use App\Framework\Model;
use App\Usuarios\IntegradorPermissoesLegadasService;
use App\Usuarios\PermissoesService;

class Usuario extends Model
{
    public const TIPO_CONVIDADO = null;
    public const TIPO_ALUNO = 0;
    public const TIPO_RESPONSAVEL = 1;
    public const TIPO_FUNCIONARIO = 2;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'senha', 'password_hash'
    ];

    protected $fillable = [
        'provider_iss',
        'provider_sub',
        'idpessoa',
        'login',
    ];

    protected $appends = [
        'email'
    ];

    public static function fromSession(?array $session = null)
    {
        if (empty($session)) {
            $session = $_SESSION;
        }

        return Usuario::find($session['id_usuario']);
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idpessoa');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'idpessoa', 'idpessoa');
    }

    public function alunos()
    {
        return $this->belongsToMany(
            Aluno::class,
            'responsaveis',
            'idpessoa',
            'idaluno',
            'idpessoa',
            'id'
        );
    }

    public function matriculas()
    {
        return $this->belongsToMany(
            Matricula::class,
            'responsaveis',
            'idpessoa',
            'idaluno',
            'idpessoa',
            'idaluno'
        );
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'politica_grupo_id');
    }

    public function hasRole($role)
    {
        if ('aluno') {
            return !!$this->aluno;
        }

        if ('responsavel') {
            return !!$this->responsavel;
        }

        if ('funcionario') {
            return !!$this->funcionario;
        }

        if ('professor') {
            return !!$this->professor;
        }

        return false;
    }

    public function getApiKeyAttribute($value)
    {
        if (!$value) {
            $value = $this->api_key = md5(random_bytes(64));
            $this->save();
        }

        return $value;
    }

    public function getTipoAttribute($value)
    {
        return self::TIPO_FUNCIONARIO;
    }

    public function getEmailAttribute()
    {
        $cliente = $_ENV['CLIENTE'];
        return $this->login . "@$cliente.sweduc.com.br";
    }

    public function autorizado(string $permissao, int $unidade = 0)
    {
        if (!$this->funcionario) {
            return false;
        }

        $permissoes = new PermissoesService();
        $permissoesLegadas = new IntegradorPermissoesLegadasService();

        // Sistema de permissões não sincronizado
        if (!$this->politica_grupo_id) {
            $permissoes->importaPermissoesLegadas();
            $this->refresh();
        }

        $autorizadoPermissaoLegada = in_array($permissao, $permissoesLegadas->listarPermissoes($this->idpermissao));
        $autorizadoPermissao = $permissoes->verificarPermissao($this->politica_grupo_id, $permissao, $unidade);

        return $autorizadoPermissaoLegada || $autorizadoPermissao;
    }

    /**
     * Retorna array com unidades em que o funcionário é permitido a realizar certa ação
     *
     * @return array unidades permitidas
     */
    public function autorizadoEmUnidades(string $permissao)
    {
        if (!$this->funcionario) {
            return false;
        }

        $permissoes = new PermissoesService();

        // Sistema de permissões não sincronizado
        if (!$this->politica_grupo_id) {
            $permissoes->importaPermissoesLegadas();
            $this->refresh();
        }

        $unidades = $permissoes->verificarUnidades($this->politica_grupo_id, $permissao);
        $idx = array_search('0', $unidades);
        if ($idx !== false) {
            $unidades[$idx] = $this->funcionario->idunidade;
        }
        return array_unique($unidades);
    }

    /**
     * Verifica se a senha confere
     *
     * @param boolean $compararSenha senhas armazenadas em texto plano
     */
    public function verificaSenha(string $senha, bool $compararSenha = false): bool
    {
        $hash = $this->password_hash ?? '';

        // Verifica se a senha é igual ao hash do banco
        $hashConfere = password_verify($senha, $hash);
        $senhaConfere = $this->senha === $senha;

        // Se o hash não bate e não deve comparar por senha ou a senha não confere retorna falso
        if (!$hashConfere && !($compararSenha && $senhaConfere)) {
            return false;
        }

        // Atualiza hash se o algorítimo de hash usado não for o padrão
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $this->password_hash = password_hash($this->senha, PASSWORD_DEFAULT);
            $this->save();
        }

        return true;
    }

    /**
     * Remove senha do usuário e opcionalmente o hash
     * Útil para desabilitar logins ou remover senha plaintext
     *
     * @param boolean $removerHash
     */
    public function removerSenha($removerHash = false): void
    {
        $this->senha = '';
        if ($removerHash) {
            $this->password_hash = null;
        }
        $this->save();
    }

    public static function gerarSenha($tam = 6): string
    {
        $CaracteresAceitos = 'abcdxywzABCDZYWZ0123456789';
        $max = strlen($CaracteresAceitos) - 1;
        $password = null;

        for ($i = 0; $i < $tam; $i++) {
            $password .= $CaracteresAceitos[random_int(0, $max)];
        }

        return($password);
    }


    public $timestamps = false;
    protected $table = 'usuarios';
}
