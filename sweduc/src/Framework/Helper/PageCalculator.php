<?php

declare(strict_types=1);

namespace App\Framework\Helper;

class PageCalculator
{
    public function __construct(protected int $totalItens, protected int $itensPerPage)
    {
    }

    /**
     * Return max page numer
     */
    public function pages(): int
    {
        return (int) ceil($this->totalItens / $this->itensPerPage);
    }

    /**
     * Return offset index for builders like eloquent builder or mysql limit
     */
    public function offset(int $currentPage): int
    {
        return ($currentPage - 1) * $this->itensPerPage;
    }

    /**
     * Return count parameter for builders like eloquent builder or mysql limit
     */
    public function count(): int
    {
        return $this->itensPerPage;
    }

    public function itens(): int
    {
        return $this->totalItens;
    }
}
