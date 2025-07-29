<?php

declare(strict_types=1);

namespace App\Financeiro\Controller\Relatorios;

use App\Framework\Http\BaseController;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Ramsey\Uuid\Uuid;

class RetornoRelatorioController extends BaseController
{
    public function relatorio(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $retornoId = $queryParams['id'];

        if ($retornoId == "null") {
            // Pega o id do último retorno
            $lastRetorno = DB::table('financeiro_retornos')->orderBy('id', 'desc')->first();
            $retornoId = $lastRetorno->id;
            $nomeArquivo = $lastRetorno->nomearquivo;
        } else {
            $nomeArquivo = DB::table('financeiro_retornos')->where('id', $retornoId)->value('nomearquivo');
        }

        // Pega todos os ids dos titulos do retorno
        $retornoTitulosIds = DB::table('financeiro_retornos_titulos')
                                ->where('idfinanceiro_retornos', $retornoId)
                                ->pluck('idfichafinanceira')
                                ->toArray();

        if (!empty($retornoTitulosIds)) {
            $infoTitulos = [];

            foreach ($retornoTitulosIds as $id) {
                $infoTituloQ = DB::select("
                    SELECT
                        p.nome AS aluno,
                        af.titulo,
                        (af.valor - af.bolsa + cb.tarifaboleto) AS valor,
                        af.datavencimento,
                        af.datarecebimento,
                        (af.desconto - frt.tarifaValor) AS descontos,
                        frt.tarifaValor,
                        af.juros,
                        af.valorrecebido,
                        CASE
                            WHEN frt.resultado = 'SITUACAO_OK' THEN 'SIM'
                            ELSE frt.resultado
                        END AS recebido
                    FROM
                        alunos_fichafinanceira af
                    JOIN
                        financeiro_retornos_titulos frt ON af.id = frt.idfichafinanceira
                    JOIN
                        alunos a ON af.idaluno = a.id
                    JOIN
                        pessoas p ON a.idpessoa = p.id
                    JOIN
                        contasbanco cb ON af.idcontasbanco = cb.id
                    WHERE
                        af.id = :id
                    AND
                        frt.idfinanceiro_retornos = :retornoId
                ", ['id' => $id, 'retornoId' => $retornoId]);

                $infoTitulos[] = $infoTituloQ[0];
            }

            $spreadsheet = new Spreadsheet();
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', '#');
            $sheet->setCellValue('B1', 'Aluno');
            $sheet->setCellValue('C1', 'Título');
            $sheet->setCellValue('D1', 'Valor');
            $sheet->setCellValue('E1', 'Vencimento');
            $sheet->setCellValue('F1', 'Recebimento');
            $sheet->setCellValue('G1', 'Descontos');
            $sheet->setCellValue('H1', 'Tarifa');
            $sheet->setCellValue('I1', 'Juros');
            $sheet->setCellValue('J1', 'Valor Recebido');
            $sheet->setCellValue('K1', 'Recebido');

            $sheet->getStyle('A1:K1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFA500');
            $sheet->getStyle('A1:K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:K1')->getFont()->setBold(true);

            $sheet->getColumnDimension('A')->setWidth(10);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);

            $row = 2;
            foreach ($infoTitulos as $info) {
                $recebidoFormatado = str_replace('_', ' ', $info->recebido);

                $sheet->setCellValueExplicit('A' . $row, $row - 1, Datatype::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('B' . $row, $info->aluno, Datatype::TYPE_STRING);
                $sheet->setCellValue('C' . $row, $info->titulo);
                $sheet->setCellValue('D' . $row, $info->valor);
                $sheet->setCellValue('E' . $row, $info->datavencimento);
                $sheet->setCellValue('F' . $row, $info->datarecebimento);
                $sheet->setCellValue('G' . $row, $info->descontos);
                $sheet->setCellValue('H' . $row, $info->tarifaValor);
                $sheet->setCellValue('I' . $row, $info->juros);
                $sheet->setCellValue('J' . $row, $info->valorrecebido);
                $sheet->setCellValueExplicit('K' . $row, $recebidoFormatado, Datatype::TYPE_STRING);

                $row++;
            }

            $uuid = Uuid::uuid4();
            $filePath = sys_get_temp_dir() . "/{$uuid}.xlsx";
            $xlsxWriter = new Xlsx($spreadsheet);
            $xlsxWriter->save($filePath);

            $response = $this->createResponse(201);
            $response = $response
                ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->withHeader('Content-Disposition', 'attachment;filename="relatorio_retorno_' . $uuid . '.xlsx"')
                ->withHeader('Cache-Control', 'max-age=0')
                ->withHeader('X-Nome-Arquivo', $nomeArquivo);

            $response->getBody()->write(file_get_contents($filePath));

            unlink($filePath);

            return $response;
        } else {
            $response = $this->createResponse(404);
            $response->getBody()->write('Nenhum título foi recebido nesse arquivo retorno.');
            return $response;
        }
    }
}
