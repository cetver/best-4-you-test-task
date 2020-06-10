<?php declare(strict_types=1);

namespace App\Service;

/**
 * The "PaginationLimiterService" class
 */
class PaginationLimiterService
{
    private int $page;
    private int $perPage;
    private int $maxPerPage;

    public function __construct(int $page = 1, int $perPage = 30, int $maxPerPage = 100)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->maxPerPage = $maxPerPage;
    }

    public function offset(): int
    {
        $currentPage = ($this->page <= 0) ? 1 : $this->page;

        return ($currentPage - 1) * $this->limit();
    }

    public function limit(): int
    {
        return ($this->perPage <= $this->maxPerPage) ? $this->perPage : $this->maxPerPage;
    }
}