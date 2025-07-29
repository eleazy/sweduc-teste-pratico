<?php

declare(strict_types=1);

namespace App\Academico;

use App\Academico\Exception\CalculoDeMediaException;
use App\Academico\Model\Media;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Capsule\Manager as DB;

use function App\Academico\Boletim\arredonda05;
use function App\Academico\Boletim\mediaNaoNulos;

require_once __DIR__ . '/Boletim/HelperNotas.php';

class CalculoMediaService
{
    protected static array $computed = [];

    public function __construct(protected int $notasDecimais, protected ?LoggerInterface $logger = null)
    {
    }

    /**
     * Retorna média calculada a partir do id de aluno e da média
     *
     * @param bool $emitirExcecao Emitir exceção quando a fórmula estiver com problemas de sintaxe
     * @return string media calculada e formatada
     */
    public function calcularMedia(int $alunoId, ?Media $media, bool $emitirExcecao = false): string
    {
        if (!$media) {
            return "";
        }

        if (!empty($this->getCache($alunoId, $media->id))) {
            return $this->getCache($alunoId, $media->id);
        }

        $calculo = '';

        if ($media->formula === "") {
            return $calculo;
        }

        try {
            $calculo = $this->resolverFormula($media, $alunoId);
        } catch (\Throwable $th) {
            $this->handleThrowable($emitirExcecao, $th, $media, $alunoId);
        }

        $this->setCache($alunoId, $media->id, $calculo);
        return $calculo;
    }

    protected function resolverVariavel(
        int $alunoId,
        string $formula,
        Media $media
    ): ?string {
        // Padrão de média #M123@
        if ($mediaId = $this->validaPadraoMedia($formula)) {
            if (!empty($this->getCache($alunoId, $mediaId))) {
                return $this->getCache($alunoId, $mediaId);
            }

            return $this->calcularMedia(
                $alunoId,
                $this->getMedia((int) $mediaId)
            );
        }

        // Padrão de avaliação #A123@
        if ($avaliacaoId = $this->validaPadraoAvaliacao($formula)) {
            return $this->getNota($alunoId, (int) $avaliacaoId, $media);
        }

        // Padrão de período #P123@
        if (stripos($formula[0], 'P') === 0) {
            // $id_periodo = substr($p, 1);
            return null;
        }

        // Padrão de disciplina #D123@
        if (stripos($formula[0], 'D') === 0) {
            // $id_disciplina = substr($p, 1);
            return null;
        }

        return null;
    }

    protected function resolverFormula($media, $alunoId)
    {
        $calculo = '';
        $variaveisNaoEncontradas = [];

        // Resultado 1: Carrega outras variáveis
        $resultado1 = preg_replace_callback(
            "/#([^@]+)@/",
            function ($match) use ($alunoId, $media, &$variaveisNaoEncontradas) {
                $nota = $this->resolverVariavel($alunoId, $match[1], $media);

                if ($this->validaPadraoMedia($match[1]) && ($nota === null || $nota === '')) {
                    $variaveisNaoEncontradas[] = $match[1];
                }

                return !empty($nota) ? $nota : 0;
            },
            $media->formula
        );

        // Resultado 2: Executa funções
        $resultado2 = $this->resolverFuncoes($resultado1);

        $todasNotasEncontradas = count($variaveisNaoEncontradas) === 0;

        if ($todasNotasEncontradas || $media->periodo->calculaMedia) {
            $calculo = eval("return number_format($resultado2, {$this->notasDecimais}, '.', '');");
        }

        if ($calculo == 0 && $resultado2 === "0") { // "0" == NULO
            return '';
        }
        if ($calculo == 0 && $resultado2 === "0.0") { // "0.0" == 0
            return '0.0';
        }

        return $calculo;
    }

    protected function resolverFuncoes($formula)
    {
        return preg_replace_callback_array([
            '/arredonda05\(([^()]+)\)/' => function ($match) {
                $innerExpression = eval("return " . $match[1] . ";");
                return arredonda05((float) $innerExpression);
            },
            '/mediaNaoNulos\((.+)\)/' => fn($match) => mediaNaoNulos(...explode(',', $match[1])),
        ], $formula);
    }

    protected function handleThrowable($emitirExcecao, $th, $media, $alunoId)
    {
        if ($emitirExcecao) {
            throw new CalculoDeMediaException($th->getMessage(), $media, $alunoId);
        }

        if ($this->logger) {
            $this->logger->error(
                'Erro ao exibir nota. ' . $th->getMessage(),
                [
                    'alunoId' => $alunoId,
                    'mediaId' => $media->id,
                    'formula' => $media->formula
                ]
            );
        }
    }

    protected function setCache(int $alunoId, int $mediaId, string $nota)
    {
        self::$computed[$alunoId . $mediaId] = $nota;
    }

    protected function getCache(int $alunoId, int $mediaId)
    {
        return self::$computed[$alunoId . $mediaId] ?? null;
    }

    protected function getMedia(int $mediaId): ?Media
    {
        return Media::findOrFail($mediaId);
    }

    protected function getNota(int $alunoId, int $avaliacaoId, Media $media): ?string
    {
        $nota = $media->notas
                ->where('idavaliacao', $avaliacaoId)
                ->where('idaluno', $alunoId)
                ->first();

        return $nota ? $nota->nota : null;
    }

    /**
     * Retorna id de média se validação de formato
     * M123 estiver correto
     */
    protected function validaPadraoMedia(string $variavel): ?int
    {
        return $this->validaPadraoLetraID('M', $variavel);
    }

    /**
     * Retorna id de avaliação se validação de formato
     * A123 estiver correto
     */
    protected function validaPadraoAvaliacao(string $variavel): ?int
    {
        return $this->validaPadraoLetraID('A', $variavel);
    }

    /**
     * Retorna id de avaliação se validação de formato
     * X123 estiver correto sendo X a $letra
     *
     * @param string $letra identificador do tipo de padrão
     * @param string $variavel padrão a ser validado
     * @return integer|null id resultante ou nulo em caso de não ser válido
     */
    protected function validaPadraoLetraID(string $letra, string $variavel): ?int
    {
        if (
            stripos($variavel[0], $letra) === 0 &&
            $id = filter_var(substr($variavel, 1), FILTER_VALIDATE_INT)
        ) {
            return $id;
        }

        return null;
    }
}
