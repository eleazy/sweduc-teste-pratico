<?php

declare(strict_types=1);

namespace App\Academico\Controller;

use App\Controller\Controller;
use App\Academico\Model\Documento;
use App\Academico\Model\Matricula;
use App\Academico\Model\PeriodoLetivo;
use App\Academico\Model\AlunoStatus;
use App\Model\Core\ConfiguracaoKV;
use App\Model\Core\Configuracao;
use App\Model\Core\Pais;
use App\Model\Core\Sexo;
use App\Model\Core\Unidade;
use App\Model\Core\Usuario;
use App\Model\Core\Pessoa;
use Psr\Http\Message\ServerRequestInterface;

class AlunoController extends Controller
{
    public function busca(ServerRequestInterface $request)
    {
        $alunosStatus = AlunoStatus::all();
        return $this->platesView('Academico/Aluno/Busca', [
            'alunosStatus' => $alunosStatus,
        ]);
    }

    public function listar(ServerRequestInterface $request)
    {
        $ralunosnovos = null;
        $input = $request->getParsedBody();
        $usuario = $request->getAttribute(Usuario::class);

        $idunidade = $input['idunidadebusca'];
        $idcurso = $input['curso'];
        $idserie = $input['serie'];
        $idturma = $input['turma'];
        $idanoletivo = $input['idanoletivo'];
        $nome = $input['nome'];
        $codigo = $input['codigo'];
        $idsexo = $input['idsexo'];
        $situacao = $input['situacao'];
        $ordenacao = $input['ordenacao'];
        $paginacao = $input['paginacao'] ?? false;
        $limitesup = $input['limitesup'];
        $limiteinf = $input['limiteinf'];
        $novosalunos = isset($input['novosalunos']);
        $novosalunosunidade = isset($input['novosalunosunidade']);

        $sql = "";
        $sqlselect = "";
        $sqlfim = ", cursos.curso, series.serie, turmas.turma";

        $sqlgroupby = "";
        if ($idanoletivo != "todos") {
            $sql .= " AND ( alunos_matriculas.turmamatricula=turmas.id OR alunos_matriculas.turmamatricula<1) ";
            if ($idanoletivo > 0) { // ALGUM ANO LETIVO VÁLIDO
                $sqlselect = "unidades.id as uid, ";
                $sql .= " AND alunos_matriculas.anoletivomatricula = anoletivo.id AND alunos_matriculas.anoletivomatricula = " . $idanoletivo . " AND MONTH(alunos_matriculas.datamatricula)>0 ";
            } elseif ($idanoletivo == -1) { // NÃO MATRICULADOS.
                $sql .= " AND alunos_matriculas.status <> 1 ";
                $sqlgroupby = " GROUP BY alunos_matriculas.id ";
            }
        }

        if ($idunidade == "todos") {
            $unidades = "'" . implode("','", $usuario->autorizadoEmUnidades('academico-alunos-consultar')) . "'";
            $sql .= " AND unidades.id IN ($unidades) ";
        } else {
            $sql .= " AND alunos_matriculas.idunidade = '$idunidade' ";
        }

        if ($idcurso != "todos") {
            $sql .= " AND alunos_matriculas.status > 0 AND cursos.id = $idcurso  AND cursos.idunidade = unidades.id";
        }

        if ($idserie != "todos") {
            $sql .= " AND series.id = $idserie  AND series.idcurso = cursos.id";
        }

        if ($idturma != "todos") {
            $sql .= " AND turmas.id = $idturma  AND turmas.idserie = series.id";
        }

        // Remove multiplas entradas de espaço na digitação do nome
        $nome = trim(preg_replace('/\s+/', ' ', $nome ?? ''));
        if ($nome != "") {
            $nome_like = addslashes(preg_replace('/\s+/', '%', $nome));
            $sql .= " AND pessoas.nome LIKE '%$nome_like%' ";
        }

        if ($codigo != "") {
            $sql .= " AND numeroaluno LIKE '$codigo%' ";
        }

        $mesnascimento = $_POST['mesnascimento'];
        if ($mesnascimento != "todos") {
            $sql .= " AND MONTH(datanascimento)='$mesnascimento' ";
        }

        if (($seguroescolar = filter_var($_REQUEST['seguroescolar'], FILTER_VALIDATE_INT)) !== false) {
            $sql .= " AND alunos_matriculas.seguroescolar = " . $seguroescolar . " ";
        }

        if (($presencial = filter_var($_REQUEST['presencial'], FILTER_VALIDATE_INT)) !== false) {
            $sql .= " AND alunos_matriculas.presencial = " . $presencial . " ";
        }

        $aluno_not_in = '';

        // sexo
        if ($idsexo > 0) {
            $sql .= " AND pessoas.idsexo = " . $idsexo . " ";
        }

        if ($novosalunos && $idanoletivo > 0) {
            $statusExcluido = Matricula::STATUS_CANCELADO;

            $qalunosnovos = "SELECT
                    idaluno
                FROM alunos_matriculas
                WHERE anoletivomatricula < '$idanoletivo'
                AND status != $statusExcluido
                GROUP BY idaluno";
            $ralunosnovos = mysql_query($qalunosnovos);
            $notin = '';
            while ($rowaln = mysql_fetch_array($ralunosnovos, MYSQL_ASSOC)) {
                $notin .= $rowaln['idaluno'] . ',';
            }
            $aluno_not_in .= ' AND alunos_matriculas.idaluno NOT IN (' . rtrim($notin, ',') . ') ';
        }


        if ($novosalunosunidade && $idanoletivo > 0) {
            $statusExcluido = Matricula::STATUS_CANCELADO;

            $qalunosnovos = "SELECT
                    idaluno
                FROM alunos_matriculas
                WHERE anoletivomatricula < '$idanoletivo'
                AND status != $statusExcluido
                GROUP BY idaluno";
            $ralunosnovosunidade = mysql_query($qalunosnovos);
            $notin = '';
            while ($rowaln = mysql_fetch_array($ralunosnovos, MYSQL_ASSOC)) {
                $notin .= $rowaln['idaluno'] . ',';
            }
            $aluno_not_in .= ' AND alunos_matriculas.idaluno NOT IN (' . rtrim($notin, ',') . ') ';
        }


        if ($situacao != null) {
            $sql .= " AND ( alunos_matriculas.status = " . $situacao . ") ";
        }

        $order = "ORDER BY ";

        if (!empty($agrupar_ano_letivo)) {
            $order .= "anoletivo, ";
        }

        switch ($ordenacao) {
            // Ordenação alfabética
            case 1:
                $order .= "pessoas.nome " . $sqlfim . " ASC ";
                break;
            // Ordenação por data de matrícula
            case 2:
                $order .= "datamatricula, pessoas.nome " . $sqlfim . " ASC ";
                break;
            // Ordenação por data de nascimento
            case 3:
                $order .= "pessoas.datanascimento, pessoas.nome " . $sqlfim . " ASC ";
                break;
            // Ordenação por numero do aluno
            case 4:
                $order .= "alunos.numeroaluno ASC ";
                break;
            // Ordenação por data de aniversário
            case 5:
                $order .= "month(pessoas.datanascimento), DAYOFMONTH(pessoas.datanascimento) ASC ";
                break;
        }

        $query = "SELECT
                SQL_CALC_FOUND_ROWS *,
                unidades.unidade,
                alunos_matriculas.idunidade as uid,
                alunos_matriculas.idempresa as empid,
                alunos.id as as_id,
                alunos_matriculas.id as am_id,
                alunos_matriculas.id as matricula_id,
                pessoas.id as pid,
                $sqlselect
                DATE_FORMAT(datastatus,'%d/%m/%Y') AS 'dtstatus',
                DATE_FORMAT(datanascimento,'%d/%m/%Y') as dtnasc,
                alunos_status.nome as nome_status,
                pessoas.nome as nome,
                entrada,
                saida,
                DATE_FORMAT(iniciada_em,'%d/%m/%Y') as iniciada_em,
                DATE_FORMAT(adiada_para,'%d/%m/%Y') as adiada_para_,
                DATE_FORMAT(terminada_em,'%d/%m/%Y') as encerrada_em,
                dias_da_semana,
                usuarios.id as usuario_id
            FROM alunos
            JOIN pessoas ON alunos.idpessoa=pessoas.id
            JOIN alunos_matriculas ON alunos_matriculas.idaluno=alunos.id
            JOIN alunos_status ON alunos_matriculas.status=alunos_status.id
            JOIN anoletivo ON anoletivo.id=anoletivomatricula
            LEFT JOIN turmas ON turmamatricula = turmas.id
            LEFT JOIN turnos ON idturno = turnos.id
            LEFT JOIN series ON series.id=turmas.idserie
            LEFT JOIN cursos ON cursos.id=series.idcurso
            LEFT JOIN unidades ON alunos_matriculas.idunidade = unidades.id
            LEFT JOIN usuarios ON usuarios.idpessoa=pessoas.id
            WHERE (alunos_matriculas.idunidade=unidades.id OR unidades.id IS NULL)
            $sql
            $aluno_not_in
            $sqlgroupby
            $order";

        if ($paginacao == "1") {
            $query .= " LIMIT " . $limiteinf . "," . $limitesup;
        }

        if ($situacao == -1) { // EX-ALUNOS ANTIGOS
            $query = "SELECT SQL_CALC_FOUND_ROWS id, nomealuno as nome, ano, escola, local, serie as s, disciplina, media, cargahoraria, frequencia, situacao FROM alunos_historico
                        WHERE idaluno=-1 AND nomealuno LIKE '%$nome%' GROUP BY nome " . $order . " LIMIT " . $limiteinf . "," . $limitesup;
        }

        if ($situacao == -1) { // EX-ALUNOS ANTIGOS
            $query = "SELECT SQL_CALC_FOUND_ROWS id, nomealuno as nome, ano, escola, local, serie as s, disciplina, media, cargahoraria, frequencia, situacao FROM alunos_historico
                        WHERE idaluno = -1 AND nomealuno LIKE '%$nome%' GROUP BY nome " . $order . "  LIMIT " . $limiteinf . "," . $limitesup;
        }

        $result = mysql_query($query);
        $queryCount = "SELECT FOUND_ROWS() as cnt;";
        $resultCount = mysql_query($queryCount);
        $rowCount = mysql_fetch_array($resultCount, MYSQL_ASSOC);
        $cntTotal = $rowCount['cnt'];
        if ($cntTotal < $limitesup) {
            $limitesuperior = $cntTotal;
        } else {
            $limitesuperior = $limitesup;
        }

        return $this->platesView('Academico/Aluno/Listar', [
            'usuario' => $usuario,
            'cliente' => $_ENV['CLIENTE'],
            'permitirEnviarEmails' => ConfiguracaoKV::chave('SMTP_APROVADO') == true,
            'cntTotal' => $cntTotal,
            'limitesuperior' => $limitesuperior,
            'alunosResult' => $result,
            'queryCount' => $queryCount,
            'resultCount' => $resultCount,
            'rowCount' => $rowCount,
            'cntTotal' => $cntTotal,
            'limiteinf' => $limiteinf,
            'idanoletivo' => $idanoletivo,
            'idpermissoes' => $usuario->idpermissao,
            'paginacao' => $paginacao,
        ]);
    }

    public function cadastro(ServerRequestInterface $request)
    {
        $dados = [];
        $input = $request->getParsedBody() + $request->getQueryParams();

        $usaMatriculaOnline = Configuracao::completa()['usa_matricula_online'];
        $dados['usaMatriculaOnline'] = $usaMatriculaOnline;

        $cliente = $_ENV['CLIENTE'];
        $periodosLetivos = PeriodoLetivo::orderBy('anoletivo', 'desc')->get();
        $periodoPadraoMatricula = Unidade::first()->anoletivo_matricula;
        $alunosStatus = AlunoStatus::all();

        $aluno = null;
        $matricula = null;

        $alunoId = filter_var($input['alunoId'] ?? null, FILTER_VALIDATE_INT);
        $matriculaId = filter_var($input['matriculaId'] ?? null, FILTER_VALIDATE_INT);
        $ocorrenciaId = filter_var($input['ocorrenciaId'] ?? null, FILTER_VALIDATE_INT);

        $usuario = $request->getAttribute(Usuario::class);
        $funcionario = $usuario->funcionario;

        $dados['usuario'] = $usuario;
        $dados['fotoUrl'] = '/images/shared/student.png';
        $dados['aba'] = $input['aba'] ?? '';
        $dados['identrevista'] = $input['identrevista'] ?? null;
        $dados['alergiaalimentos'] = null;
        $dados['alergiamedicamentos'] = null;
        $dados['aluno'] = $aluno;
        $dados['alunoId'] = null;
        $dados['cliente'] = $cliente;
        $dados['contrato_desempenho'] = null;
        $dados['documentosRequeridos'] = Documento::where('idserie', 0)->get();
        $dados['escola_origem'] = null;
        $dados['fatorRH'] = null;
        $dados['foto'] = "images/shared/student.png";
        $dados['idaluno'] = null;
        $dados['idestado'] = null;
        $dados['idestadocivil'] = null;
        $dados['idestadonascimento'] = null;
        $dados['idfuncionario'] = $funcionario->id;
        $dados['idfuncionariounidade'] = $funcionario->idunidade;
        $dados['idpaisnascimento'] = null;
        $dados['medicamentofebre'] = null;
        $dados['medicamentosregulares'] = null;
        $dados['modo'] = $input['modo'] ?? '';
        $dados['nomeunidade'] = $funcionario->unidade->titulo;
        $dados['observacoesmedicas'] = null;
        $dados['paises'] = Pais::all();
        $dados['estados'] = Pais::where('nom_pais', 'BRASIL')->first()->estados ?? [];
        $dados['cidades'] = [];
        $dados['pediatra_tel'] = null;
        $dados['pediatra'] = null;
        $dados['periodoPadraoMatricula'] = $periodoPadraoMatricula;
        $dados['periodosLetivos'] = $periodosLetivos;
        $dados['alunosStatus'] = $alunosStatus;
        $dados['planosaude_tel'] = null;
        $dados['planosaude'] = null;
        $dados['sexos'] = Sexo::all();
        $dados['raca'] = Pessoa::getAllRacas();
        $dados['tiposanguineo'] = null;
        $dados['tratamentosmedicos'] = null;
        $dados['turmamatricula'] = null;
        $dados['documentosEntregues'] = [];
        $dados['ocorrenciaId'] = $ocorrenciaId;
        $query = "SELECT * FROM tipotel ORDER BY id ASC";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $dados['tipoTel'][] = $row;
        }

        if (empty($matriculaId)) {
            return $this->platesView('Academico/Aluno/Cadastro', $dados);
        }

        $matricula = Matricula::with('aluno.pessoa')->findOrFail($matriculaId);
        $aluno = $matricula->aluno;

        $nummatricula = $matricula->nummatricula;
        $idpessoa = $aluno->idpessoa;
        $idunidade = $matricula->idunidade;
        $idaluno = $aluno->id;
        $idserie = $matricula->turma->serie->id;

        $dados['idaluno'] = $aluno->id;
        $dados['idempresa'] = $matricula->idempresa;
        $dados['idpessoa'] = $aluno->idpessoa;
        $dados['idunidade'] = $matricula->idunidade;
        $dados['nummatricula'] = $matricula->nummatricula;

        if (file_exists($fotoAluno = "clientes/$cliente/fotos/$idpessoa.png")) {
            $dados['fotoUrl'] = $fotoAluno . '?' . filemtime($fotoAluno);
        }

        if ($aluno->pessoa->foto) {
            $dados['fotoUrl'] = '/api/v1/img/perfil/' . $aluno->pessoa->foto;
        }

        $queryA = "SELECT
                *,
                alunos.id as id,
                alunos.id as aid,
                alunos_matriculas.id as idmatricula,
                alunos_matriculas.idfuncionario as idfuncmatricula,
                cursos.id as idcurso,
                series.id as idserie,
                alunos_matriculas.valorAnuidade as valorAnuidade,
                (alunos_matriculas.valorAnuidade-alunos_matriculas.bolsa) as valorAnuidadeAluno,
                alunos_matriculas.bolsa as bolsaAluno,
                alunos_matriculas.bolsapercentual as bolsapercentualAluno,
                turmas.id as idturma,
                DATE_FORMAT(datastatus,'%d/%m/%Y') AS 'dtstatus',
                DATE_FORMAT(datanascimento,'%d/%m/%Y') AS 'dtnasc',
                DATE_FORMAT(validade,'%d/%m/%Y') AS 'dtvalidade',
                DATE_FORMAT(dataexpedicao,'%d/%m/%Y') AS 'dtexpedicao',
                DATE_FORMAT(datavalidacao,'%d/%m/%Y') AS 'dtvalidacao',
                DATE_FORMAT(datamatricula,'%d/%m/%Y') AS 'dtmatricula',
                DATE_FORMAT(te_dataemissao,'%d/%m/%Y') AS 'dtte_dataemissao',
                DATE_FORMAT(rg_expedido_em,'%d/%m/%Y') AS 'rg_data_expedicao',
                anoletivomatricula as idanoletivo,
                anoletivo,
                idempresa,
                alunos_matriculas.turmamatricula as idturmamatricula,
                alunos_status.nome as nome_status,
                pessoas.nome as nome
            FROM
                alunos,
                pessoas,
                alunos_matriculas JOIN alunos_status ON alunos_matriculas.status = alunos_status.id,
                cursos,
                series,
                turmas,
                anoletivo
            WHERE
                alunos_matriculas.anoletivomatricula=anoletivo.id AND
                alunos_matriculas.turmamatricula=turmas.id AND
                turmas.idserie=series.id AND
                series.idcurso=cursos.id AND
                cursos.idunidade='$idunidade' AND
                alunos.id=alunos_matriculas.idaluno AND
                alunos.id='$idaluno' AND
                alunos.idpessoa=pessoas.id AND
                alunos_matriculas.nummatricula='$nummatricula'"
        ;

        $resultA = mysql_query($queryA);
        $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);

        $dados['idturmamatriculaaluno'] = $rowA['idturmamatricula'];
        $dados['idmatricula'] = $rowA['idmatricula'];
        $dados['idempresa'] = $rowA['idempresa'];
        $dados['idanoletivomatriculaaluno'] = $rowA['anoletivomatricula'];
        $dados['anoletivomatriculaaluno'] = $rowA['anoletivo'];
        $dados['idunidadealuno'] = $rowA['idunidade'];

        $queryB = "SELECT * FROM usuarios WHERE usuarios.idpessoa=$idpessoa";
        $resultB = mysql_query($queryB);
        $rowB = mysql_fetch_array($resultB, MYSQL_ASSOC);
        $dados['login'] = $rowB['login'];
        $dados['senha'] = $rowB['senha'];
        $dados['funcmatricula'] = $matricula->registradoPor->pessoa->nome ?? '';

        $emails = [];
        $query = "SELECT * FROM emails WHERE idpessoa='$idpessoa' ORDER BY primario DESC, email ASC";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $emails[] = $row;
        }

        if ($rowA['turmamatricula'] > 0) {
            $queryTurma = "SELECT turma FROM turmas, series, cursos WHERE turmas.id=" . $rowA['turmamatricula'] . " AND turmas.idserie=series.id AND series.idcurso=cursos.id AND cursos.idunidade=" . $idunidade;
            $resultTurma = mysql_query($queryTurma);
            $rowTurma = mysql_fetch_array($resultTurma, MYSQL_ASSOC);

            $queryUnidade = "SELECT unidade FROM unidades WHERE id=" . $idunidade;
            $resultUnidade = mysql_query($queryUnidade);
            $rowUnidade = mysql_fetch_array($resultUnidade, MYSQL_ASSOC);

            $queryFin = "SELECT COUNT(*) as cnt FROM alunos_fichafinanceira WHERE situacao=0 AND datavencimento<CURDATE() AND idaluno=" . $idaluno;
            $resultFin = mysql_query($queryFin);
            $rowFin = mysql_fetch_array($resultFin, MYSQL_ASSOC);
        }

        $dados['documentosRequeridos'] = Documento::whereIn('idserie', [0, $idserie])
            ->whereDoesntHave('alunos', fn ($x) => $x->where('idaluno', $alunoId))
            ->get()
        ;

        $query2 = "SELECT * FROM telefones WHERE idpessoa='$idpessoa'";
        $result2 = mysql_query($query2);
        while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
            $dados['telefones'][] = $row2;
        }

        $qel = "SELECT disciplinas.id as id, disciplina
            FROM disciplinas INNER JOIN grade ON grade.iddisciplina=disciplinas.id
            WHERE numordem BETWEEN 1000 AND 1100
            AND grade.idturma='$matricula->turmamatricula'
            AND grade.idanoletivo='$matricula->anoletivomatricula'
            ORDER BY disciplina ASC
        ";

        $rel = mysql_query($qel);
        $eletivas = [];
        while ($rowel = mysql_fetch_array($rel, MYSQL_ASSOC)) {
            $verificaElAl = self::verificaInscritoEletiva($idaluno, $nummatricula, $rowel['id'], $matricula->anoletivomatricula);

            $eletivas[] = $rowel + [
                'verificaElAl' => $verificaElAl,
                'elclass' => ($verificaElAl['quant'] > 0) ? 'btn-danger' : 'green-color',
                'elval' => ($verificaElAl['quant'] > 0) ? 'Remover aluno' : 'Inscrever aluno',
            ];
        }

        $responsavelFinanceiro = $aluno->responsavelFinanceiro;
        $respFinNome = $responsavelFinanceiro->pessoa->nome;
        $respFinContatos = '';
        if ($responsavelFinanceiro->telefones) {
            $respFinContatos .= "Tel.: " . $responsavelFinanceiro->telefones->pluck('telefone')->join(' | ');
        }

        if ($responsavelFinanceiro->pessoa->email) {
            !empty($respFinContatos) && $respFinContatos .= ' | ';
            $respFinContatos .= "E-mail:<a href='mailto:{$responsavelFinanceiro->pessoa->email}'>{$responsavelFinanceiro->pessoa->email}</a>";
        }

        if ($aluno->pessoa->estado) {
            $dados['cidades'] = $aluno->pessoa->estado->cidades;
        }

        return $this->platesView('Academico/Aluno/Cadastro', array_merge($dados, [
            'aluno' => $aluno,
            'alunoId' => $alunoId,
            'cliente' => $cliente,
            'documentosEntregues' => $aluno->documentosEntregues,
            'eletivas' => $eletivas,
            'emails' => $emails,
            'fotoUrl' => $dados['fotoUrl'],
            'matricula' => $matricula,
            'matriculaId' => $matriculaId,
            'periodoPadraoMatricula' => $periodoPadraoMatricula,
            'periodosLetivos' => $periodosLetivos,
            'respFinNome' => $respFinNome,
            'respFinContatos' => $respFinContatos,
            'rowA' => $rowA,
            'rowFin' => $rowFin,
            'rowTurma' => $rowTurma,
            'rowUnidade' => $rowUnidade,
        ], $rowA));
    }

    private static function verificaInscritoEletiva($idaluno, $nummatricula, $ideletiva, $idanoletivo)
    {
        $queryel = "SELECT * FROM alunos_eletivas WHERE idanoletivo=$idanoletivo AND idaluno=$idaluno AND ideletiva=$ideletiva AND nummatricula=$nummatricula";
        $resultel = mysql_query($queryel);
        $rel = mysql_fetch_array($resultel, MYSQL_ASSOC);

        $info = ['obs' => $rel['obs'], 'quant' => mysql_num_rows($resultel)];

        return $info;
    }
}
