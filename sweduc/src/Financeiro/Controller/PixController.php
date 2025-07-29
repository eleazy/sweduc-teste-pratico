<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Academico\Model\Aluno;
use App\Controller\Controller;
use App\Financeiro\Pix\GeradorPixSantander;
use App\Model\Core\Funcionario;
use App\Model\Core\Pessoa;
use App\Model\Core\Usuario;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Model\Financeiro\Titulo;
use App\Model\Financeiro\Conta;
use App\Model\Financeiro\FormaDePagamento;
use App\Model\Financeiro\Recebimento;
use App\Model\Financeiro\Pix;
use Carbon\Carbon;
use Laminas\Diactoros\Response\JsonResponse;
use Throwable;

/**
 * Controller do Pix
 */
class PixController extends Controller
{
    /**
     * Retorna formulário de busca
     */
    public function buscar(): ResponseInterface
    {
        return $this->platesView('Financeiro/Pix/Buscar');
    }

    /**
     * Retorna listagem
     */
    public function listar(ServerRequestInterface $request): ResponseInterface
    {
        $usuario = $request->getAttribute(Usuario::class);

        $GeradorPixSantander = new GeradorPixSantander();

        $input = $request->getParsedBody();

        $periodoDe = $input['periodode'];
        $periodoAte = $input['periodoate'];
        $contaBusca = $input['contaBusca'];

        if ($contaBusca != 0) {
            $nomeB = Conta::where('id', $contaBusca)->first();
            $banco = $nomeB->banconome;

            $nomeBancoLimpo = [
                'Itaú' => 'itau',
                'Santander/Banespa' => 'santander',
            ];

            $banconome = $nomeBancoLimpo[$banco];
        }

        if ($contaBusca == 0) {
            $banconome = 'todos';
        }

        $pagamentos = $GeradorPixSantander->consultarCobrancas($banconome, $periodoDe, $periodoAte);

        if ($pagamentos != 'sem-resultados') {
            $pagamentosPorTxid = array_column($pagamentos, null, 'txid');
            $pagamentosPix = Pix::whereIn('txid', array_keys($pagamentosPorTxid))
                ->with('titulo.aluno.pessoa')
                ->get()
                ->map(function (Pix $pix) use ($pagamentosPorTxid, $GeradorPixSantander) {
                    $titulo = $pix->titulo;
                    $aluno = $titulo->aluno;
                    $alunoPessoa = $aluno->pessoa;
                    $pagamento = $pagamentosPorTxid[$pix->txid];
                    return [
                        'devedorNome' => ucwords(strtolower($GeradorPixSantander->limpaCaractereEspecial($pix->devedorNome))),
                        'nome' => ucwords(strtolower($GeradorPixSantander->limpaCaractereEspecial($alunoPessoa->nome))),
                        'titulo' => $titulo->titulo,
                        'valor' => number_format((float) $pagamento->valor, 2, ",", "."),
                        'horario' => Carbon::parse($pagamento->horario)->format('d/m/Y H:i:s'),
                        'datavencimento' => Carbon::parse($titulo->datavencimento)->format('d/m/Y'),
                        'infoPagador' => ucwords(strtolower($pagamento->infoPagador)),
                    ];
                });

            return $this->platesView('Financeiro/Pix/Listar', [
                'usuario'    => $usuario,
                'pagamentos' => $pagamentosPix,
                'periodoDe'  => $periodoDe,
                'periodoAte' => $periodoAte,
            ]);
        } else {
            return $this->platesView('Financeiro/Pix/Listar', [
                'usuario'    => $usuario,
                'pagamentos' => 'sem-resultados',
                'periodoDe'  => $periodoDe,
                'periodoAte' => $periodoAte,
            ]);
        }
    }

    /**
     * Webhook Santander Boletos
     */
    public function webhookSantander(ServerRequestInterface $request)
    {
        if ($request->getParsedBody()->status == "ACTIVE") {
            return new JsonResponse(['message' => 'Webhook received successfully'], 200);
        } elseif (empty($request->getParsedBody())) {
            return new JsonResponse(['message' => 'Webhook received successfully'], 200);
        } else {
            $boletoRecebidoTitulo = $request->getParsedBody()->clientNumber;
            $valorPago = $request->getParsedBody()->payedValue;
            $titulo = Titulo::where('titulo', $boletoRecebidoTitulo)->first();
            $pessoaAutoatendimento = Pessoa::where('nome', 'Autoatendimento')->first();
            $autoatendimento = Funcionario::where('idpessoa', $pessoaAutoatendimento->id)->first();
            $formaPagamento = FormaDePagamento::where('formapagamento', 'Boleto')->first();

            $pix = Pix::where('alunos_fichafinanceira_id', $titulo->id)->first();

            if ($titulo->situacao != 1) {
                $fichaRecebida = new Recebimento();

                try {
                    $titulo->datarecebimento = Carbon::now()->format('Y-m-d');
                    $titulo->valorrecebido = $valorPago;
                    $titulo->situacao = 1;
                    $titulo->saveOrFail();
                } catch (Throwable $e) {
                    throw $e;
                }
                try {
                    $fichaRecebida->idalunos_fichafinanceira = $titulo->id;
                    $fichaRecebida->idfuncionario = $autoatendimento->id;
                    $fichaRecebida->datarecebido = Carbon::now()->format('Y-m-d');
                    $fichaRecebida->datacompensado = Carbon::now()->format('Y-m-d');
                    $fichaRecebida->valorrecebido = $valorPago;
                    $fichaRecebida->formarecebido = $formaPagamento->id;
                    $fichaRecebida->idcontasbanco = $titulo->idcontasbanco;
                    $fichaRecebida->saveOrFail();
                } catch (Throwable $e) {
                    throw $e;
                }
            } else {
                return new JsonResponse(['message' => 'Webhook received successfully'], 200);
            }
        }
    }

    /**
     * Webhook Santander Pix
     */
    public function webhookPixSantander(ServerRequestInterface $request)
    {
        if (empty($request->getParsedBody())) {
            return new JsonResponse(['message' => 'Webhook received successfully'], 200);
        }

        $pixRecebidos = $request->getParsedBody()->pix;
        $pessoaAutoatendimento = Pessoa::where('nome', 'Autoatendimento')->first();
        $autoatendimento = Funcionario::where('idpessoa', $pessoaAutoatendimento->id)->first();
        $formaPagamento = FormaDePagamento::where('formapagamento', 'Pix')->first();

        foreach ($pixRecebidos as $pagamento) {
            $pix = Pix::where('txid', $pagamento->txid)->first();
            $titulo = Titulo::where('id', $pix->alunos_fichafinanceira_id)->first();

            if ($titulo->situacao != 1) {
                $fichaRecebida = new Recebimento();

                try {
                    $titulo->datarecebimento = Carbon::now()->format('Y-m-d');
                    $titulo->valorrecebido = $pagamento->valor;
                    $titulo->situacao = 1;

                    $titulo->saveOrFail();
                } catch (Throwable $e) {
                    throw $e;
                }

                try {
                    $fichaRecebida->idalunos_fichafinanceira = $titulo->id;
                    $fichaRecebida->idfuncionario = $autoatendimento->id;
                    $fichaRecebida->datarecebido = Carbon::now()->format('Y-m-d');
                    $fichaRecebida->datacompensado = Carbon::now()->format('Y-m-d');
                    $fichaRecebida->valorrecebido = $pagamento->valor;
                    $fichaRecebida->formarecebido = $formaPagamento->id;
                    $fichaRecebida->idcontasbanco = $titulo->idcontasbanco;

                    $fichaRecebida->saveOrFail();
                } catch (Throwable $e) {
                    throw $e;
                }
            } else {
                return new JsonResponse(['message' => 'Webhook received successfully'], 200);
            }
        }
    }

    public static function extractQrCodeEMV(string $url): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) && !str_starts_with($url, "pix.")) {
            return $url;
        }

        $content = self::fetchUrlContents($url);

        if ($content !== false) {
            $parts = explode(".", $content);
            if (count($parts) !== 3) {
                return ""; // Invalid JWT
            }
            $dados = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);

            if (isset($dados['chave'])) {
                $tamanhoLocation = strlen($url);
                $tamanhoMerchantAccountInformation = strlen('0014br.gov.bcb.pix25' . $tamanhoLocation . $url);
                $merchantName = self::limpaCaractereEspecial(substr($dados['recebedor']['nome'], 0, 25));
                $merchantCity = self::limpaCaractereEspecial($dados['recebedor']['cidade']);
                $tamanhoCampoValor = str_pad((string) strlen($dados['valor']['final']), 2, '0', STR_PAD_LEFT);

                $emvqrcodeParcial = '00020101021226' . "$tamanhoMerchantAccountInformation" . '0014br.gov.bcb.pix25' . $tamanhoLocation . $url . '52040000530398654' . $tamanhoCampoValor . $dados['valor']['final'] . '5802BR59' . strlen($merchantName) . $merchantName . '60' . strlen($merchantCity) . $merchantCity . '62070503***6304';

                $crc16 = self::gerarCRC16($emvqrcodeParcial);
                $emvqrcode = $emvqrcodeParcial . $crc16;

                self::salvaEmvqrcode($url, $emvqrcode);
                return $emvqrcode;
            }
        }
        return "";
    }

    private static function gerarCRC16($payload)
    {
        $polynomial = 0x1021;
        $crc = 0xFFFF;

        for ($i = 0; $i < strlen($payload); $i++) {
            $crc ^= ord($payload[$i]) << 8;

            for ($j = 0; $j < 8; $j++) {
                if (($crc & 0x8000) != 0) {
                    $crc = ($crc << 1) ^ $polynomial;
                } else {
                    $crc <<= 1;
                }
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    private static function limpaCaractereEspecial($str)
    {
        $apost = "'";
        $special = ['Ã', 'Ãˆ', 'Ã´', 'Ã‡', 'Ã¡', 'Ã¨', 'Ã’', 'Ã§', 'Ã‚', 'Ã‹', 'Ã²', 'Ã¢', 'Ã«', 'Ã˜', 'Ã‘', 'Ã€', 'Ã', 'Ã¸', 'Ã±', 'Ã ', 'Ã°', 'Ã•', 'Ã…', 'Ãµ', 'Ã', 'Ã¥', 'Ã', 'Ã–', 'Ã½', 'Ãƒ', 'Ã­', 'Ã¶', 'Ã£', 'ÃŽ', 'Ã„', 'Ã®', 'Ãš', 'Ã¤', 'ÃŒ', 'Ãº', 'Ã†', 'Ã¬', 'Ã›', 'Ã¦', 'Ã', 'Ã»', 'Ã¯', 'Ã™', 'Â®', 'Ã‰', 'Ã¹', 'Â©', 'Ã©', 'Ã“', 'Ãœ', 'Ãž', 'ÃŠ', 'Ã³', 'Ã¼', 'Ã¾', 'Ãª', 'Ã”', 'ÃŸ', 'â€˜', 'â€™', 'â€š', 'â€œ', 'â€', 'â€ž', 'é', 'á', 'í', 'ó', 'ú', 'ã', 'õ', 'â', 'ê', 'ô', 'û', 'É', 'Á', 'Í', 'Ó', 'Ú', 'Ã', 'Õ', 'Â', 'Ê', 'Ô', 'Û', 'à', 'À', 'ç', 'Ç', 'Ä', 'ä', 'Ö', 'ö', 'Ü', 'ü', $apost];
        $clearspc = ['a', 'e', 'o', 'c', 'a', 'e', 'o', 'c', 'a', 'e', 'o', 'a', 'e', 'o', 'n', 'a', 'd', 'o', 'n', 'a', 'o', 'o', 'a', 'o', 'y', 'a', 'i', 'o', 'y', 'a', 'i', 'o', 'a', 'i', 'a', 'i', 'u', 'a', 'i', 'u', 'a', 'i', 'u', 'a', 'i', 'u', 'i', 'u', '', 'e', 'u', 'c', 'e', 'o', 'u', 'p', 'e', 'o', 'u', 'b', 'e', 'o', 'b', '', '', '', '', '', '', 'e', 'a', 'i', 'o', 'u', 'a', 'o', 'a', 'e', 'o', 'u', 'E', 'A', 'I', 'O', 'U', 'A', 'O', 'A', 'E', 'O', 'U', 'a', 'A', 'c', 'C', 'A', 'a', 'O', 'o', 'U', 'u', ''];
        return str_replace($special, $clearspc, $str);
    }

    private static function fetchUrlContents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL issues
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"); // Fake User-Agent
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json, text/plain, */*"
        ]);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            return $content;
        }

        return false;
    }

    private static function salvaEmvqrcode($url, $emvqrcode)
    {
        $pix = Pix::where('location', $url)->first();
        if ($pix) {
            $pix->emvqrcode = $emvqrcode;
            $pix->save();
            return;
        }
    }
}
