<?php

declare(strict_types=1);

/**
 * @template T
 * @param callable(int $index): T $callback
 * @return array<int, T>
 */
function array_fill_callback(int $startIndex, int $count, callable $callback): array
{
    /** @var array<int, T> $data */
    $data = [];

    for ($i = $startIndex; $i < $startIndex + $count; ++$i) {
        $data[$i] = $callback($i);
    }

    return $data;
}
