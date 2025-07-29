<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Controller\Controller;
use App\Exception\RecursoNaoAutorizadoException;
use App\Model\Core\Usuario;
use App\Model\Financeiro\AsaasCobranca;
use App\Asaas\Models\Asaas;
use App\Model\Financeiro\Titulo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mpdf\Mpdf;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller de contas a receber
 */
class BoletosController extends Controller
{
    public function index(ServerRequestInterface $request)
    {
        $usuario = $request->getAttribute(Usuario::class);
        $tituloId = $request->getQueryParams()['id'];
        $titulo = Titulo::findOrFail($tituloId);
        $this->autorizaAcesso($usuario, $titulo->aluno->id);
        $titulo->patchMatriculaId($titulo->aluno->id);

        ob_start();
        $idl = $tituloId;
        set_include_path(__DIR__ . '/../../../public/lib/boletos/');
        require 'fazboletos.php';
        $response = ob_get_clean();

        return $this->plainTextResponse($response);
    }

    public function pdf(ServerRequestInterface $request)
    {
        $usuario = $request->getAttribute(Usuario::class);
        $tituloId = $request->getQueryParams()['id'];
        $titulo = Titulo::findOrFail($tituloId);
        $this->autorizaAcesso($usuario, $titulo->aluno->id);
        $titulo->patchMatriculaId($titulo->aluno->id);

        if ($titulo->conta->usabancoAPI) {
            switch ($titulo->conta->banconome) {
                case 'asaas':
                    $asaas = new Asaas();
                    $cobranca = AsaasCobranca::where('id_alunos_fichafinanceira', $tituloId)
                        ->where('billing_type', 'BOLETO')
                        ->first();

                    if ($cobranca->data_excluida) {
                        try {
                            $response = $asaas->restaurarCobranca($cobranca);
                        } catch (\Exception $e) {
                            return $this->plainTextResponse('Erro ao gerar boleto. Por favor entre em contato.');
                        }
                    }

                    $pdfContent = file_get_contents($cobranca->link_boleto);
                    return $this->plainTextResponse($pdfContent)
                        ->withHeader('Content-Disposition', 'inline; filename="boleto.pdf"')
                        ->withHeader('Content-type', 'application/pdf')
                        ->withHeader('Cache-Control', 'public');

                    break;
                default:
                    break;
            }

            return $this->plainTextResponse('Erro ao gerar boleto. Por favor entre em contato.');
        }

        /**
         * Captura buffer, coloca ele numa variável e descarta
         */
        ob_start();
        $idl = $tituloId;
        $GLOBALS['pdf'] = true;
        set_include_path(__DIR__ . '/../../../public/lib/boletos/');
        require 'fazboletos.php';
        header_remove();
        $response = ob_get_clean();
        $cleanResponse = mb_convert_encoding($response, 'UTF-8', 'UTF-8');

        $mpdf = new Mpdf(['tempDir' => '/tmp/boletos']);
        $mpdf->WriteHTML($cleanResponse);
        return $this->plainTextResponse($mpdf->Output('boleto.pdf', 'S'))
            ->withHeader('Content-Disposition', 'inline;filename="boleto.pdf"')
            ->withHeader('Content-type', 'application/pdf')
            ->withHeader('Cache-Control', 'public');
    }

    public function copiaCodigo(ServerRequestInterface $request)
    {
        $usuario = $request->getAttribute(Usuario::class);
        $tituloId = $request->getQueryParams()['id'];
        $titulo = Titulo::findOrFail($tituloId);
        $this->autorizaAcesso($usuario, $titulo->aluno->id);
        $titulo->patchMatriculaId($titulo->aluno->id);

        if ($titulo->conta->usabancoAPI) {
            $tipo = $request->getQueryParams()['tipo'];
            switch ($titulo->conta->banconome) {
                case 'asaas':
                    $asaas = new Asaas();
                    $cobranca = AsaasCobranca::where('id_alunos_fichafinanceira', $titulo->id)
                        ->where('billing_type', 'BOLETO')
                        ->first();
                    if ($cobranca->data_excluida) {
                        try {
                            $response = $asaas->restaurarCobranca($cobranca);
                        } catch (\Exception $e) {
                            return $this->plainTextResponse('Erro ao gerar boleto. Por favor entre em contato.');
                        }

                        if (!$response['id']) {
                            return $this->plainTextResponse('error');
                        }
                    }

                    $codigos = $asaas->linhaDigitavelBoletoOuQrCode($cobranca->id_asaas);
                    if ($codigos) {
                        $codigo = $codigos[$tipo];
                        return $this->plainTextResponse($codigo);
                    }
                    break;
                default:
                    break;
            }

            return $this->plainTextResponse('error');
        }

        ob_start();
        $idl = $tituloId;
        set_include_path(__DIR__ . '/../../../public/lib/boletos/');
        require 'fazboletos.php';
        header_remove();
        $response = ob_get_clean();
        $cleanResponse = mb_convert_encoding($response, 'UTF-8', 'UTF-8');

        $startPos = strpos($cleanResponse, '<span class="campotitulo">');
        if ($startPos !== false) {
            $startPos += strlen('<span class="campotitulo">');
            $endPos = strpos($cleanResponse, '</span>', $startPos);
            if ($endPos !== false) {
                $length = $endPos - $startPos;
                $extracted = str_replace(array('.', ' '), '', trim(substr($cleanResponse, $startPos, $length)));
                return $this->plainTextResponse($extracted);
            }
        }
        return $this->plainTextResponse('error');
    }

    private function autorizaAcesso(Usuario $usuario, int $alunoId)
    {
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
