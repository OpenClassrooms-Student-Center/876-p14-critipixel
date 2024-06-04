<?php

declare(strict_types=1);

namespace App\List\VideoGameList;

use App\Model\Entity\Tag;

final class Filter
{
    /**
     * @param Tag[] $tags
     */
    public function __construct(
        private ?string $search = null,
        private array $tags = []
    ) {
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): Filter
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param Tag[] $tags
     */
    public function setTags(array $tags): Filter
    {
        $this->tags = $tags;
        return $this;
    }
}
