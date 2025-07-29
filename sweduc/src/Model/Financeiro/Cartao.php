<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Carbon\Carbon;
use Freelancehunt\Validators\CreditCard;

final class Cartao
{
    public const CREDITO = 'credito';
    public const DEBITO = 'debito';

    public const MASTERCARD = CreditCard::TYPE_MASTERCARD;
    public const VISA = CreditCard::TYPE_VISA;

    private $nome;
    private $numero;
    private ?Carbon $expiracao = null;
    private $codigo;
    private $tipo;
    private $bandeira;

    public function __construct($nome, $numero, $expiracao, $codigo, $tipo)
    {
        $validate = CreditCard::validCreditCard($numero);
        $this->bandeira = $validate['type'];
        $this->setNome($nome);
        $this->setNumero($numero);
        $this->setExpiracao($expiracao);
        $this->setCodigo($codigo);
        $this->setTipo($tipo);
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getNumero()
    {
        return $this->numero;
    }

    public function getExpiracao()
    {
        return $this->expiracao;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getExpiracaoMes()
    {
        return $this->expiracao->month;
    }

    public function getExpiracaoAno()
    {
        return $this->expiracao->year;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function getBandeira()
    {
        return strtolower($this->bandeira);
    }

    public function isCredito()
    {
        return $this->getTipo() == Cartao::CREDITO;
    }

    public function isDebito()
    {
        return $this->getTipo() == Cartao::DEBITO;
    }

    public function setNome($value)
    {
        $this->nome = $value;
    }

    public function setNumero($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT)) {
            $this->numero = $value;
        } else {
            throw new \Exception("O número do cartão é inválido", 1);
        }
    }

    public function setExpiracao($value)
    {
        if ($value instanceof Carbon) {
            $this->expiracao = $value;
        } else {
            $this->expiracao = Carbon::parse($value);
        }
    }

    public function setCodigo($value)
    {
        $this->codigo = $value;
    }

    public function setTipo($value)
    {
        $this->tipo = $value;
    }
}
