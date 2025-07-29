<?php

declare(strict_types=1);

namespace App\Framework\ViewRenderer;

use Carbon\Carbon;
use League\Plates\Engine;
use League\Plates\Extension\Asset;
use NumberFormatter;
use RuntimeException;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

class PlatesRenderer implements RendererInterface
{
    protected const DEFAULT_VIEW_DIR = __DIR__ . '/../../View/';
    protected const PUBLIC_DIR = __DIR__ . '/../../../public/';

    protected NumberFormatter $currencyFormatter;
    protected SluggerInterface $slugger;

    protected Engine $engine;

    public function __construct(?string $viewDirectory = null)
    {
        $this->engine = new Engine($viewDirectory ?? self::DEFAULT_VIEW_DIR);

        if ($_ENV['APP_ENV'] === 'production') {
            $this->engine->loadExtension(new Asset(self::PUBLIC_DIR));
        } else {
            $this->engine->registerFunction('asset', fn ($url) => $url);
        }
        $this->engine->registerFunction('eif', function ($content, $statement) {
            if ($statement) {
                return $content;
            }
        });

        $this->engine->registerFunction('checked', function ($statement) {
            if ($statement) {
                return 'checked=\'checked\'';
            }
        });

        $this->engine->registerFunction('selected', function ($statement) {
            if ($statement) {
                return 'selected=\'selected\'';
            }
        });

        $this->engine->registerFunction('disabled', function ($statement) {
            if ($statement) {
                return 'disabled=\'disabled\'';
            }
        });

        $this->engine->registerFunction('dindin', [$this, 'dindin']);

        $this->engine->registerFunction('date', fn($val) => Carbon::parse($val));

        $this->engine->registerFunction('dateLocal', fn($val, $fmt = 'L') => Carbon::parse($val)->locale('pt_BR')->isoFormat($fmt));

        $this->engine->registerFunction('dateTimeLocal', fn($val, $fmt = 'LLL') => Carbon::parse($val)->locale('pt_BR')->isoFormat($fmt));

        $this->engine->registerFunction('slugify', fn ($str) => ($this->slugger ??= new AsciiSlugger())->slug(strtolower($str)));

        $this->engine->registerFunction('pagination', function ($currentPage, $maxPage) {
            $previousPages = array_filter(range($currentPage - 9, $currentPage), fn ($x) => $x > 0 && $x <= $maxPage);
            $nextPages = array_filter(range($currentPage, $currentPage + 9), fn ($x) => $x > 0 && $x <= $maxPage);

            $previousPages = array_reverse(array_slice(array_reverse($previousPages), 0, 6));
            $nextPages = array_slice($nextPages, 0, 12 - count($previousPages));

            $pages = array_unique([...$previousPages, ...$nextPages]);
            return $pages;
        });
    }

    /**
     * Renderiza template plates
     *
     * @param template Nome do template a partir da pasta View
     * @param data Array ou objeto com dados a ser renderizados na página
     *
     * @return string template renderizado
     */
    public function render(string $path, array $data = []): string
    {
        $this->engine->registerFunction('csrfToken', function () use ($data) {
            if (!isset($data['csrfToken'])) {
                throw new RuntimeException('CSRF Token not defined.');
            }

            $csrfToken = $data['csrfToken'];

            return "<input type='hidden' name='csrf-token' value='$csrfToken'>";
        });

        return $this->engine->render($path, $data);
    }

    /**
     * Transforma valor em unidade de moeda
     *
     * @param string|float $valor The numeric currency value.
     * @param string $moeda The 3-letter ISO 4217 currency code indicating the currency to use.
     * @return String representing the formatted currency value, ou FALSE em caso de falha.
     */
    public function dindin(string|float|null $valor, $moeda = 'BRL')
    {
        if (!is_null($valor)) {
            $valor = floatval($valor);
        }

        if (is_null($valor)) {
            $valor = 0;
        }

        if (empty($this->currencyFormatter)) {
            $this->currencyFormatter = new NumberFormatter('pt_BR', NumberFormatter::CURRENCY);
        }

        $valorAsFloat = floatval($valor);
        return $this->currencyFormatter->formatCurrency($valorAsFloat, $moeda);
    }
}
