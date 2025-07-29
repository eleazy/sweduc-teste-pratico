<?php

declare(strict_types=1);

namespace App\Academico\Exception;

use App\Academico\Model\Aluno;
use App\Academico\Model\Media;

class CalculoDeMediaException extends \Exception
{
    private array $contexto = [];

    public function __construct(string $message, Media $media, int $alunoId)
    {
        $aluno = Aluno::find($alunoId)->pessoa->nome;
        $mediaId = $media->id;
        $disciplina = $media->grade->disciplina->disciplina;
        $periodo = $media->periodo->periodo;
        $formula = $media->formula;

        $this->contexto = [
            'Número da média' => $mediaId,
            'Disciplina' => $disciplina,
            'Período' => $periodo,
            'Fórmula' => $formula,
            'Mensagem de erro' => $message
        ];

        $msg = "Erro de média #$mediaId em aluno ($aluno)";
        parent::__construct($msg);
    }

    public function getContexto()
    {
        return $this->contexto;
    }
}
