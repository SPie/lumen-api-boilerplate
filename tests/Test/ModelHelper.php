<?php

namespace Test;

use Illuminate\Support\Collection;

/**
 * Trait ModelHelper
 *
 * @package Test
 */
trait ModelHelper
{

    /**
     * @param string      $modelClass
     * @param int         $times
     * @param array       $data
     * @param string|null $state
     *
     * @return Collection
     */
    private function createModels(
        string $modelClass,
        int $times = 1,
        array $data = [],
        string $state = null
    ): Collection
    {
        if ($times == 1) {
            return new Collection([
                $state
                    ? entity($modelClass, $state, $times)->create($data)
                    : entity($modelClass, $times)->create($data)
            ]);
        }

        return $state
            ? entity($modelClass, $state, $times)->create($data)
            : entity($modelClass, $times)->create($data);
    }
}