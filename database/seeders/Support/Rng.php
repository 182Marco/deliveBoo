<?php

namespace Database\Seeders\Support;

use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

// Deterministic random numbers: the same key always gives the same sequence, whatever the
// state of the database. Every entity seeds its own generator from its natural key.
final class Rng
{
    private Randomizer $r;

    public function __construct(string $key)
    {
        $this->r = new Randomizer(new Xoshiro256StarStar(hash('sha256', $key, true)));
    }

    public function int(int $min, int $max): int
    {
        return $min >= $max ? $min : $this->r->getInt($min, $max);
    }

    public function float(): float
    {
        return $this->r->nextFloat();
    }

    public function between(float $min, float $max): float
    {
        return $min + ($max - $min) * $this->r->nextFloat();
    }

    public function chance(float $p): bool
    {
        return $this->r->nextFloat() < $p;
    }

    public function pick(array $items): mixed
    {
        $items = array_values($items);

        return $items[$this->r->getInt(0, count($items) - 1)];
    }

    public function shuffle(array $items): array
    {
        return $this->r->shuffleArray(array_values($items));
    }

    // index of a weighted choice: weights are non-negative numbers
    public function weighted(array $weights): int|string
    {
        $total = array_sum($weights);
        $x = $this->r->nextFloat() * $total;
        foreach ($weights as $key => $w) {
            if (($x -= $w) < 0) {
                return $key;
            }
        }

        return array_key_last($weights);
    }
}
