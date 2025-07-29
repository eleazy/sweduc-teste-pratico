<?php

/*
    Classe para conexao em MySQL
*/

/*********************************************
 * Classes para acesso � camada de dados
 * por Marcelo Rezende (malvre@gmail.com)
 * atualizado em 14/11/2002 -> suporte a navegacao de registros
 *
 *
 * Classe......: db
 * M�todos.....: db("tipodb") construtor, *experimental, usar sem par�metros
 *               open(banco, host, user, password)
 *                lock(tabela, modo)
 *               unlock()
 *               error()
 *               close()
 *               execute(sql)
 *               begin()
 *               commit()
 *               rollback()
 *
 * Classe......: query
 * M�todos.....: query(db, sql, numero_pagina, tamanho_pagina) -> construtor
 *               getrow()
 *               field(campo)
 *               fieldname([numerodocampo] ou [nomedocampo])
 *               firstrow()
 *               free()
 *               numrows()
 *               totalpages()
 *
 **************************/

/**
 * Conexão com o banco
 *
 * @deprecated version
 */
class db
{
    public $connect_id;
    public $type = "mysql";

    //----- construtor, par�metro default � "mysql"
    public function __construct($database_type = "mysql")
    {
    }

    //----- executa uma express�o SQL
    public function execute($strSQL)
    {

        @mysql_query($strSQL, $this->connect_id);
        return @mysql_insert_id($this->connect_id);
    }

    //----- begin transaction
    public function begin()
    {
        @mysql_query("BEGIN", $this->connect_id);
    }

    //----- commit transaction
    public function commit()
    {
        @mysql_query("COMMIT", $this->connect_id);
    }

    //----- rollback transaction
    public function rollback()
    {
        @mysql_query("ROLLBACK", $this->connect_id);
    }

    //----- abertura do banco de dados
    //----- configure a conex�o conforme suas necessidades
    public function open(?string $database = null, ?string $host = null, ?string $user = null, ?string $password = null)
    {
        $database ??= $_SERVER['DB_DATABASE'];
        $host ??= $_SERVER['DB_HOST'];
        $user ??= $_SERVER['DB_USER'];
        $password ??= $_SERVER['DB_PASSWORD'];

        $this->connect_id = @mysql_connect($host, $user, $password);

        if ($this->connect_id) {
            $result = @mysql_select_db($database);
            if (!$result) {
                @mysql_close($this->connect_id);
                $this->connect_id = $result;
            } else {
                @mysql_query("SET CHARACTER SET utf8", $this->connect_id);
                @mysql_query("SET NAMES 'utf8'", $this->connect_id);
            }
        }
        return $this->connect_id;
    }

    //----- efetua lock na tabela
    public function lock($table, $mode = "write")
    {
        $query = new query($this, "lock tables $table $mode");
        $result = $query->result;
        return $result;
    }

    //----- efetua unlock nas tabelas em lock
    public function unlock()
    {
        $query = new query($this, "unlock tables");
        $result = $query->result;
        return $result;
    }

    //----- retorna mensagem de erro
    public function error($string_erro = "")
    {
        //----- caso ocorra erro, envia mensagem
        if (@mysql_errno($this->connect_id) != 0) {
            @mail(SIS_EMAIL_RESPONSAVEL, "Erro " . date("d-m-Y"), mysql_errno($this->connect_id) . " - " . mysql_error($this->connect_id) . " - " . $string_erro);
        }
        return @mysql_errno($this->connect_id);
    }

    //----- encerra conex�o e todos recorsets abertos
    public function close()
    {
        if ($this->query_id && is_array($this->query_id)) {
            foreach ($this->query_id as $key => $val) {
                @mysql_free_result($val);
            }
        }
    }

    //----- gera pool de recordsets. m�todo privado, n�o utilizar !!!
    public function addquery($query_id)
    {
        $this->query_id[] = $query_id;
    }
}

class query
{
    public $result;
    public $row;
    public $numrows;
    public $totalpages = 0;
    public $totalLista;

    //----- construtor, retorna recordset
    public function __construct(&$db, $query = "", $pagina_inicial = 0, $tamanho_pagina = 0)
    {
        if ($query) {
            if ($this->result) {
                $this->free();
            }
            $this->result = @mysql_query($query, $db->connect_id);
            $this->numrows = @mysql_num_rows($this->result);

            if (($pagina_inicial + $tamanho_pagina) > 0) {
                $this->totalpages = ceil($this->numrows() / $tamanho_pagina);
                $query .= " limit " . ($pagina_inicial - 1) * $tamanho_pagina . ", $tamanho_pagina";
            }
            $this->result = @mysql_query($query, $db->connect_id);
            $db->addquery($this->result);

            // para saber qts estao na listagem exibida
            $this->totalLista = @mysql_num_rows($this->result);
        }
    }

    public function totalpages()
    {
        return $this->totalpages;
    }

    //----- retorna array com os campos e avan�a o registro
    public function getrow()
    {
        if ($this->result) {
            $this->row = @mysql_fetch_array($this->result);
        } else {
            $this->row = 0;
        }
        return $this->row;
    }

    //----- retorna o valor do campo
    public function field($field)
    {
        if (get_magic_quotes_gpc()) {
            $result = stripslashes($this->row[$field]);
        } else {
            $result = $this->row[$field];
        }
        return $result;
    }

    //----- retorna o nome do campo
    public function fieldname($fieldnum)
    {
        return @mysql_field_name($this->result, $fieldnum);
    }

    //----- retorna primeira linha do recordset
    public function firstrow()
    {
        $result = @mysql_data_seek($this->result, 0);
        if ($result) {
            $result = $this->getrow();
        }
        return $this->row;
    }

    //----- fecha o recordset
    public function free()
    {
        return @mysql_free_result($this->result);
    }

    //----- retorna a quantidade de registros
    public function numrows()
    {
        return $this->numrows;
    }

    public function getTotalLista()
    {
        return $this->totalLista;
    }
}
