<?php

declare(strict_types=1);

namespace App\Controller\Helper;

use Illuminate\Database\Eloquent\Builder;

class Paginator
{
    protected int $itensCount;
    protected Builder $builder;

    /**
     * Contruct a paginator optionally applying pagination to eloquent query builder
     *
     * @param Builder $builder A Eloquent query builder to count pages and apply pagination
     * @param boolean $applyPagination Should modify query builder with pagination conditions
     */
    public function __construct(Builder $builder, protected int $currentPage, protected int $itensPerPage, bool $applyPagination = false)
    {
        $this->itensCount = $builder->count();
        $this->builder = $builder;

        if ($applyPagination) {
            $this->builder->skip(($currentPage - 1) * $itensPerPage)->take($itensPerPage);
        }
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function itensPerPage(): int
    {
        return $this->itensPerPage;
    }

    public function itensCount(): int
    {
        return $this->itensCount;
    }

    public function pageCount(): int
    {
        return (int) ceil($this->itensCount / $this->itensPerPage);
    }

    public function toData(): array
    {
        return [
            'data' => $this->builder->get(),
            'totalPages' => $this->pageCount(),
            'currentPage' => $this->currentPage(),
        ];
    }
}
