<?php

declare(strict_types=1);

namespace App\List\VideoGameList;

use App\Model\ValueObject\Direction;
use App\Model\ValueObject\Info;
use App\Model\ValueObject\Page;
use App\Model\ValueObject\Sorting;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 * @implements IteratorAggregate<Page>
 */
final class Pagination implements IteratorAggregate, Countable
{
    private bool $initialized = false;

    private int $total;

    private int $count;

    /**
     * @var Page[]
     */
    private array $pages;

    public function __construct(
        private int $page,
        private int $limit,
        private Sorting $sorting,
        private Direction $direction
    ) {
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function getLastPage(): int
    {
        if (!$this->initialized) {
            throw new RuntimeException('Pagination is not initialized');
        }

        return (int) ceil($this->total / $this->limit);
    }

    public function init(int $total, int $count): void
    {
        $this->total = $total;
        $this->count = $count;
        $this->initialized = true;
    }

    public function add(Page $page): self
    {
        $this->pages[] = $page;

        return $this;
    }

    /**
     * @return Traversable<Page>
     */
    public function getIterator(): Traversable
    {
        if (!$this->initialized) {
            throw new RuntimeException('Pagination is not initialized');
        }

        return new ArrayIterator($this->pages);
    }

    public function getInfo(): Info
    {
        if (!$this->initialized) {
            throw new RuntimeException('Pagination is not initialized');
        }

        return new Info(
            $this->count,
            $this->getOffset() + 1,
            $this->getOffset() + $this->count,
            $this->total
        );
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return array<Direction>
     */
    public function getDirections(): array
    {
        return Direction::cases();
    }

    /**
     * @return array<Sorting>
     */
    public function getAllSorting(): array
    {
        return Sorting::cases();
    }

    public function getSorting(): Sorting
    {
        return $this->sorting;
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }

    /**
     * @return array{page: int, limit: int, sorting: string, direction: string}
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
            'sorting' => $this->sorting->name,
            'direction' => $this->direction->name,
        ];
    }

    public function count(): int
    {
        return $this->getLastPage();
    }
}
