<?php

declare(strict_types=1);

namespace App\Model\Core;

class Menu
{
    protected Configuracao $config;

    public function __construct(protected Usuario $usr)
    {
        $this->config = Configuracao::first();
    }

    public function root()
    {
        return [
            $this->alunos(),
            $this->marketing(),
            $this->academico(),
            $this->financeiro(),
            $this->estoque(),
            $this->configuracoes(),
            $this->sistema(),
        ];
    }

    protected function alunos()
    {
        return $this->title('Alunos', [
            $this->link('Buscar', 'alunos_busca.php', $this->usr->autorizado('academico-alunos-consultar')),
        ], $this->usr->autorizado('academico-alunos-consultar'), 'alunos_busca.php');
    }

    protected function marketing()
    {
        return $this->title('Marketing', []);
    }

    protected function academico()
    {
        return $this->title('Acadêmico', [

            $this->title('Lançamento de Notas', [
                $this->link('Infantil', 'academico_notas_infantil.php'),
                $this->link('Fundamental e Médio', 'academico_notas.php'),
                $this->link('Eletivas', 'academico_notas_eletivas.php'),

            ], $this->usr->autorizado('academico-notas-consultar'))
        ]);
    }

    protected function financeiro()
    {
        return $this->title('Financeiro', []);
    }

    protected function estoque()
    {
        return $this->title('Estoque', []);
    }

    protected function configuracoes()
    {
        return [
            'label' => 'Configurações',
        ];
    }

    protected function sistema()
    {
        return [
            'label' => 'Sistema',

        ];
    }

    protected function title($label, $children, $show = true, $link = '')
    {
        return [
            'label' => $label,
            'link' => $link,
            'show' => $show,
            'children' => $children
        ];
    }

    protected function link($label, $link, $show = true, $children = null)
    {
        return [
            'label' => $label,
            'link' => $link,
            'show' => $show,
            'children' => $children
        ];
    }

    protected function appLink($label, $link, $show = true, $children = null)
    {
        return [
            'label' => $label,
            'link' => $link,
            'mode' => 'app',
            'show' => $show,
            'children' => $children
        ];
    }
}
