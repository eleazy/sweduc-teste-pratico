<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Controller\Controller;
use App\Exception\RecursoNaoAutorizadoException;
use App\Model\Core\Usuario;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller de contas a pagar
 */
class ContasAPagarController extends Controller
{
    /**
     * Retorna cadastro de contas a pagar
     */
    public function cadastrar(ServerRequestInterface $request): ResponseInterface
    {
        $usuario = $request->getAttribute(Usuario::class);
        $unidadeFuncionario = $usuario->funcionario->unidade;
        $unidades = $usuario->autorizadoEmUnidades('financeiro-contas-a-pagar-cadastrar');
        $idpermissoes = $usuario->idpermissao;
        $colaboradores = $this->getColaboradores($unidades);
        $permissaoPagar = $usuario->autorizado('financeiro-contas-a-pagar-pagar');

        if (empty($unidades)) {
            throw new RecursoNaoAutorizadoException();
        }

        $idfuncionario = $usuario->funcionario->id;
        $idfuncionariounidade = $usuario->funcionario->unidade->id;
        $nomeunidade = $usuario->funcionario->unidade->unidade;

        if ($idpermissoes == '1') {
            $empresasQuery  = "SELECT DISTINCT empresas.id, empresas.razaosocial
                FROM empresas
                ORDER BY razaosocial ASC";
        } else {
            if ($unidades == '0') {
                $empresasQuery  = "SELECT DISTINCT empresas.id, empresas.razaosocial
                    FROM empresas
                    ORDER BY razaosocial ASC";
            } elseif ($unidades == '') {
                $empresasQuery  = "SELECT DISTINCT empresas.id, empresas.razaosocial
                    FROM empresas
                    JOIN unidades_empresas ON unidades_empresas.idempresa = empresas.id
                    WHERE unidades_empresas.idunidade = '$idfuncionariounidade'
                    ORDER BY razaosocial ASC";
            } else {
                $unidadesList = implode(',', $unidades);
                $empresasQuery  = "SELECT DISTINCT empresas.id, empresas.razaosocial
                    FROM empresas
                    JOIN unidades_empresas ON unidades_empresas.idempresa = empresas.id
                    WHERE unidades_empresas.idunidade IN ($unidadesList)
                    ORDER BY razaosocial ASC";
            }
        }

        $empresasQueryResult = mysql_query($empresasQuery);
        $empresas = [];

        while ($empresasResultRow = mysql_fetch_array($empresasQueryResult, MYSQL_ASSOC)) {
            $empresas[] = $empresasResultRow;
        }

        return $this->platesView('Financeiro/ContasAPagar/Cadastrar', compact(
            'usuario',
            'idpermissoes',
            'unidades',
            'unidadeFuncionario',
            'colaboradores',
            'permissaoPagar',
            'empresas',
            'idfuncionario',
            'idfuncionariounidade',
            'nomeunidade',
        ));
    }

    public function recibo(ServerRequestInterface $request, $params): ResponseInterface
    {
        $input = $request->getParsedBody();
        $idcontaapagar = filter_var($input['idcontaapagar'], FILTER_VALIDATE_INT);

        $mes = date('m');

        $qconta = "SELECT
                *,
                IF(idcolaborador <> 0, 'Colaborador', fornecedor) as fornecedornovo
            FROM contasapagar
            LEFT JOIN fornecedores ON fornecedores.id=contasapagar.idfornecedor
            LEFT JOIN funcionarios ON funcionarios.id=contasapagar.idcolaborador
            WHERE contasapagar.id=" . $idcontaapagar;
        $rconta = mysql_query($qconta);
        $rowconta = mysql_fetch_array($rconta, MYSQL_ASSOC);

        $documento        = $rowconta['documento'];
        $eventofinanceiro = $rowconta['eventofinanceiro'];
        $valor            = $rowconta['valorpago'];
        $dt               = explode('-', $rowconta['datapagamento']);
        $datapagto        = $dt[2] . '/' . $dt[1] . '/' . $dt[0];

        $empresa = $rowconta['idempresa'];
        $qempresa = "SELECT * FROM empresas WHERE id=" . $empresa;
        $rempresa = mysql_query($qempresa);
        $rowempresa = mysql_fetch_array($rempresa, MYSQL_ASSOC);
        $razaosocial = $rowempresa['razaosocial'];

        $recebedor = $recebedor = ($rowconta['fornecedornovo'] == "Colaborador")
            ? $this->nomeColaborador($rowconta['idcolaborador'])
            : $rowconta['fornecedor'];

        switch ($mes) {
            case 1:
                $mes = "Janeiro";
                break;
            case 2:
                $mes = "Fevereiro";
                break;
            case 3:
                $mes = "Março";
                break;
            case 4:
                $mes = "Abril";
                break;
            case 5:
                $mes = "Maio";
                break;
            case 6:
                $mes = "Junho";
                break;
            case 7:
                $mes = "Julho";
                break;
            case 8:
                $mes = "Agosto";
                break;
            case 9:
                $mes = "Setembro";
                break;
            case 10:
                $mes = "Outubro";
                break;
            case 11:
                $mes = "Novembro";
                break;
            case 12:
                $mes = "Dezembro";
                break;
        }

        return $this->platesView('Financeiro/ContasAPagar/Recibo', [
            'documento' => $documento,
            'eventofinanceiro' => $eventofinanceiro,
            'valor' => $valor,
            'datapagto' => $datapagto,
            'descritivo' => $rowconta['descritivo'],
            'recebedor' => $recebedor,
            'cliente' => $_ENV['CLIENTE'],
            'razaosocial' => $razaosocial,
            'mes' => $mes
        ]);
    }

    /**
     * Retorna formulário de busca
     */
    public function buscar(ServerRequestInterface $request): ResponseInterface
    {
        $usuario = $request->getAttribute(Usuario::class);
        $unidadeFuncionario = $usuario->funcionario->unidade;
        $unidades = $usuario->autorizadoEmUnidades('financeiro-contas-a-pagar-consultar');
        $idpermissoes = $usuario->idpermissao;
        $colaboradores = $this->getColaboradores($unidades);

        if (empty($unidades)) {
            throw new RecursoNaoAutorizadoException();
        }

        return $this->platesView('Financeiro/ContasAPagar/Buscar', compact(
            'usuario',
            'idpermissoes',
            'unidades',
            'unidadeFuncionario',
            'colaboradores'
        ));
    }

    /**
     * Retorna listagem
     */
    public function listar(ServerRequestInterface $request): ResponseInterface
    {
        $input = $request->getQueryParams();
        $usuario = $request->getAttribute(Usuario::class);

        $codeventofinanceiro = filter_var($input['codeventofinanceiro'] ?? null, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $documento = filter_var($input['documento'] ?? null, FILTER_DEFAULT);
        $formapgto = filter_var($input['formapgto'] ?? null, FILTER_VALIDATE_INT);
        $idcolaborador = filter_var($input['idcolaborador'] ?? null, FILTER_VALIDATE_INT);
        $iddepartamento = filter_var($input['iddepartamento'] ?? null, FILTER_VALIDATE_INT);
        $idempresa = filter_var($input['idempresa'] ?? null, FILTER_DEFAULT);
        $idfornecedor = filter_var($input['idfornecedor'] ?? null, FILTER_VALIDATE_INT);
        $periodoate = filter_var($input['periodoate'] ?? null, FILTER_DEFAULT);
        $periodode = filter_var($input['periodode'] ?? null, FILTER_DEFAULT);
        $radiobusca = filter_var($input['radiobusca'] ?? null, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];
        $situacao = filter_var($input['situacao'] ?? null, FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY) ?: [];
        $valor = filter_var($input['valor'] ?? null, FILTER_DEFAULT);
        $valormaiormenor = filter_var($input['valormaiormenor'] ?? null, FILTER_DEFAULT);

        $relatorioUrl = '/financeiro/contas-a-pagar/relatorio?' . http_build_query($request->getQueryParams());
        $relatorioXlsUrl = '/financeiro/contas-a-pagar/relatorio-xls?' . http_build_query($request->getQueryParams());

        $tiposituacao = '';
        $exibebotaorelatorio = false;

        if (count($situacao) === 1) {
            if ($situacao[0] == 0) {
                $tiposituacao = ' Em Aberto';
                $exibebotaorelatorio = true;
            } elseif ($situacao[0] == 1) {
                $tiposituacao = ' Pagos';
                $exibebotaorelatorio = true;
            } elseif ($situacao[0] == 2) {
                $tiposituacao = ' Cancelados';
                $exibebotaorelatorio = true;
            }
        }

        return $this->platesView('Financeiro/ContasAPagar/Listar', [
            'idfuncionario' => $usuario->funcionario->id,
            'codeventofinanceiro' => $codeventofinanceiro,
            'documento' => $documento,
            'formapgto' => $formapgto,
            'idcolaborador' => $idcolaborador,
            'iddepartamento' => $iddepartamento,
            'idempresa' => $idempresa,
            'idfornecedor' => $idfornecedor,
            'periodoate' => $periodoate,
            'periodode' => $periodode,
            'relatorioUrl' => $relatorioUrl,
            'relatorioXlsUrl' => $relatorioXlsUrl,
            'radiobusca' => $radiobusca,
            'situacao' => $situacao,
            'usuario' => $usuario,
            'valor' => $valor,
            'valormaiormenor' => $valormaiormenor,
            'tiposituacao' => $tiposituacao,
            'exibebotaorelatorio' => $exibebotaorelatorio,
            'buscaColaborador' => fn ($idcolaborador) => $this->buscaColaborador($idcolaborador),
            'nomeColaborador' => fn ($idcolaborador) => $this->nomeColaborador($idcolaborador),
            'contasSqlResult' => $this->listagemContas(
                $radiobusca,
                $idempresa,
                $idfornecedor,
                $idcolaborador,
                $iddepartamento,
                $valor,
                $valormaiormenor,
                $situacao,
                $periodode,
                $periodoate,
                $formapgto,
                $documento,
                $codeventofinanceiro
            ),
            'totaltitulos' => 0,
            'totalesperado' => 0,
            'totalpago' => 0,
        ]);
    }

    /**
     * Retorna relatório
     */
    public function relatorio(ServerRequestInterface $request): ResponseInterface
    {
        $input = $request->getQueryParams();
        $usuario = $request->getAttribute(Usuario::class);

        $codeventofinanceiro = filter_var($input['codeventofinanceiro'] ?? null, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $documento = filter_var($input['documento'] ?? null, FILTER_DEFAULT);
        $formapgto = filter_var($input['formapgto'] ?? null, FILTER_VALIDATE_INT);
        $idcolaborador = filter_var($input['idcolaborador'] ?? null, FILTER_VALIDATE_INT);
        $iddepartamento = filter_var($input['iddepartamento'] ?? null, FILTER_VALIDATE_INT);
        $idempresa = filter_var($input['idempresa'] ?? null, FILTER_DEFAULT);
        $idfornecedor = filter_var($input['idfornecedor'] ?? null, FILTER_VALIDATE_INT);
        $periodoate = filter_var($input['periodoate'] ?? null, FILTER_DEFAULT);
        $periodode = filter_var($input['periodode'] ?? null, FILTER_DEFAULT);
        $situacao = filter_var($input['situacao'] ?? null, FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY) ?: [];
        $valor = filter_var($input['valor'] ?? null, FILTER_DEFAULT);
        $valormaiormenor = filter_var($input['valormaiormenor'] ?? null, FILTER_DEFAULT);
        $radiobusca = filter_var($input['radiobusca'] ?? null, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];

        $qempresa = "SELECT * FROM empresas WHERE id=" . $idempresa;
        $rempresa = mysql_query($qempresa);
        $rowempresa = mysql_fetch_array($rempresa, MYSQL_ASSOC);
        $tiposituacao = '';

        if ($situacao[0] == 0) {
            $tiposituacao = ' Relatório de <strong>Contas a Pagar</strong> ';
        } elseif ($situacao[0] == 1) {
            $tiposituacao = ' Relatório de <strong>Contas Pagas</strong> ';
        } elseif ($situacao[0] == 2) {
            $tiposituacao = ' Relatório de <strong>Cancelados</strong> ';
        }

        if ($periodode == "") {
            $periodode = date("d/m/Y");
        }
        if ($periodoate == "") {
            $periodoate = $periodode;
        }

        $totalesperadoapagar = 0;
        $nomefornecedor = "";

        return $this->platesView('Financeiro/ContasAPagar/Relatorio', [
            'idfuncionario' => $usuario->funcionario->id,
            'idempresa' => $idempresa,
            'idfornecedor' => $idfornecedor,
            'idcolaborador' => $idcolaborador,
            'iddepartamento' => $iddepartamento,
            'valormaiormenor' => $valormaiormenor,
            'valor' => $valor,
            'documento' => $documento,
            'situacao' => $situacao,
            'periodode' => $periodode,
            'periodoate' => $periodoate,
            'formapgto' => $formapgto,
            'contasSqlResult' => $this->listagemContas(
                $radiobusca,
                $idempresa,
                $idfornecedor,
                $idcolaborador,
                $iddepartamento,
                $valor,
                $valormaiormenor,
                $situacao,
                $periodode,
                $periodoate,
                $formapgto,
                $documento,
                $codeventofinanceiro
            ),
            'totaltitulos' => 0,
            'totalesperado' => 0,
            'totalpago' => 0,
            'totalesperadoapagar' => $totalesperadoapagar,
            'nomefornecedor' => $nomefornecedor,
            'rowempresa' => $rowempresa,
            'tiposituacao' => $tiposituacao,
        ]);
    }

    /**
     * Retorna relatório XLS
     */
    public function relatorioXLS(ServerRequestInterface $request): ResponseInterface
    {
        $input = $request->getQueryParams();
        $usuario = $request->getAttribute(Usuario::class);

        $codeventofinanceiro = filter_var($input['codeventofinanceiro'] ?? null, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $documento = filter_var($input['documento'] ?? null, FILTER_DEFAULT);
        $descritivo = filter_var($input['descritivo'] ?? null, FILTER_DEFAULT);
        $formapgto = filter_var($input['formapgto'] ?? null, FILTER_VALIDATE_INT);
        $idcolaborador = filter_var($input['idcolaborador'] ?? null, FILTER_VALIDATE_INT);
        $iddepartamento = filter_var($input['iddepartamento'] ?? null, FILTER_VALIDATE_INT);
        $idempresa = filter_var($input['idempresa'] ?? null, FILTER_DEFAULT);
        $idfornecedor = filter_var($input['idfornecedor'] ?? null, FILTER_VALIDATE_INT);
        $periodoate = filter_var($input['periodoate'] ?? null, FILTER_DEFAULT);
        $periodode = filter_var($input['periodode'] ?? null, FILTER_DEFAULT);
        $situacao = filter_var($input['situacao'] ?? null, FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY) ?: [];
        $valor = filter_var($input['valor'] ?? null, FILTER_DEFAULT);
        $idusuario = filter_var($input['idusuario'] ?? null, FILTER_VALIDATE_INT);
        $data_competencia = filter_var($input['data_competencia'] ?? null, FILTER_DEFAULT);
        $autorizado_por_id = filter_var($input['autorizado_por_id'] ?? null, FILTER_VALIDATE_INT);
        $valormaiormenor = filter_var($input['valormaiormenor'] ?? null, FILTER_DEFAULT);
        $radiobusca = filter_var($input['radiobusca'] ?? null, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];

        $qempresa = "SELECT * FROM empresas WHERE id=" . $idempresa;
        $rempresa = mysql_query($qempresa);
        $rowempresa = mysql_fetch_array($rempresa, MYSQL_ASSOC);
        $tiposituacao = '';

        if ($situacao[0] == 0) {
            $tiposituacao = ' Relatório de <strong>Contas a Pagar</strong> ';
        } elseif ($situacao[0] == 1) {
            $tiposituacao = ' Relatório de <strong>Contas Pagas</strong> ';
        } elseif ($situacao[0] == 2) {
            $tiposituacao = ' Relatório de <strong>Cancelados</strong> ';
        }

        if ($periodode == "") {
            $periodode = date("d/m/Y");
        }
        if ($periodoate == "") {
            $periodoate = $periodode;
        }

        $totalesperadoapagar = 0;
        $nomefornecedor = "";

        return $this->platesView('Financeiro/ContasAPagar/RelatorioXLS', [
            'idfuncionario' => $usuario->funcionario->id,
            'idempresa' => $idempresa,
            'idfornecedor' => $idfornecedor,
            'idcolaborador' => $idcolaborador,
            'iddepartamento' => $iddepartamento,
            'valormaiormenor' => $valormaiormenor,
            'valor' => $valor,
            'documento' => $documento,
            'descritivo' => $descritivo,
            'situacao' => $situacao,
            'periodode' => $periodode,
            'periodoate' => $periodoate,
            'formapgto' => $formapgto,
            'idusuario' => $idusuario,
            'data_competencia' => $data_competencia,
            'autorizado_por_id' => $autorizado_por_id,
            'contasSqlResult' => $this->listagemContas(
                $radiobusca,
                $idempresa,
                $idfornecedor,
                $idcolaborador,
                $iddepartamento,
                $valor,
                $valormaiormenor,
                $situacao,
                $periodode,
                $periodoate,
                $formapgto,
                $documento,
                $codeventofinanceiro
            ),
            'totaltitulos' => 0,
            'totalesperado' => 0,
            'totalpago' => 0,
            'totalesperadoapagar' => $totalesperadoapagar,
            'nomefornecedor' => $nomefornecedor,
            'rowempresa' => $rowempresa,
            'tiposituacao' => $tiposituacao,
        ]);
    }

    private function listagemContas(
        $radiobusca,
        $idempresa,
        $idfornecedor,
        $idcolaborador,
        $iddepartamento,
        $valor,
        $valormaiormenor,
        $situacao,
        $periodode,
        $periodoate,
        $formapgto,
        $documento,
        $codeventofinanceiro
    ) {
        $sql = "";
        $dataorder = 'datavencimento';

        foreach ($radiobusca as $k) {
            switch ($k) {
                case "fornecedor":
                    $sql .= " AND contasapagar.idfornecedor=$idfornecedor ";
                    break;

                case "colaborador":
                    $sql .= " AND contasapagar.idcolaborador=$idcolaborador ";
                    break;

                case "departamento":
                    $sql .= " AND funcionarios.iddepartamento=$iddepartamento ";
                    break;

                case "valor":
                    if ($valor == "") {
                        $valor = 0;
                    } else {
                        $valor = str_replace(',', '.', str_replace('.', '', $valor));
                    }

                    if ($valormaiormenor == '0') {
                        $sql .= " AND valor=$valor ";
                    } elseif ($valormaiormenor == '1') {
                        $sql .= " AND valor<$valor ";
                    } elseif ($valormaiormenor == '2') {
                        $sql .= " AND valor>$valor ";
                    }
                    break;


                case "situacao":
                    $ss = implode(",", $situacao);
                    $sql .= " AND situacao IN ($ss) ";
                    foreach ($situacao as $sit) {
                        switch ($sit) {
                            case "0": //ABERTO
                                $datatipo = "datavencimento";
                                break;
                            case "1": //PAGO
                                $datatipo = "datapagamento";
                                $dataorder = 'datapagamento';
                                break;
                            case "2": //CANCELADOS
                                $datatipo = "datacancelado";
                                break;
                        }

                        if ($periodode == "") {
                            $periodode = date("Y-m-d");
                        } else {
                            $vctde = explode("/", $periodode);
                            $periodode = $vctde[2] . "-" . $vctde[1] . "-" . $vctde[0];
                        }

                        if ($periodoate == "") {
                            $sql .= " AND " . $datatipo . " > " . $periodode . " ";
                        } else {
                            $vctate = explode("/", $periodoate);
                            $periodoate = $vctate[2] . "-" . $vctate[1] . "-" . $vctate[0];
                            $sql .= " AND " . $datatipo . "  BETWEEN '" . $periodode . "' AND '" . $periodoate . "' ";
                        }

                        $sql .= ($formapgto > 0) ? " AND idformaspagamentos=" . $formapgto . " " : "";
                    }
                    break;

                case "documento":
                    $sql .= " AND documento LIKE '%$documento%' ";
                    break;

                case "eventofinanceiro":
                    $queryEventos = [];
                    foreach ($this->filtraRaizesEventosFinanceiros($codeventofinanceiro) as $key => $value) {
                        $queryEventos[] = "contasapagar.codigoeventofinanceiro LIKE '" . $value . "%'";
                    }
                    $sql .= "AND (" . implode(" OR ", $queryEventos) . ") ";
                    break;

                default:
                    $sql = "";
                    break;
            }
        }

        $query  = "SELECT
                *,
                contasapagar.id as cid,
                contasapagar.multa as cmulta,
                DATE_FORMAT(datacancelado,'%d/%m/%Y') as dtcancelado,
                DATE_FORMAT(datareaberto,'%d/%m/%Y') as dtreaberto,
                DATE_FORMAT(datavencimento,'%d/%m/%Y') as dtvenc,
                DATE_FORMAT(datapagamento,'%d/%m/%Y') as dtpag,
                IF(idcolaborador <> 0, 'Colaborador', fornecedor) as fornecedornovo,
                pessoas.nome as colaborador
            FROM contasapagar
            INNER JOIN empresas ON empresas.id=contasapagar.idempresa
            INNER JOIN contasbanco ON contasapagar.idcontasbanco=contasbanco.id
            LEFT JOIN formaspagamentos ON formaspagamentos.id=contasapagar.idformaspagamentos
            LEFT JOIN fornecedores ON fornecedores.id=contasapagar.idfornecedor
            LEFT JOIN funcionarios ON funcionarios.id=contasapagar.idcolaborador
            LEFT JOIN pessoas ON pessoas.id=funcionarios.idpessoa
            WHERE contasapagar.idempresa=$idempresa " . $sql . "
            GROUP BY contasapagar.id ORDER BY $dataorder ASC, fornecedor ASC";

        return mysql_query($query);
    }

    private function getColaboradores(array $unidades)
    {
        $unidadesString = join(',', $unidades);
        $colaboradores = [];
        $querycolab = "SELECT f.id,p.nome,d.departamento, unidade
            FROM funcionarios f
            INNER JOIN pessoas p ON f.idpessoa=p.id
            INNER JOIN departamentos d ON f.iddepartamento=d.id
            LEFT JOIN unidades ON f.idunidade = unidades.id
            WHERE f.idunidade IN ($unidadesString)
            ORDER BY unidades.id, p.nome ASC";

        $resultcolab = mysql_query($querycolab);

        while ($rowcolab = mysql_fetch_array($resultcolab, MYSQL_ASSOC)) {
            $colaboradores[] = $rowcolab;
        }

        return $colaboradores;
    }

    private function filtraRaizesEventosFinanceiros($eventos)
    {
        $trimmedEventos = [];
        foreach ($eventos as $key => $value) {
            $trimmedEvt = rtrim($value, '0');

            /*
            * A mascara possui duas casas com uma unidade e 3 casas com duas unidades
            * Para valores maiores que 2 casas que vierem impares adiciona um zero no final
            */
            $repeticoes = (strlen($trimmedEvt) > 2 && strlen($trimmedEvt) % 2 != 0 ? 1 : 0);
            $trimmedEventos[] = $trimmedEvt . str_repeat('0', $repeticoes);
            ;
        }

        foreach ($trimmedEventos as $roots) {
            foreach ($trimmedEventos as $key => $evento) {
                if (str_contains($evento, $roots) && strlen($evento) > strlen($roots)) {
                    unset($trimmedEventos[$key]);
                }
            }
        }

        return $trimmedEventos;
    }

    private function buscaColaborador($idcolaborador)
    {
        if ($idcolaborador > 0) {
            $qcolab = "SELECT p.nome as nomecol, d.departamento as deptocol FROM funcionarios f
                    INNER JOIN departamentos d ON f.iddepartamento=d.id
                    INNER JOIN pessoas p ON f.idpessoa=p.id
                    WHERE f.id=" . $idcolaborador;
            $rcolab = mysql_query($qcolab);
            $rowcolab = mysql_fetch_array($rcolab, MYSQL_ASSOC);
            return 'Colaborador: ' . $rowcolab['nomecol'] . ' (' . $rowcolab['deptocol'] . ')';
        } else {
            return '';
        }
    }

    private function nomeColaborador($idcolaborador)
    {
        $qcolab = "SELECT p.nome as nomecol, d.departamento as deptocol FROM funcionarios f
                INNER JOIN departamentos d ON f.iddepartamento=d.id
                INNER JOIN pessoas p ON f.idpessoa=p.id
                WHERE f.id=" . $idcolaborador;
        $rcolab = mysql_query($qcolab);
        $rowcolab = mysql_fetch_array($rcolab, MYSQL_ASSOC);
        return $rowcolab['nomecol'];
    }

    public function getUsuariosAutorizados(ServerRequestInterface $request, $args)
    {
            /*
            * Retorna Usuarios que possuem a permissão de
            * autorizar pagamentos no Contas a Pagar
            */

        $queryUsu = "SELECT U.id, P.nome FROM usuarios U
        LEFT JOIN pessoas P ON (U.idpessoa = P.id)
        LEFT JOIN politica_grupos PG ON (U.politica_grupo_id = PG.id)
        LEFT JOIN politica_permissoes_grupos PP ON (PG.id = PP.grupo_id)
        WHERE PP.permissao = 'financeiro-contas-a-pagar-autorizar'
        AND P.nome NOT LIKE 'zadislaw@swsistemas.com.br'
        AND P.nome NOT LIKE 'fernando.sousa@swsistemas.com.br'
        AND P.nome NOT LIKE 'sistema SW com Inicie'
        GROUP BY P.id
        ORDER BY P.nome";

        $resultUsu = mysql_query($queryUsu);

        $response = [];
        while ($rowUsu = mysql_fetch_array($resultUsu, MYSQL_ASSOC)) {
            $response .= "<option value=" . $rowUsu['id'] . ">" . $rowUsu['nome'] . "</option>";
        }

        return $this->plainTextResponse($response);
    }
}
