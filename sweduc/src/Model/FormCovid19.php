<?php

namespace App\Model;

class FormCovid19
{
    protected $attributes;
    protected $db;
    public static $sintomasExtenso = [
        'aluno_sintomas_coriza' => 'Coriza',
        'aluno_sintomas_nariz_entupido' => 'Nariz entupido',
        'aluno_sintomas_cansaco' => 'Cansaço',
        'aluno_sintomas_tosse' => 'Tosse',
        'aluno_sintomas_dor_de_cabeca' => 'Dor de cabeça',
        'aluno_sintomas_dores_no_corpo_ou_mal_estar' => 'Dores no corpo ou mal estar',
        'aluno_sintomas_dor_de_garganta' => 'Dor de garganta',
        'aluno_sintomas_diarreia_ou_dores_abdominais' => 'Diarréia ou dores abdominais',
        'aluno_sintomas_perda_de_olfato' => 'Perda de olfato',
        'aluno_sintomas_perda_do_paladar' => 'Perda do paladar',

        'moradores_sintomas_coriza' => 'Coriza',
        'moradores_sintomas_nariz_entupido' => 'Nariz entupido',
        'moradores_sintomas_cansaco' => 'Cansaço',
        'moradores_sintomas_tosse' => 'Tosse',
        'moradores_sintomas_dor_de_cabeca' => 'Dor de cabeça',
        'moradores_sintomas_dores_no_corpo_ou_mal_estar' => 'Dores no corpo ou mal estar',
        'moradores_sintomas_dor_de_garganta' => 'Dor de garganta',
        'moradores_sintomas_diarreia_ou_dores_abdominais' => 'Diarréia ou dores abdominais',
        'moradores_sintomas_perda_de_olfato' => 'Perda de olfato',
        'moradores_sintomas_perda_do_paladar' => 'Perda do paladar',
    ];

    public function __construct()
    {
        $db = new DbConnect();
        $this->db = $db->connect();
    }

    public function __set($name, $value)
    {
        if (!isset($this->attributes)) {
            $this->attributes = [];
        }

        $this->attributes[$name] = $value;
    }

    public function __get($name)
    {
        return $this->attributes[$name];
    }

    public function setAttributes($attributes)
    {
        if (!is_array($attributes)) {
            throw new \Exception("The attributes param is not an array!");
        }

        foreach ($attributes as $key => $value) {
            // Transform checkbox default values to true
            $newValue = $value == 'on' ? true : $value;

            // Transform kebab-case to snake_case
            $newKey = implode('_', explode('-', $key));

            $this->$newKey = $newValue;
        }
    }

    /**
     * Retorna array com a lista de sintomas apresentados
     *
     * @return Array Lista de sintomas
     */
    public function alunoListaSintomas()
    {
        $filtraSintomas = $this->alunoFiltrarSintomas();
        return array_map(fn($sintoma) => self::$sintomasExtenso[$sintoma], $filtraSintomas);
    }

    public function alunoQuantidadeSintomas()
    {
        $filtraSintomas = $this->alunoFiltrarSintomas();
        return count($filtraSintomas) ?: 0;
    }

    private function alunoFiltrarSintomas()
    {
        $filtraPositivos = array_filter($this->attributes);
        return array_filter(array_keys($filtraPositivos), fn($attribute) => stripos($attribute, 'aluno_sintomas') !== false);
    }

    /**
     * Retorna array com a lista de sintomas apresentados
     *
     * @return Array Lista de sintomas
     */
    public function moradoresListaSintomas()
    {
        $filtraSintomas = $this->moradoresFiltrarSintomas();
        return array_map(fn($sintoma) => self::$sintomasExtenso[$sintoma], $filtraSintomas);
    }

    public function moradoresQuantidadeSintomas()
    {
        $filtraSintomas = $this->moradoresFiltrarSintomas();
        return count($filtraSintomas) ?: 0;
    }

    private function moradoresFiltrarSintomas()
    {
        $filtraPositivos = array_filter($this->attributes);
        return array_filter(array_keys($filtraPositivos), fn($attribute) => stripos($attribute, 'moradores_sintomas') !== false);
    }

    public function save()
    {
        $this->attributes['realizado_por_usuario_id'] = $_SESSION['id_usuario'];

        $attributesKeys = array_keys($this->attributes);
        $colNames = implode(', ', $attributesKeys);
        $colValuesNames = ':' . implode(', :', $attributesKeys);

        $this->db
            ->prepare("INSERT INTO form_covid ($colNames) VALUES ($colValuesNames)")
            ->execute($this->attributes);
    }

    public static function buscarPorAlunoId($alunoId)
    {
        $db = (new DbConnect())->connect();
        $stmt = $db->prepare(
            "SELECT
                form_covid.*,
                DATE_FORMAT(form_covid.criado_em, '%d/%m/%Y') as form_criado_em
            FROM
                form_covid
            WHERE
                aluno_id = :alunoId
            ORDER BY id
            LIMIT 1;"
        );

        $stmt->execute([ 'alunoId' => $alunoId ]);
        return $stmt->fetchObject(self::class);
    }

    public static function buscarPorId($id)
    {
        $db = (new DbConnect())->connect();
        $stmt = $db->prepare(
            "SELECT
                form_covid.*,
                DATE_FORMAT(form_covid.criado_em, '%d/%m/%Y') as form_criado_em
            FROM
                form_covid
            WHERE
                id = :id
            LIMIT 1;"
        );

        $stmt->execute([ 'id' => $id ]);
        return $stmt->fetchObject(self::class);
    }

    public static function listar($paramsBusca)
    {
        $busca = [];

        if (!empty($paramsBusca['unidade_id']) && (int) $paramsBusca['unidade_id']) {
            $busca[] = "alunos_matriculas.idunidade = '{$paramsBusca['unidade_id']}'";
        }

        if (!empty($paramsBusca['curso_id']) && (int) $paramsBusca['curso_id']) {
            $busca[] = "series.idcurso = '{$paramsBusca['curso_id']}'";
        }

        if (!empty($paramsBusca['serie_id']) && (int) $paramsBusca['serie_id']) {
            $busca[] = "turmas.idserie = '{$paramsBusca['serie_id']}'";
        }

        if (!empty($paramsBusca['turma_id']) && (int) $paramsBusca['turma_id']) {
            $busca[] = "turmamatricula = {$paramsBusca['turma_id']}";
        }

        if (!empty($paramsBusca['periodo_de'])) {
            $busca[] = "form_covid.atualizado_em >= '{$paramsBusca['periodo_de']}'";
        }

        if (!empty($paramsBusca['periodo_ate'])) {
            $busca[] = "form_covid.atualizado_em <= DATE_ADD('{$paramsBusca['periodo_ate']}', INTERVAL 1 DAY)";
        }

        $where = count($busca) ? 'WHERE ' . implode(' AND ', $busca) : '';

        $somenteUltimaResposta = '';
        if (true) {
            $somenteUltimaResposta = "
                JOIN
                (
                    SELECT MAX(id) as max_id, aluno_id
                    FROM form_covid
                    GROUP BY aluno_id
                ) max_form ON max_form.max_id = form_covid.id
            ";
        }


        $db = (new DbConnect())->connect();
        $forms = [];

        $stmt = $db->prepare(
            "SELECT
                aluno_pessoa.nome as aluno_nome,
                respondido_por_pessoa.nome as respondido_por_nome,
                form_covid.*,
                DATE_FORMAT(form_covid.criado_em, '%d/%m/%Y') as form_criado_em,
                max_mat.aluno_matricula_id,
                alunos_matriculas.turmamatricula
            FROM
                form_covid
            JOIN
                alunos ON alunos.id = form_covid.aluno_id
            JOIN
                pessoas as aluno_pessoa ON aluno_pessoa.id = alunos.idpessoa
            JOIN
                usuarios as criador_usuario ON criador_usuario.id = form_covid.realizado_por_usuario_id
            JOIN
                pessoas as respondido_por_pessoa ON respondido_por_pessoa.id = criador_usuario.idpessoa
            JOIN
                (
                    SELECT MAX(id) as aluno_matricula_id, idaluno
                    FROM alunos_matriculas
                    GROUP BY idaluno
                ) max_mat ON alunos.id = idaluno
            JOIN
                alunos_matriculas ON alunos_matriculas.id = max_mat.aluno_matricula_id
            JOIN
                turmas ON turmas.id = alunos_matriculas.turmamatricula
            JOIN
                series ON series.id = turmas.idserie
            $somenteUltimaResposta
            $where
            "
        );
        $stmt->execute();

        while ($result = $stmt->fetchObject(self::class)) {
            $forms[] = $result;
        }

        return $forms;
    }
}
