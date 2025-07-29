<?php

if (!function_exists('_validar')) {
    /**
     * Simplifica a validação de condições do script
     * e retornar um erro para o usuário caso uma das condições forem verdadeiras.
     *
     * @param condicoes Lista de nomes de erro seguido de suas condições [ 'mensagem de erro' => !$arquivo_salvo  ]
     * @param erroHttp Tipo de código de erro retornado
     */
    function _validar(array $condicoes, $erroHttp)
    {
        $erros = array_keys(array_filter($condicoes));
        if (!empty($erros)) {
            http_response_code($erroHttp);
            echo json_encode(compact('erros'), JSON_THROW_ON_ERROR);
            die();
        }
    }
}
