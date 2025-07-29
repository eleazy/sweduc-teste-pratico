<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Controller\Controller;
use App\Exception\RecursoNaoAutorizadoException;
use App\Model\Core\Usuario;
use App\Model\Financeiro\Cartao;
use App\Model\Financeiro\Titulo;
use App\Asaas\Models\Asaas;
use App\Model\Financeiro\AsaasCobranca;
use App\Service\Financeiro\RedePagamentosServiceProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rede\Exception\RedeException;

/**
 * Controller de contas a receber
 */
class ReceberCartaoController extends Controller
{
    public function __construct(
        protected RedePagamentosServiceProvider $pagamentoService,
        ResponseFactoryInterface $responseFactory
    ) {
        parent::__construct($responseFactory);
    }

    public function index(ServerRequestInterface $request, $args)
    {
        $this->request = $request;

        $queryParams = $request->getQueryParams();
        $tituloId = $args['id'];

        if (!empty($queryParams['metodo']) && $queryParams['metodo'] == 'boleto') {
            return $this->redirect("/boleto.pdf?id=$tituloId");
        }

        $titulo = Titulo::findOrFail($tituloId);
        $this->autorizaAcesso($titulo->aluno->id);
        $titulo->patchMatriculaId($titulo->aluno->id);

        if (!$titulo->pagamento_cartao_online) {
            throw new RecursoNaoAutorizadoException();
        }

        if ($titulo->recebido) {
            return $this->platesView('Financeiro/ContasAReceber/RecebimentoCartao/JaRecebida');
        }

        if ($titulo->conta->usabancoAPI) {
            switch ($titulo->conta->banconome) {
                case 'asaas':
                    $asaas = new Asaas();

                    // se ja foi criada a cobrança, redireciona para o link de pagamento, ou a restaura
                    $cobranca = AsaasCobranca::where('id_alunos_fichafinanceira', $titulo->id)
                        ->where('billing_type', 'CREDIT_CARD')
                        ->first();
                    if ($cobranca) {
                        if ($cobranca->data_excluida) {
                            try {
                                $response = $asaas->restaurarCobranca($cobranca);
                            } catch (\Exception $e) {
                                return $this->platesView('Financeiro/ContasAReceber/RecebimentoCartao/Erro', [
                                    'voltarUrl' => "/",
                                    'erro' => 'Erro ao gerar link de pagamento. Por favor, entre em contato.'
                                ]);
                            }

                            if ($response['id']) {
                                return $this->redirect($response['invoiceUrl']);
                            }
                        }
                        return $this->redirect($cobranca->link_cobranca);
                    }
                    // cria cobrança por cartão no Asaas
                    try {
                        $response = $asaas->criarCobrancaCartao($titulo->id);
                    } catch (\Exception $e) {
                        return $this->platesView('Financeiro/ContasAReceber/RecebimentoCartao/Erro', [
                            'voltarUrl' => "/",
                            'erro' => 'Erro ao gerar link de pagamento. Por favor, entre em contato.'
                        ]);
                    }

                    if ($response['id']) {
                        return $this->redirect($response['invoiceUrl']);
                    }
                    break;
                default:
                    break;
            }
        }

        return $this->platesView('Financeiro/ContasAReceber/RecebimentoCartao/Pagamento', compact(
            'titulo'
        ));
    }

    public function store(ServerRequestInterface $request, $args)
    {
        $this->request = $request;

        $tituloId = $args['id'];
        $titulo = Titulo::findOrFail($tituloId);

        if (!$titulo->pagamento_cartao_online) {
            throw new RecursoNaoAutorizadoException();
        }

        $this->autorizaAcesso($titulo->aluno->id);

        if ($titulo->recebido) {
            return $this->redirect($request->getRequestTarget());
        }

        $input = $request->getParsedBody();
        $cartaoNome = $input['cartao-nome'];
        $cartaoNumero = filter_var($input['cartao-numero'], FILTER_SANITIZE_NUMBER_INT);
        $cartaoExpiracao = "20{$input['cartao-expiracao-ano']}-{$input['cartao-expiracao-mes']}";
        $cartaoCodigo = $input['cartao-codigo'];
        $cartaoTipo = ($input['cartao-tipo'] ?? Cartao::CREDITO);
        $parcelas = (int) filter_var($input['cartao-parcelas'], FILTER_SANITIZE_NUMBER_INT) ?: 1;

        try {
            $cartao = new Cartao(
                $cartaoNome,
                $cartaoNumero,
                $cartaoExpiracao,
                $cartaoCodigo,
                $cartaoTipo === Cartao::DEBITO ? Cartao::DEBITO : Cartao::CREDITO
            );

            // Valida bandeiras permitidas
            // $bandeira = $cartao->getBandeira();
            // if (!in_array($bandeira, [Cartao::MASTERCARD, Cartao::VISA])) {
            //     return $this->redirect($request->getRequestTarget() . "/erro?code=110");
            // }

            $pagamento = $this->pagamentoService;
            $resultado = $pagamento->realizarPagamento($titulo, $cartao, $parcelas);

            if (RedePagamentosServiceProvider::PAGAMENTO_REALIZADO == $resultado) {
                return $this->redirect($request->getRequestTarget() . '/sucesso');
            }

            return $this->redirect($request->getRequestTarget() . "/erro?code=$resultado");
        } catch (RedeException) {
            return $this->redirect($request->getRequestTarget() . "/erro");
        } catch (\Throwable $th) {
            return $this->redirect($request->getRequestTarget() . "/erro?msg={$th->getMessage()}");
        }
    }

    public function sucesso(ServerRequestInterface $request)
    {
        return $this->platesView('Financeiro/ContasAReceber/RecebimentoCartao/Sucesso');
    }

    public function erro(ServerRequestInterface $request, $args)
    {
        $tituloId = $args['id'];
        $errorCode = $request->getQueryParams()['code'] ?? -1;

        $erros = [
            1 => 'expirationYear: Invalid parameter size',
            2 => 'expirationYear: Invalid parameter format',
            3 => 'expirationYear: Required parameter missing',
            4 => 'cavv: Invalid parameter size',
            5 => 'cavv: Invalid parameter format',
            6 => 'postalCode: Invalid parameter size',
            7 => 'postalCode: Invalid parameter format',
            8 => 'postalCode: Required parameter missing',
            9 => 'complement: Invalid parameter size',
            10 => 'complement: Invalid parameter format',
            11 => 'departureTax: Invalid parameter format',
            12 => 'documentNumber: Invalid parameter size',
            13 => 'documentNumber: Invalid parameter format',
            14 => 'documentNumber: Required parameter missing',
            15 => 'securityCode: Invalid parameter size',
            16 => 'securityCode: Invalid parameter format',
            17 => 'distributorAffiliation: Invalid parameter size',
            18 => 'distributorAffiliation: Invalid parameter format',
            19 => 'xid: Invalid parameter size',
            20 => 'eci: Invalid parameter format',
            21 => 'xid: Required parameter for Visa card is missing',
            22 => 'street: Required parameter missing',
            23 => 'street: Invalid parameter format',
            24 => 'affiliation: Invalid parameter size',
            25 => 'affiliation: Invalid parameter format',
            26 => 'affiliation: Required parameter missing',
            27 => 'Parameter cavv or eci missing',
            28 => 'code: Invalid parameter size',
            29 => 'code: Invalid parameter format',
            30 => 'code: Required parameter missing',
            31 => 'softdescriptor: Invalid parameter size',
            32 => 'softdescriptor: Invalid parameter format',
            33 => 'expirationMonth: Invalid parameter format',
            34 => 'code: Invalid parameter format',
            35 => 'expirationMonth: Required parameter missing',
            36 => 'cardNumber: Invalid parameter size',
            37 => 'cardNumber: Invalid parameter format',
            38 => 'cardNumber: Required parameter missing',
            39 => 'reference: Invalid parameter size',
            40 => 'reference: Invalid parameter format',
            41 => 'reference: Required parameter missing',
            42 => "O pagamento desse documento parece já ter sido realizado.",
            43 => 'number: Invalid parameter size',
            44 => 'number: Invalid parameter format',
            45 => 'number: Required parameter missing',
            46 => 'installments: Not correspond to authorization transaction',
            47 => 'origin: Invalid parameter format',
            49 => 'The value of the transaction exceeds the authorized',
            50 => 'installments: Invalid parameter format',
            51 => 'Product or service disabled for this merchant. Contact Rede',
            53 => 'Transaction not allowed for the issuer. Contact Rede.',
            54 => 'installments: Parameter not allowed for this transaction',
            55 => 'cardHolderName: Invalid parameter size',
            56 => 'Error in reported data. Try again.',
            57 => 'affiliation: Invalid merchant',
            58 => 'Não autorizado. Contact issuer.',
            59 => 'cardHolderName: Invalid parameter format',
            60 => 'street: Invalid parameter size',
            61 => 'subscription: Invalid parameter format',
            63 => 'softdescriptor: Not enabled for this merchant',
            64 => 'Transaction not processed. Try again',
            65 => 'token: Invalid token',
            66 => 'departureTax: Invalid parameter size',
            67 => 'departureTax: Invalid parameter format',
            68 => 'departureTax: Required parameter missing',
            69 => 'Transaction not allowed for this product or service.',
            70 => 'amount: Invalid parameter size',
            71 => 'amount: Invalid parameter format',
            72 => 'Contact issuer.',
            73 => 'amount: Required parameter missing',
            74 => 'Communication failure. Try again',
            75 => 'departureTax: Parameter should not be sent for this type of transaction',
            76 => 'kind: Invalid parameter format',
            78 => 'Transaction does not exist',
            79 => 'Expired card. Transaction cannot be resubmitted. Contact issuer.',
            80 => 'Não autorizado. Contact issuer. (Insufficient funds)',
            82 => 'Unauthorized transaction for debit card.',
            83 => 'Não autorizado. Contact issuer.',
            84 => 'Não autorizado. Transaction cannot be resubmitted. Contact issuer.',
            85 => 'complement: Invalid parameter size',
            86 => 'Expired card',
            87 => 'At least one of the following fields must be filled: tid or reference',
            88 => 'Merchant not approved. Regulate your website and contact the Rede to return to transact.',
            89 => 'token: Invalid token',
            97 => 'tid: Invalid parameter size',
            98 => 'tid: Invalid parameter format',
            101 => 'Não autorizado. Problems on the card, contact the issuer.',
            102 => 'Não autorizado. Check the situation of the store with the issuer.',
            103 => 'Não autorizado. Please try again.',
            104 => 'Não autorizado. Please try again.',
            105 => 'Não autorizado. Restricted card.',
            106 => 'Error in issuer processing. Please try again.',
            107 => 'Não autorizado. Please try again.',
            108 => 'Não autorizado. Value not allowed for this type of card.',
            109 => 'Não autorizado. Nonexistent card.',
            110 => 'Não autorizado. Tipo de transação não permitida para este cartão.',
            111 => 'Não autorizado. Saldo insuficiente.',
            112 => 'Não autorizado. Data de expiração vencida.',
            113 => 'Não autorizado. Identified moderate risk by the issuer.',
            114 => 'Não autorizado. The card does not belong to the payment network.',
            115 => 'Não autorizado. Exceeded the limit of transactions allowed in the period.',
            116 => 'Não autorizado. Please contact the Card Issuer.',
            117 => 'Transaction not found.',
            118 => 'Não autorizado. Card locked.',
            119 => 'Não autorizado. Invalid security code',
            121 => 'Error processing. Please try again.',
            122 => 'Transaction previously sent.',
            123 => 'Não autorizado. Bearer requested the end of the recurrences in the issuer.',
            124 => 'Não autorizado. Contact Rede',
            132 => 'DirectoryServerTransactionId: Invalid parameter size.',
            133 => 'ThreedIndicator: Invalid parameter value.',
            150 => 'Timeout. Try again',
            151 => 'installments: Greater than allowed',
            153 => 'documentNumber: Invalid number',
            154 => 'embedded: Invalid parameter format',
            155 => 'eci: Required parameter missing',
            156 => 'eci: Invalid parameter size',
            157 => 'cavv: Required parameter missing',
            158 => 'capture: Type not allowed for this transaction',
            159 => 'userAgent: Invalid parameter size',
            160 => 'urls: Required parameter missing (kind)',
            161 => 'urls: Invalid parameter format',
            167 => 'Invalid request JSON',
            169 => 'Invalid Content-Type',
            171 => 'Operation not allowed for this transaction',
            173 => 'Authorization expired',
            176 => 'urls: Required parameter missing (url)',
            204 => 'Cardholder not registered in the issuer\'s authentication program.',
            899 => 'Unsuccessful. Please contact Rede.',
            1018 => 'MCC Invalid Size.',
            1019 => 'MCC Parameter Required.',
            1020 => 'MCC Invalid Format.',
            1021 => 'PaymentFacilitatorID Invalid Size.',
            1023 => 'PaymentFacilitatorID Invalid Format.',
            1030 => 'CitySubMerchant Invalid Size.',
            1034 => 'CountrySubMerchant Invalid Size.',
        ];

        if (!empty($request->getQueryParams()['code'])) {
            $errorMsg = $erros[$errorCode];
            $erro = $errorMsg ? "$errorCode - $errorMsg" : $errorCode;
        } else {
            $erro = $request->getQueryParams()['msg'] ?? '';
        }

        $voltarUrl = "/financeiro/contasareceber/$tituloId/cartao";

        return $this->platesView(
            'Financeiro/ContasAReceber/RecebimentoCartao/Erro',
            compact('voltarUrl', 'erro')
        );
    }

    private function autorizaAcesso($alunoId)
    {
        $usuario = $this->request->getAttribute(Usuario::class);

        if ($usuario->tipo === Usuario::TIPO_FUNCIONARIO) {
            //
        } elseif ($usuario->tipo === Usuario::TIPO_RESPONSAVEL) {
            try {
                $usuario->alunos()->findOrFail($alunoId);
            } catch (ModelNotFoundException) {
                throw new RecursoNaoAutorizadoException();
            }
        } else {
            throw new RecursoNaoAutorizadoException();
        }
    }
}
