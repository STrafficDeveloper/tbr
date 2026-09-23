<?php

declare(strict_types=1);

namespace App\Core;

final class Paginator
{
    public readonly int $page;
    public readonly int $lastPage;

    /** @param array<string,string> $query current filters, carried into every page link */
    public function __construct(
        public readonly int $total,
        public readonly int $perPage,
        int $page,
        private readonly string $path,
        private readonly array $query = [],
    ) {
        $this->lastPage = max(1, (int) ceil($total / $perPage));
        $this->page = min(max(1, $page), $this->lastPage);
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    public function url(int $page): string
    {
        $query = array_filter($this->query, static fn (?string $v): bool => $v !== null && $v !== '');

        if ($page > 1) {
            $query['page'] = (string) $page;
        } else {
            unset($query['page']);
        }

        return $this->path . ($query === [] ? '' : '?' . http_build_query($query));
    }

    /**
     * Page numbers to show, with null marking a gap: [1, null, 4, 5, 6, null, 12].
     *
     * @return list<int|null>
     */
    public function window(int $around = 1): array
    {
        $pages = [];
        $previous = 0;

        for ($i = 1; $i <= $this->lastPage; $i++) {
            $isEdge = $i === 1 || $i === $this->lastPage;
            $isNear = abs($i - $this->page) <= $around;

            if (!$isEdge && !$isNear) {
                continue;
            }

            if ($i - $previous > 1) {
                $pages[] = null;
            }

            $pages[] = $i;
            $previous = $i;
        }

        return $pages;
    }
}
