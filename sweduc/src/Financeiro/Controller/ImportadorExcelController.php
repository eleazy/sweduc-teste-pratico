<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Academico\Model\Matricula;
use App\Controller\Controller;
use App\Model\Core\Funcionario;
use App\Model\Core\Pessoa;
use App\Model\Financeiro\Conta;
use App\Model\Financeiro\EventoFinanceiro;
use Psr\Http\Message\ServerRequestInterface;
use App\Service\FilesystemService;
use Psr\Http\Message\StreamFactoryInterface;
use Illuminate\Database\Capsule\Manager as DB;
use App\Model\Financeiro\Titulo;
use App\Model\Financeiro\FichaItem;
use App\Model\Financeiro\Recebimento;
use Carbon\Carbon;
use Throwable;

/**
 * Controller de contas a pagar
 */
class ImportadorExcelController extends Controller
{
    protected $auth;
    protected StreamFactoryInterface $streamFactory;

    public function index()
    {
        return $this->platesView('Financeiro/Importador/Index');
    }

    public function store(ServerRequestInterface $request, array $params)
    {
        $arquivos = end($request->getUploadedFiles())->getStream()->getMetadata('uri');
        $fs = FilesystemService::cloud();
        $nomeArquivo = "financeiro/" . substr($arquivos, 8) . ".xlsx";

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($arquivos);
        $worksheet = $spreadsheet->getActiveSheet();
        $thead = [];
        $tbody = [];
        $contador = 0;

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            if ($contador == 0) {
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue()) {
                        $thead[] = $cell->getValue();
                    }
                }
            } else {
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue()) {
                        $tbody[] = $this->limpaCelula($cell->getValue());
                    }
                }
            }

            $contador++;
        }

        $rawBody = $tbody;
        $tbody = array_chunk($tbody, count($thead));
        $contasBancos = Conta::all();

        $fs->writeStream($nomeArquivo, fopen($arquivos, 'r'));

        $eventosarquivo = [];
        foreach ($tbody as $ficha) {
            if (!in_array($ficha[1], $eventosarquivo)) {
                $eventosarquivo[] = $ficha[1];
            }
        }
        $eventosbase = DB::select("SELECT * FROM eventosfinanceiros WHERE eventofinanceiro LIKE ?", ['%Mensalidade%']);

        return $this->platesView('Financeiro/Importador/Table', compact(
            'thead',
            'tbody',
            'rawBody',
            'contasBancos',
            'eventosarquivo',
            'eventosbase'
        ));
    }

    public function insert(ServerRequestInterface $request)
    {
        $input = $request->getParsedBody();
        $header = explode(',', $input['thead']);
        $body = explode(',', $input['tbody']);
        $contaId = $input['conta'];
        $dataRecebimento = $input['dataRecebimento'];
        $thead = $header;
        array_push($thead, 'Status da Importação');
        $bodyResult = [];

        $tbody = array_chunk($body, count($header));

        foreach ($tbody as $ficha) {
            DB::beginTransaction();

            try {
                $ficha[5] = 'Sucesso';

                $pessoa = Pessoa::where('nome', trim($ficha[0]))->firstOrFail();

                $anoVencimentoTitulo = Carbon::parse($ficha[2])->format('Y');
                $anoletivoTituloId = DB::table('anoletivo')->where('anoletivo', $anoVencimentoTitulo)->first()->id;
                $matriculaQ =
                    "SELECT
                                am.id
                            FROM
                                alunos_matriculas am
                            JOIN
                                alunos a ON am.idaluno = a.id
                            JOIN
                                pessoas p on a.idpessoa = p.id
                            WHERE
                                TRIM(p.nome) = ?
                            AND
                                am.anoletivomatricula = ?
                            AND
                                am.status != 3";
                $matriculaId = DB::select($matriculaQ, [trim($ficha[0]), $anoletivoTituloId])[0]->id;

                $titulo = Titulo::gerar(
                    Conta::findOrFail($contaId),
                    Funcionario::findOrFail($_SESSION['id_funcionario']),
                    Matricula::findOrFail($matriculaId),
                    Carbon::parse($ficha[2]),
                    $ficha[3],
                    [
                        'origin' => 'importador_financeiro_sweduc',
                        'identificadorDaParcela' => $ficha[6],
                        'data' => date('Y-m-d H:i:s'),
                    ],
                    [],
                    true,
                    Carbon::parse($dataRecebimento),
                );

                $relacoes = json_decode($input['relacoesJson'], true);
                $eventoId = (int) $relacoes[$ficha[1]];
                $evento = DB::table('eventosfinanceiros')->where('id', $eventoId)->first();

                $item = new FichaItem();
                $item->idalunos_fichafinanceira = $titulo->id;
                $item->codigo = $evento->codigo;
                $item->eventofinanceiro = $evento->eventofinanceiro;
                $item->valor = $ficha[3];

                $item->saveOrFail();

                $recebimento = new Recebimento();
                $recebimento->idalunos_fichafinanceira = $titulo->id;
                $recebimento->idfuncionario = $_SESSION['id_funcionario'];
                $recebimento->datarecebido = $ficha[2];
                $recebimento->datacompensado = $ficha[2];
                $recebimento->valorrecebido = $ficha[3];
                $recebimento->formarecebido = 12;
                $recebimento->idcontasbanco = $contaId;

                $recebimento->saveOrFail();

                DB::commit();
            } catch (Throwable $e) {
                $ficha[5] = 'Falha';
                DB::rollBack();
            }

            $bodyResult[] = $ficha;
        }

        return $this->platesView('Financeiro/Importador/TableReturn', compact(
            'thead',
            'bodyResult',
        ));
    }

    public static function limpaCelula($cell)
    {
        if (is_string($cell)) {
            $cell = preg_replace('/^\d{4} - /', '', $cell);

            if (preg_match('/(Janeiro|Fevereiro|Março|Abril|Maio|Junho|Julho|Agosto|Setembro|Outubro|Novembro|Dezembro)\/(\d{4})/', $cell, $matches)) {
                $months = [
                    'Janeiro' => 1,
                    'Fevereiro' => 2,
                    'Março' => 3,
                    'Abril' => 4,
                    'Maio' => 5,
                    'Junho' => 6,
                    'Julho' => 7,
                    'Agosto' => 8,
                    'Setembro' => 9,
                    'Outubro' => 10,
                    'Novembro' => 11,
                    'Dezembro' => 12
                ];

                $month = $months[$matches[1]];
                $year = $matches[2];

                return Carbon::create($year, $month, 15)->format('Y-m-d');
            }
        }

        return $cell;
    }
}
