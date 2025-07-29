<?php

/**
* @author Fabio Souza
* Arquivo criado com diversas funções para auxiliar no desenvolvimento
**/

/**
 * Recupera variáveis de sessão
 *
 * @param String $param_name
 * @return String
 */
function get_session($param_name)
{
    return (empty($_SESSION[$param_name])) ? '' : $_SESSION[$param_name];
}

/**
 * Grava uma Variavel de Sessao
 *
 * @param String $param_name
 * @param String $param_value
 *
 */
function set_session($param_name, $param_value)
{
    $_SESSION[$param_name] = $param_value;
}

/**
 * Funcao para recuperar as variaveis GET e POST
 *
 * @param String $param_name
 * @return String
 *
 */
function get_param($param_name)
{
    $var = (!empty($_REQUEST[$param_name])) ? $_REQUEST[$param_name] : "";

    if (!is_array($var)) {
        $var = trim($var);
    }

    if (empty($var)) {
        return null;
    } else {
        return $var;
    }
}

/**
 * Função de MSG em JavaScript
 *
 * @param string $msg
 */
function alert($msg)
{
    $msg = addslashes($msg);
    echo "<script>alert('$msg');</script>";
}


/**
 * Redirecionador de pagina via javascript
 *
 * @param string $url
 */
function redirect($url)
{

    echo "<script language='JavaScript'>";
    echo "location='$url';";
    echo "</SCRIPT>";
}

/**
* Gera uma senha aleatoria com a quantidade de digitos estipulada no parametro
* @param int $tam - qtd de digitos para gerar a senha
*/
function gera_senha($tam)
{
    $CaracteresAceitos = 'abcdxywzABCDZYWZ0123456789';

    $max = strlen($CaracteresAceitos) - 1;

    $password = null;

    for ($i = 0; $i < $tam; $i++) {
        $password .= $CaracteresAceitos[random_int(0, $max)];
    }

    return($password);
}

// Polyfill para função removida money_format
if (!function_exists('money_format')) {
    function money_format($format, $valor, $moeda = 'BRL')
    {
        global $_GLOBAL;
        $_GLOBAL['currencyFormatter'] ??= new NumberFormatter('pt_BR', NumberFormatter::CURRENCY);
        $valorAsFloat = floatval($valor);
        return $_GLOBAL['currencyFormatter']->formatCurrency($valorAsFloat, $moeda);
    }
}
