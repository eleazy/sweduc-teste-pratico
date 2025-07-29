<?php

/*
    ESTE É O DOCUMENTO MODEL DE MENSAGENS ENTRE A ESCOLA E O ALUNO
    ESTE GERENCIA A COMUNICAÇÃO ENTRE O CONTROLLER E O BANCO DE DADOS
*/


namespace App\Model;

use App\Model\DbConnect;

/**
 * @deprecated Utilizando estrutura de modelo antiga
 */
class MensagensAlunos
{
    public $conn;

    // NOME DA TABELA NO BANCO
    public $tabelaMensagem = "mensagens_institucionais";
    public $tabelaMensagemPredefinida = "mensagens_predefinidas";
    public $tabelaanoletivo = "anoletivo";
    public $tabelaUnidades = "unidades";
    public $tabelaCursos = "cursos";
    public $tabelaAlunos = "alunos";
    public $tabelaPessoas = "pessoas";

    public $resultadoporpagina = 25;

    public function __construct()
    {
        $db = new DbConnect();
        $this->conn = $db->connect();
    }


    public function listaResponsaveisQueLeramAMensagem($idMensagem)
    {

        $query = "SELECT pessoas.nome, responsaveis.ultima_mensagem_lida
        FROM responsaveis
        JOIN pessoas ON responsaveis.idpessoa = pessoas.id
        JOIN mensagens_institucionais ON responsaveis.ultima_mensagem_lida > mensagens_institucionais.criado_em
        JOIN alunos ON responsaveis.idaluno = alunos.id
        WHERE mensagens_institucionais.id = " . $idMensagem ;

        $pdo = $this->conn->prepare($query);

        $pdo->execute();

        $results = $pdo->fetchAll();

        return $results;
    }

    public function buscaResponsaveisQueLeramAMensagemEmMassa($idmensagem, $nomeASerBuscado)
    {

        $query = "SELECT unidade_id, curso_id, serie_id, turma_id, ano_letivo_id
        FROM mensagens_institucionais
        WHERE id = " . $idmensagem ;

        $pdo = $this->conn->prepare($query);
        $pdo->execute();
        $results = $pdo->fetch();

        $unidade = "";
        $curso = "";
        $serie = "";
        $turma = "";

        $anoLetivo = $results['ano_letivo_id'];



        if ($results['unidade_id'] != null) {
            $unidade = " AND U.id = " . $results['unidade_id'];
        }


        if ($results['curso_id'] != null) {
            $curso = "  AND C.idunidade = " . $results['unidade_id'];
        }


        if ($results['serie_id'] != null) {
            $serie = " AND S.idcurso = " . $results['curso_id'];
        }


        if ($results['turma_id'] != null) {
            $turma = " AND T.idserie = " . $results['serie_id'];
        }




        $query = "SELECT PR.nome AS nomeresponsavel, PA.nome AS nomealuno, R.ultima_mensagem_lida,
        T.turma,
        S.serie,
        C.curso,
        U.unidade,
        CASE WHEN EXISTS (SELECT 1 FROM mensagens_institucionais MI WHERE MI.id = " . $idmensagem . " AND R.ultima_mensagem_lida > MI.criado_em) THEN 1 ELSE 0 END AS visualizado
        FROM responsaveis R
        LEFT JOIN alunos A ON R.idaluno = A.id
        LEFT JOIN pessoas PR ON R.idpessoa = PR.id
        LEFT JOIN pessoas PA ON A.idpessoa = PA.id
        INNER JOIN alunos_matriculas AM ON A.id = AM.idaluno AND AM.anoletivomatricula = " . $anoLetivo . "
        INNER JOIN turmas T ON AM.turmamatricula = T.id " . $turma . "
        INNER JOIN series S ON T.idserie = S.id " . $serie . "
        INNER JOIN cursos C ON S.idcurso = C.id " . $curso . "
        INNER JOIN unidades U ON C.idunidade = U.id " . $unidade . "
        WHERE PR.nome LIKE '%" . $nomeASerBuscado . "%'
        LIMIT 20"
        ;


        $pdo = $this->conn->prepare($query);

        $pdo->execute();

        $results = $pdo->fetchAll();
        return $results;
    }

    //  QUANDO CLICA PARA VER DETALHES DA MENSAGEM
    public function detalhesEnvioMensagem($idmensagem)
    {

        $query = "SELECT
                F.idpessoa,
                F.professor,
                P.nome,
                D.departamento,
                U.unidade
            FROM
                funcionarios F
            LEFT JOIN pessoas P ON (P.id = F.idpessoa)
            LEFT JOIN mensagens_institucionais M ON (F.id =  M.enviado_por_funcionario_id)
            LEFT JOIN departamentos D ON (F.iddepartamento = D.id)
            LEFT JOIN unidades U ON (F.idunidade = U.id)
            WHERE M.id = " . $idmensagem;
        $pdo = $this->conn->prepare($query);

        $pdo->execute();

        $results = $pdo->fetch();

        return $results;
        //  RETORNO ESPERADO
        //  idpessoa | professor | nome | departamento | unidade
    }


    //  USADO NO CAMPO DE BUSCA DAS MENSAGENS
    public function buscarIdFuncIdUnidadeNomeUnidade($idpessoa)
    {
        $query = "
        SELECT
            funcionarios.idpessoa,
            funcionarios.professor,
            pessoas.nome,
            departamentos.departamento,
            unidades.unidade

        FROM
            pessoas

        INNER JOIN funcionarios ON funcionarios.idpessoa = pessoas.id
        INNER JOIN departamentos ON funcionarios.iddepartamento = departamentos.id
        INNER JOIN unidades ON funcionarios.idunidade = unidades.id

        WHERE
            funcionarios.idpessoa=" . $idpessoa . "
    ";

        $pdo = $this->conn->prepare($query);

        $pdo->execute();

        $results = $pdo->fetchAll();

        return $results;
        //  RETORNO ESPERADO
        //  idpessoa | professor | nome | departamento | unidade
    }

    //  USADO NO CAMPO DE BUSCA DAS MENSAGENS
    public function selecionarTodosAnosLetivos()
    {

        $query = "SELECT * FROM " . $this->tabelaanoletivo . " ORDER BY anoletivo ASC";

        $pdo = $this->conn->prepare($query);

        $pdo->execute();

        $results = $pdo->fetchAll();

        return $results;
    }

    //  USADO NO CAMPO DE BUSCA DAS MENSAGENS
    public function selecionarTodasAsUnidades()
    {
        $query = "SELECT * FROM " . $this->tabelaUnidades . " ORDER BY unidade ASC";

        $pdo = $this->conn->prepare($query);

        $pdo->execute();

        $results = $pdo->fetchAll();

        return $results;
    }

    //  ###     NOVAS FUNÇÕES SIMPLIFICADAS     ###

    //  BUSCAR INDIVIDUAL GENÉRICO      #NOVAS FUNÇÕES
    public function mensagensIndividuais(
        $paginaAtual,
        $idanoletivo,
        $idunidadebusca,
        $idcurso,
        $idserie,
        $idturma,
        $databuscainicio,
        $databuscafinal,
        $nomebuscar,
        $idaluno = null
    ) {
        //  LIMITA O RETORNO PARA CADA PÁGINA
        if ($paginaAtual == 1) {
            $resultadoInicio = 0;
            $resultadoFinal = $this->resultadoporpagina - 1;
        } else {
            $resultadoInicio = ($paginaAtual - 1) * $this->resultadoporpagina;
            $resultadoFinal = $resultadoInicio + $this->resultadoporpagina;
        }

        $query = "
            Select M.*, P.nome,
            DATE_FORMAT(M.criado_em,'%d/%m/%Y - %H:%i') AS dataFormatada
            FROM " . $this->tabelaMensagem . " M
            INNER JOIN alunos A ON (M.aluno_id = A.id)
            INNER JOIN pessoas P ON (A.idpessoa = P.id)
        ";

        $colocaAND = 0;
        if ($idanoletivo != "todos" && $idanoletivo != "") {
            $query .= " WHERE (ano_letivo_id = " . $idanoletivo . " OR ano_letivo_id IS NULL)\n";
            $colocaAND = 1;
        }

        if ($idunidadebusca != "todos" && $idunidadebusca != "") {
            if ($colocaAND == 1) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
            }
            $query .= " (unidade_id = " . $idunidadebusca . " OR unidade_id IS NULL)\n";

            if ($idcurso != "todos" && $idcurso != "") {
                $query .= " AND (curso_id = " . $idcurso . " OR curso_id IS NULL)\n";
                if ($idserie != "todos" && $idserie != "") {
                    $query .= " AND (serie_id = " . $idserie . " OR serie_id IS NULL)\n";
                    if ($idturma != "todos" && $idturma != "") {
                        $query .= " AND (turma_id = " . $idturma . " OR turma_id IS NULL)\n";
                    }
                }
            }
            $colocaAND = 1;
        }

        if ($idaluno != null) {
            if ($colocaAND == 1) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
            }
            $query .= " aluno_id = " . $idaluno;
        }

        if (!empty($databuscainicio) && !empty($databuscafinal)) {
            if ($colocaAND == 1) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
                $colocaAND = 1;
            }
            $query .= " criado_em BETWEEN '" . $databuscainicio . "' AND DATE_ADD('" . $databuscafinal . "', INTERVAL 1 DAY)";
        }

        if ($nomebuscar != "" || $nomebuscar != null) {
            if ($colocaAND == 1) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
            }
            $query .= " nome LIKE '%" . $nomebuscar . "%'";
        }

        $query .= " ORDER BY id DESC ";
        $query .= " LIMIT " . $resultadoInicio . "," . $resultadoFinal;

        $pdo = $this->conn->prepare($query);
        $pdo->execute();

        $results = $pdo->fetchAll();

        return $results;
    }

    //  BUSCAR MASSA GENÉRICO           #NOVAS FUNÇÕES
    public function mensagensMassa(
        $paginaAtual,
        $idanoletivo,
        $idunidadebusca,
        $idcurso,
        $idserie,
        $idturma,
        $databuscainicio,
        $databuscafinal
    ) {
        //  LIMITA O RETORNO PARA CADA PÁGINA
        if ($paginaAtual == 1) {
            $resultadoInicio = 0;
            $resultadoFinal = $this->resultadoporpagina - 1;
        } else {
            $resultadoInicio = ($paginaAtual - 1) * $this->resultadoporpagina;
            $resultadoFinal = $resultadoInicio + $this->resultadoporpagina;
        }

        $query = "
            SELECT M.*, U.unidade, C.curso, S.serie, T.turma,
            DATE_FORMAT(M.criado_em,'%d/%m/%Y - %H:%i') AS dataFormatada
            FROM " . $this->tabelaMensagem . " M
            LEFT JOIN unidades U ON (U.id = M.unidade_id)
            LEFT JOIN cursos C ON (C.id = M.curso_id)
            LEFT JOIN series S ON (S.id = M.serie_id)
            LEFT JOIN turmas T ON (T.id = M.turma_id)
            WHERE M.matricula_id IS NULL

        ";


        if ($idanoletivo != "todos" && $idanoletivo != "") {
            $query .= " AND (M.ano_letivo_id = " . $idanoletivo . " OR M.ano_letivo_id IS NULL)\n";
        }
        if ($idunidadebusca != "todos" && $idunidadebusca != "") {
            $query .= " AND (M.unidade_id = " . $idunidadebusca . " OR M.unidade_id IS NULL)\n";
        }
        if ($idcurso != "todos" && $idcurso != "") {
            $query .= " AND (M.curso_id = " . $idcurso . " OR M.curso_id IS NULL)\n";
        }
        if ($idserie != "todos" && $idserie != "") {
            $query .= " AND (M.serie_id = " . $idserie . " OR M.serie_id IS NULL)\n";
        }
        if ($idturma != "todos" && $idturma != "") {
            $query .= " AND (M.turma_id = " . $idturma . " OR M.turma_id IS NULL)\n";
        }


        if (!empty($databuscainicio) && !empty($databuscafinal)) {
            $query .= " AND criado_em BETWEEN '" . $databuscainicio . "' AND DATE_ADD('" . $databuscafinal . "', INTERVAL 1 DAY)\n";
        }

        $query .= " ORDER BY id DESC ";
        $query .= " LIMIT " . $resultadoInicio . "," . $resultadoFinal;


        $pdo = $this->conn->prepare($query);
        $pdo->execute();

        $results = $pdo->fetchAll();

        return $results;
    }

    //  CONTA O RESULTADO INDIVIDUAL   #NOVAS FUNÇÕES
    public function quantidadeDeResultadosIndividual(
        $idanoletivo,
        $idunidadebusca,
        $idcurso,
        $idserie,
        $idturma,
        $databuscainicio,
        $databuscafinal,
        $nomebuscar,
        $idaluno = null
    ) {

        $query = "
            SELECT id
            FROM " . $this->tabelaMensagem . " M
            ";

        if ($idaluno  != null) {
            $query .= " WHERE aluno_id = " . $idaluno ;
        } else {
            $query .= " WHERE matricula_id IS NOT NULL ";
        }
        if ($idanoletivo != "todos" && $idanoletivo != "") {
            $query .= " AND (ano_letivo_id = " . $idanoletivo . " OR ano_letivo_id IS NULL)\n";
        }

        if ($idunidadebusca != "todos" && $idunidadebusca != "") {
            $query .= " AND (unidade_id = " . $idunidadebusca . " OR unidade_id IS NULL)\n";

            if ($idcurso != "todos" && $idcurso != "") {
                $query .= " AND (curso_id = " . $idcurso . " OR curso_id IS NULL)\n";
                if ($idserie != "todos" && $idserie != "") {
                    $query .= " AND (serie_id = " . $idserie . " OR serie_id IS NULL)\n";
                    if ($idturma != "todos" && $idturma != "") {
                        $query .= " AND (turma_id = " . $idturma . " OR turma_id IS NULL)\n";
                    }
                }
            }
        }

        if (!empty($databuscainicio) && !empty($databuscafinal)) {
            $query .= " AND criado_em BETWEEN '" . $databuscainicio . "' AND DATE_ADD('" . $databuscafinal . "', INTERVAL 1 DAY)";
        }

        if (!empty($nomebuscar) && $nomebuscar != "todos") {
            $query .= " AND nome LIKE '%" . $nomebuscar . "%'";
        }

        $pdo = $this->conn->prepare($query);
        $pdo->execute();

        $results = $pdo->fetchAll();

        $num_rows = is_countable($results) ? count($results) : 0;

        return $num_rows;
    }

    //  CONTA O RESULTADO EM MASSA     #NOVAS FUNÇÕES
    public function quantidadeDeResultadosMassa(
        $idanoletivo,
        $idunidadebusca,
        $idcurso,
        $idserie,
        $idturma,
        $databuscainicio,
        $databuscafinal
    ) {

        $query = "
            SELECT COUNT(*) AS quantresbusca
            FROM " . $this->tabelaMensagem . "
            WHERE matricula_id IS NULL
        ";

        if ($idanoletivo != "todos" && $idanoletivo != "") {
            $query .= " AND (ano_letivo_id = " . $idanoletivo . " OR ano_letivo_id IS NULL)\n";
        }

        if ($idunidadebusca != "todos" && $idunidadebusca != "") {
            $query .= " AND (unidade_id = " . $idunidadebusca . " OR unidade_id IS NULL)\n";
            if ($idcurso != "todos" && $idcurso != "") {
                $query .= " AND (curso_id = " . $idcurso . " OR curso_id IS NULL)\n";
                if ($idserie != "todos" && $idserie != "") {
                    $query .= " AND (serie_id = " . $idserie . " OR serie_id IS NULL)\n";
                    if ($idturma != "todos" && $idturma != "") {
                        $query .= " AND (turma_id = " . $idturma . " OR turma_id IS NULL)\n";
                    }
                }
            }
        }

        if ($databuscainicio != "" && $databuscafinal != "") {
            $query .= " AND criado_em BETWEEN '" . $databuscainicio . "' AND DATE_ADD('" . $databuscafinal . "', INTERVAL 1 DAY)";
        }

        $pdo = $this->conn->prepare($query);
        $pdo->execute();

        $results = $pdo->fetch();

        return $results;
    }

    //  REGISTRA A MENSAGEM PREDEFINIDA
    public function salvarMensagemPredefinida($assunto)
    {

        $query = "INSERT
            INTO " . $this->tabelaMensagemPredefinida . " (
                assunto
            )
            VALUES (
                '" . $assunto . "')"
            ;

        $pdo = $this->conn->prepare($query);
        $pdo->execute();
    }

    public function excluirMensagem($idMensagem)
    {
        $query = "DELETE from " . $this->tabelaMensagem . "
                    WHERE id = " . $idMensagem ;
        $pdo = $this->conn->prepare($query);
        $pdo->execute();
    }

    public function carregarAnexos($idMensagem)
    {
        $query = "SELECT anexos FROM " . $this->tabelaMensagem . " WHERE id = " . $idMensagem ;
        $pdo = $this->conn->prepare($query);
        $pdo->execute();
        $results = $pdo->fetch();

        if ($results['anexos'] != null && $results['anexos'] != "") {
            return $results['anexos'];
        }

        return null;
    }
}
